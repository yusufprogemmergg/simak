<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Angsuran;
use App\Models\SalesTransaction;
use App\Services\FleksiblePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AngsuranController extends Controller
{
    private $pembayaranService;

    public function __construct(FleksiblePaymentService $pembayaranService)
    {
        $this->pembayaranService = $pembayaranService;
    }

    // Direct pay angsuran — verifikasi kepemilikan via SalesTransaction
    public function payLunas($id, Request $request)
    {
        $request->validate([
            'tanggal_bayar' => 'required|date'
        ]);

        // Load angsuran beserta penjualan-nya
        $angsuran = Angsuran::with('penjualan')->findOrFail($id);

        // Pastikan transaksi induk milik owner yang login
        $penjualan = SalesTransaction::where('id', $angsuran->penjualan_id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        if ($angsuran->status === 'paid' || $angsuran->sisa_setelah_bayar <= 0) {
            return response()->json(['message' => 'Angsuran ini sudah lunas'], 400);
        }

        DB::beginTransaction();

        try {
            $nominalBayar = (float) ($angsuran->sisa_setelah_bayar ?? 0);

            $angsuran->update([
                'status'          => 'paid',
                'sisa_setelah_bayar' => 0,
                'tanggal_bayar'   => $request->tanggal_bayar
            ]);

            $penjualan->total_paid = ($penjualan->total_paid ?? 0) + $nominalBayar;
            $penjualan->save();

            // Record Payment History
            $payment = \App\Models\PaymentHistory::create([
                'sales_transaction_id' => $penjualan->id,
                'tanggal'              => $request->tanggal_bayar,
                'keterangan'           => 'Angsuran Payment ke - ' . $angsuran->bulan_ke,
                'amount'               => $nominalBayar
            ]);

            // Auto Journal ke Buku Kas
            \App\Models\CashFlow::create([
                'tanggal'        => $request->tanggal_bayar,
                'tipe_transaksi' => 'pemasukan',
                'kategori'       => 'Cicilan Angsuran',
                'nominal'        => $nominalBayar,
                'keterangan'     => 'Pembayaran Cicilan Angsuran ke-' . $angsuran->bulan_ke . ' Transaksi: ' . $penjualan->nomor_transaksi,
                'referensi_type' => \App\Models\PaymentHistory::class,
                'referensi_id'   => $payment->id,
            ]);

            $this->pembayaranService->checkAndSetPaidOff($penjualan);

            DB::commit();

            return response()->json([
                'message'              => 'Angsuran ke-' . $angsuran->bulan_ke . ' berhasil langsung dilunasi',
                'data_angsuran'        => $angsuran->fresh(),
                'total_paid_sekarang'  => $penjualan->fresh()->total_paid
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('AngsuranController payLunas error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal melunasi angsuran secara langsung',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}