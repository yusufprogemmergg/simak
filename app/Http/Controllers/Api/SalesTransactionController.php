<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SalesTransaction;
use App\Models\Kavling;
use Illuminate\Http\Request;
use App\Services\SalesTransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesTransactionController extends Controller
{
    private $salesService;

    public function __construct(SalesTransactionService $salesService)
    {
        $this->salesService = $salesService;
    }

    /**
     * Helper: ambil base query yang sudah di-scope ke owner.
     */
    private function ownerQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return SalesTransaction::query()->where('owner_id', auth()->id());
    }

    // GET all
    public function index(Request $request)
    {
        $query = $this->ownerQuery()->with(['kavling', 'buyer', 'sales']);

        // 1. Filter Pencarian (Kavling Blok atau Nama Pembeli)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('kavling', function ($qKavling) use ($search) {
                    $qKavling->where('blok_nomor', 'like', "%{$search}%");
                })->orWhereHas('buyer', function ($qBuyer) use ($search) {
                    $qBuyer->where('username', 'like', "%{$search}%");
                });
            });
        }

        // 2. Filter Dropdown Exact Match
        if ($request->filled('metode_pembayaran')) {
            $query->where('metode_pembayaran', $request->metode_pembayaran);
        }
        if ($request->filled('status_dp')) {
            $query->where('status_dp', $request->status_dp);
        }
        if ($request->filled('status_penjualan')) {
            $query->where('status_penjualan', $request->status_penjualan);
        }
        if ($request->filled('sales_id')) {
            $query->where('sales_id', $request->sales_id);
        }

        // 3. Filter Range Angka (Harga Min dan Harga Max)
        if ($request->filled('harga_min')) {
            $query->where('grand_total', '>=', $request->harga_min);
        }
        if ($request->filled('harga_max')) {
            $query->where('grand_total', '<=', $request->harga_max);
        }

        // 4. Filter Range Tanggal (Tgl Dari & Tgl Sampai)
        if ($request->filled('tgl_dari')) {
            $query->whereDate('tanggal_booking', '>=', $request->tgl_dari);
        }
        if ($request->filled('tgl_sampai')) {
            $query->whereDate('tanggal_booking', '<=', $request->tgl_sampai);
        }

        // 5. Paginate result
        $limit = $request->get('limit', 10);
        $data = $query->latest()->paginate($limit);

        // 6. Map data
        $mappedData = collect($data->items())->map(function ($item) {
            return [
                'id'               => $item->id,
                'nomor_transaksi'  => $item->nomor_transaksi,
                'kavling'          => $item->kavling->blok_nomor ?? '-',
                'pembeli'          => $item->buyer->username ?? '-',
                'tanggal_booking'  => $item->tanggal_booking ? $item->tanggal_booking->format('Y-m-d') : null,
                'metode_pembayaran'=> $item->metode_pembayaran,
                'harga_jual'       => (float) $item->grand_total,
                'total_dibayar'    => (float) $item->totalPembayaran(),
                'sisa_piutang'     => (float) $item->sisaPembayaran(),
                'status_dp'        => $item->status_dp,
                'status_penjualan' => $item->status_penjualan,
                'marketing'        => $item->sales->username ?? '-',
            ];
        });

        return response()->json([
            'status'     => 'success',
            'message'    => 'Data list penjualan',
            'data'       => $mappedData,
            'pagination' => [
                'current_page' => $data->currentPage(),
                'per_page'     => $data->perPage(),
                'total_data'   => $data->total(),
                'total_pages'  => $data->lastPage(),
            ]
        ], 200);
    }

    // GET by id
    public function show($id)
    {
        $data = $this->ownerQuery()
            ->with(['kavling', 'buyer', 'sales', 'angsuran', 'fleksiblePayments', 'paymentHistory'])
            ->findOrFail($id);

        return response()->json($data);
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kavling_id'          => 'required|exists:kavling,id',
            'buyer_id'            => 'required|exists:buyers,id',
            'sales_id'            => 'required|exists:users,id',
            'metode_pembayaran'   => 'required|in:cash_keras,angsuran_in_house,kpr_bank',
            'tanggal_booking'     => 'required|date',

            'tipe_diskon'         => 'nullable|in:nominal,persen',
            'promo_diskon'        => 'nullable|numeric|min:0',
            'biaya_ppjb'          => 'nullable|numeric|min:0',
            'biaya_shm'           => 'nullable|numeric|min:0',
            'biaya_lain'          => 'nullable|numeric|min:0',

            'uang_muka_nominal'   => 'nullable|numeric|min:0',
            'tenor'               => 'nullable|integer|min:1',
            'tanggal_jatuh_tempo' => 'nullable|integer|min:1|max:31',
            'status_penjualan'    => 'required|in:active,paid_off,cancel,refund',
        ]);

        try {
            // Ambil harga dasar otomatis dari kavling
            $kavling = Kavling::findOrFail($validated['kavling_id']);
            $hargaDasar = $kavling->harga_dasar;

            // Tentukan Diskon
            $nilaiDiskonInput   = $validated['promo_diskon'] ?? 0;
            $tipeDiskon         = $request->input('tipe_diskon', 'nominal');

            if ($tipeDiskon === 'persen') {
                $promoDiskonNominal = $hargaDasar * ($nilaiDiskonInput / 100);
            } else {
                $promoDiskonNominal = $nilaiDiskonInput;
            }

            // Perhitungan otomatis Netto & Grand Total
            $hargaNetto = $hargaDasar - $promoDiskonNominal;
            $biayaPpjb  = $validated['biaya_ppjb'] ?? 0;
            $biayaShm   = $validated['biaya_shm'] ?? 0;
            $biayaLain  = $validated['biaya_lain'] ?? 0;
            $grandTotal = $hargaNetto + $biayaPpjb + $biayaShm + $biayaLain;

            $validated['harga_dasar']  = $hargaDasar;
            $validated['harga_netto']  = $hargaNetto;
            $validated['grand_total']  = $grandTotal;
            $validated['promo_diskon'] = $promoDiskonNominal;
            $validated['biaya_ppjb']   = $biayaPpjb;
            $validated['biaya_shm']    = $biayaShm;
            $validated['biaya_lain']   = $biayaLain;

            $transaction = $this->salesService->createTransaction($validated);

            return response()->json([
                'message' => 'Transaksi Penjualan berhasil dibuat',
                'data'    => $transaction
            ], 201);

        } catch (\Exception $e) {
            Log::error('SalesTransaction store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat transaksi',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // UPDATE — hanya transaksi milik owner yang login
    public function update(Request $request, $id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        $validated = $request->validate([
            'kavling_id'          => 'sometimes|exists:kavling,id',
            'buyer_id'            => 'sometimes|exists:buyers,id',
            'sales_id'            => 'sometimes|exists:users,id',
            'metode_pembayaran'   => 'sometimes|in:cash_keras,angsuran_in_house,kpr_bank',
            'tanggal_booking'     => 'sometimes|date',

            'tipe_diskon'         => 'nullable|in:nominal,persen',
            'promo_diskon'        => 'nullable|numeric|min:0',
            'biaya_ppjb'          => 'nullable|numeric|min:0',
            'biaya_shm'           => 'nullable|numeric|min:0',
            'biaya_lain'          => 'nullable|numeric|min:0',

            'uang_muka_nominal'   => 'nullable|numeric|min:0',
            'tenor'               => 'nullable|integer|min:1',
            'tanggal_jatuh_tempo' => 'nullable|integer|min:1|max:31',
            'status_penjualan'    => 'sometimes|in:active,paid_off,cancel,refund',
        ]);

        try {
            $updatedTransaction = $this->salesService->updateTransaction($transaction, $validated);

            return response()->json([
                'message' => 'Data Penjualan berhasil diupdate',
                'data'    => $updatedTransaction
            ]);
        } catch (\Exception $e) {
            Log::error('SalesTransaction update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengupdate transaksi',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    // PAY DP
    public function payDp($id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        if ($transaction->status_dp === 'paid') {
            return response()->json([
                'message' => 'Uang muka (DP) sudah berstatus lunas'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $transaction->status_dp = 'paid';

            $nominalDp = (float) $transaction->uang_muka_nominal;
            if ($nominalDp > 0) {
                $transaction->total_paid = ($transaction->total_paid ?? 0) + $nominalDp;
            }

            $transaction->save();

            // Record Payment History
            $payment = \App\Models\PaymentHistory::create([
                'sales_transaction_id' => $transaction->id,
                'tanggal'              => now()->toDateString(),
                'keterangan'           => 'Pay Down Payment',
                'amount'               => $nominalDp
            ]);

            // Auto Journal ke Buku Kas
            \App\Models\CashFlow::create([
                'tanggal'         => now()->toDateString(),
                'tipe_transaksi'  => 'pemasukan',
                'kategori'        => 'DP Penjualan',
                'nominal'         => $nominalDp,
                'keterangan'      => 'Pembayaran Uang Muka (DP) Transaksi: ' . $transaction->nomor_transaksi,
                'referensi_type'  => \App\Models\PaymentHistory::class,
                'referensi_id'    => $payment->id,
            ]);

            // Check if transaction is paid off
            if ($transaction->total_paid >= $transaction->total_amount) {
                $transaction->update(['status_penjualan' => 'paid_off']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran Uang Muka (DP) berhasil',
                'data'    => $transaction->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayDp error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal melakukan pembayaran DP',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // PAY OFF
    public function payOff($id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        if ($transaction->status_penjualan === 'paid_off' || $transaction->total_paid >= $transaction->total_amount) {
            return response()->json([
                'message' => 'Transaksi ini sudah lunas'
            ], 400);
        }

        DB::beginTransaction();

        try {
            $sisaKekurangan = $transaction->total_amount - ($transaction->total_paid ?? 0);

            $transaction->total_paid         = $transaction->total_amount;
            $transaction->status_penjualan   = 'paid_off';
            $transaction->tanggal_pelunasan  = now()->toDateString();
            $transaction->save();

            // Record Payment History
            $payment = \App\Models\PaymentHistory::create([
                'sales_transaction_id' => $transaction->id,
                'tanggal'              => now()->toDateString(),
                'keterangan'           => 'Payment Off',
                'amount'               => $sisaKekurangan
            ]);

            // Auto Journal ke Buku Kas
            \App\Models\CashFlow::create([
                'tanggal'        => now()->toDateString(),
                'tipe_transaksi' => 'pemasukan',
                'kategori'       => 'Pelunasan Penjualan',
                'nominal'        => $sisaKekurangan,
                'keterangan'     => 'Pelunasan Transaksi: ' . $transaction->nomor_transaksi,
                'referensi_type' => \App\Models\PaymentHistory::class,
                'referensi_id'   => $payment->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pelunasan (Payment Off) berhasil',
                'data'    => $transaction->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('PayOff error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal melakukan Pelunasan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // DELETE
    public function destroy($id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        $this->salesService->handleHapusPenjualan($transaction);

        return response()->json([
            'message' => 'Penjualan dan semua datanya berhasil dihapus permanen'
        ]);
    }

    // CANCEL SALE (Refund, Oper Kredit, Hapus)
    public function cancelSale(Request $request, $id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        $validated = $request->validate([
            'tipe_pembatalan' => 'required|in:refund,oper_kredit,hapus',
            'nominal_refund'  => 'required_if:tipe_pembatalan,refund|numeric|min:0',
            'new_buyer_id'    => 'required_if:tipe_pembatalan,oper_kredit|exists:buyers,id',
            'new_sales_id'    => 'nullable|exists:users,id',
        ]);

        try {
            if ($validated['tipe_pembatalan'] === 'refund') {
                $this->salesService->handleCancelRefund($transaction, $validated['nominal_refund']);
                $message = 'Pembatalan transaksi via Refund berhasil.';
            } elseif ($validated['tipe_pembatalan'] === 'oper_kredit') {
                $this->salesService->handleOperKredit(
                    $transaction,
                    $validated['new_buyer_id'],
                    $validated['new_sales_id'] ?? null
                );
                $message = 'Oper Kredit berhasil, pembeli baru sudah ditetapkan.';
            } elseif ($validated['tipe_pembatalan'] === 'hapus') {
                $this->salesService->handleHapusPenjualan($transaction);
                $message = 'Transaksi penjualan berhasil dihapus permanen.';
            }

            return response()->json([
                'message' => $message,
                'data'    => $validated['tipe_pembatalan'] !== 'hapus'
                    ? $transaction->fresh()->load('buyer', 'sales', 'kavling')
                    : null
            ]);
        } catch (\Exception $e) {
            Log::error('CancelSale error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memproses pembatalan',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
