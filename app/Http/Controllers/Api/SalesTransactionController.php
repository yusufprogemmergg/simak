<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Plot;
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

    private function ownerQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return Transaction::query()->where('owner_id', auth()->id());
    }

    // GET all
    public function index(Request $request)
    {
        $query = $this->ownerQuery()->with(['plot', 'buyer', 'salesStaff']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('plot', function ($qPlot) use ($search) {
                    $qPlot->where('plot_number', 'like', "%{$search}%");
                })->orWhereHas('buyer', function ($qBuyer) use ($search) {
                    $qBuyer->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }
        if ($request->filled('dp_status')) {
            $query->where('dp_status', $request->dp_status);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('sales_staff_id')) {
            $query->where('sales_staff_id', $request->sales_staff_id);
        }
        if ($request->filled('price_min')) {
            $query->where('grand_total', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('grand_total', '<=', $request->price_max);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('booking_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('booking_date', '<=', $request->date_to);
        }

        $limit = $request->get('limit', 10);
        $data  = $query->latest()->paginate($limit);

        $mappedData = collect($data->items())->map(function ($item) {
            return [
                'id'               => $item->id,
                'transaction_number' => $item->transaction_number,
                'plot'             => $item->plot->plot_number ?? '-',
                'buyer'            => $item->buyer->name ?? '-',
                'booking_date'     => $item->booking_date ? $item->booking_date->format('Y-m-d') : null,
                'payment_method'   => $item->payment_method,
                'grand_total'      => (float) $item->grand_total,
                'total_paid'       => (float) $item->totalPaymentMade(),
                'remaining_balance' => (float) $item->remainingBalance(),
                'dp_status'        => $item->dp_status,
                'status'           => $item->status,
                'sales_staff'      => $item->salesStaff->name ?? '-',
            ];
        });

        return response()->json([
            'status'     => 'success',
            'message'    => 'Data list transaksi',
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
            ->with(['plot', 'buyer', 'salesStaff', 'installments', 'flexiblePayments', 'paymentHistories'])
            ->findOrFail($id);

        return response()->json($data);
    }

    // CREATE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plot_id'              => 'required|exists:plots,id',
            'buyer_id'             => 'required|exists:buyers,id',
            'sales_staff_id'       => 'required|exists:sales_staff,id',
            'payment_method'       => 'required|in:full_cash,installment,bank_mortgage',
            'booking_date'         => 'required|date',

            'discount_type'        => 'nullable|in:nominal,percent',
            'discount_amount'      => 'nullable|numeric|min:0',
            'ppjb_fee'             => 'nullable|numeric|min:0',
            'shm_fee'              => 'nullable|numeric|min:0',
            'other_fees'           => 'nullable|numeric|min:0',

            'down_payment_amount'  => 'nullable|numeric|min:0',
            'tenor_months'         => 'nullable|integer|min:1',
            'due_day'              => 'nullable|integer|min:1|max:31',
            'status'               => 'required|in:active,paid_off,cancelled,refunded',
            'notes'                => 'nullable|string',
            'booking_fee'          => 'nullable|numeric|min:0',
            'is_unit_included'     => 'nullable|boolean',
        ], [
            'plot_id.required'        => 'Unit/Kavling wajib dipilih.',
            'buyer_id.required'       => 'Pelanggan wajib dipilih.',
            'sales_staff_id.required' => 'Marketing wajib dipilih.',
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'booking_date.required'   => 'Tanggal booking wajib diisi.',
            'status.required'         => 'Status transaksi wajib diisi.',
        ]);

        try {
            $plot      = Plot::findOrFail($validated['plot_id']);
            $basePrice = $plot->base_price;

            $discountInput = $validated['discount_amount'] ?? 0;
            $discountType  = $request->input('discount_type', 'nominal');

            if ($discountType === 'percent') {
                $discountNominal = $basePrice * ($discountInput / 100);
            } else {
                $discountNominal = $discountInput;
            }

            $netPrice   = $basePrice - $discountNominal;
            $ppjbFee    = $validated['ppjb_fee'] ?? 0;
            $shmFee     = $validated['shm_fee'] ?? 0;
            $otherFees  = $validated['other_fees'] ?? 0;
            $bookingFee = $validated['booking_fee'] ?? 0;
            $isIncluded = $validated['is_unit_included'] ?? false;

            $grandTotal = $netPrice + $ppjbFee + $shmFee + $otherFees + ($isIncluded ? 0 : $bookingFee);

            $validated['base_price']       = $basePrice;
            $validated['net_price']        = $netPrice;
            $validated['grand_total']      = $grandTotal;
            $validated['discount_amount']  = $discountNominal;
            $validated['ppjb_fee']         = $ppjbFee;
            $validated['shm_fee']          = $shmFee;
            $validated['other_fees']       = $otherFees;

            $transaction = $this->salesService->createTransaction($validated);

            return response()->json([
                'message' => 'Transaksi Penjualan berhasil dibuat',
                'data'    => $transaction
            ], 201);

        } catch (\Exception $e) {
            Log::error('Transaction store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal membuat transaksi',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        $validated = $request->validate([
            'plot_id'             => 'sometimes|exists:plots,id',
            'buyer_id'            => 'sometimes|exists:buyers,id',
            'sales_staff_id'      => 'sometimes|exists:users,id',
            'payment_method'      => 'sometimes|in:full_cash,installment,bank_mortgage',
            'booking_date'        => 'sometimes|date',

            'discount_type'       => 'nullable|in:nominal,percent',
            'discount_amount'     => 'nullable|numeric|min:0',
            'ppjb_fee'            => 'nullable|numeric|min:0',
            'shm_fee'             => 'nullable|numeric|min:0',
            'other_fees'          => 'nullable|numeric|min:0',

            'down_payment_amount' => 'nullable|numeric|min:0',
            'tenor_months'        => 'nullable|integer|min:1',
            'due_day'             => 'nullable|integer|min:1|max:31',
            'status'              => 'sometimes|in:active,paid_off,cancelled,refunded',
            'notes'               => 'nullable|string',
            'booking_fee'         => 'nullable|numeric|min:0',
            'is_unit_included'    => 'nullable|boolean',
        ]);

        try {
            $updatedTransaction = $this->salesService->updateTransaction($transaction, $validated);

            return response()->json([
                'message' => 'Data Transaksi berhasil diupdate',
                'data'    => $updatedTransaction
            ]);
        } catch (\Exception $e) {
            Log::error('Transaction update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal mengupdate transaksi',
                'error'   => $e->getMessage()
            ], 400);
        }
    }

    // PAY DP
    public function payDp(Request $request, $id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        if ($transaction->dp_status === 'paid') {
            return response()->json(['message' => 'Uang muka (DP) sudah berstatus lunas'], 400);
        }

        DB::beginTransaction();

        try {
            $transaction->dp_status = 'paid';
            $nominalDp = (float) $transaction->down_payment_amount;

            if ($nominalDp > 0) {
                $transaction->total_paid = ($transaction->total_paid ?? 0) + $nominalDp;
            }

            $transaction->save();

            $payment = \App\Models\PaymentHistory::create([
                'transaction_id'     => $transaction->id,
                'date'               => $request->input('date', now()->toDateString()),
                'notes'              => 'Pay Down Payment',
                'amount'             => $nominalDp,
                'referenceable_type' => Transaction::class,
                'referenceable_id'   => $transaction->id,
            ]);

            \App\Models\CashFlow::create([
                'date'               => $request->input('date', now()->toDateString()),
                'type'               => 'income',
                'category'           => 'DP Penjualan',
                'amount'             => $nominalDp,
                'notes'              => 'Pembayaran Uang Muka (DP) Transaksi: ' . $transaction->transaction_number,
                'referenceable_type' => \App\Models\PaymentHistory::class,
                'referenceable_id'   => $payment->id,
            ]);

            if ($transaction->total_paid >= $transaction->total_amount) {
                $transaction->update(['status' => 'paid_off']);
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
    public function payOff(Request $request, $id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        if ($transaction->status === 'paid_off' || $transaction->total_paid >= $transaction->total_amount) {
            return response()->json(['message' => 'Transaksi ini sudah lunas'], 400);
        }

        DB::beginTransaction();

        try {
            $remaining = $transaction->total_amount - ($transaction->total_paid ?? 0);

            $transaction->total_paid      = $transaction->total_amount;
            $transaction->status          = 'paid_off';
            $transaction->settlement_date = $request->input('date', now()->toDateString());
            $transaction->save();

            $payment = \App\Models\PaymentHistory::create([
                'transaction_id'     => $transaction->id,
                'date'               => $request->input('date', now()->toDateString()),
                'notes'              => 'Payment Off',
                'amount'             => $remaining,
                'referenceable_type' => Transaction::class,
                'referenceable_id'   => $transaction->id,
            ]);

            \App\Models\CashFlow::create([
                'date'               => $request->input('date', now()->toDateString()),
                'type'               => 'income',
                'category'           => 'Pelunasan Penjualan',
                'amount'             => $remaining,
                'notes'              => 'Pelunasan Transaksi: ' . $transaction->transaction_number,
                'referenceable_type' => \App\Models\PaymentHistory::class,
                'referenceable_id'   => $payment->id,
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

        return response()->json(['message' => 'Penjualan dan semua datanya berhasil dihapus permanen']);
    }

    // CANCEL SALE
    public function cancelSale(Request $request, $id)
    {
        $transaction = $this->ownerQuery()->findOrFail($id);

        $validated = $request->validate([
            'cancel_type'    => 'required|in:refund,transfer_credit,delete',
            'refund_amount'  => 'required_if:cancel_type,refund|numeric|min:0',
            'new_buyer_id'   => 'required_if:cancel_type,transfer_credit|exists:buyers,id',
            'new_sales_staff_id' => 'nullable|exists:users,id',
        ], [
            'cancel_type.required'   => 'Tipe pembatalan wajib dipilih.',
            'refund_amount.required_if' => 'Nominal pengembalian wajib diisi jika tipe pembatalan adalah refund.',
            'new_buyer_id.required_if'  => 'Pembeli baru wajib dipilih untuk oper kredit.',
        ]);

        try {
            if ($validated['cancel_type'] === 'refund') {
                $this->salesService->handleCancelRefund($transaction, $validated['refund_amount']);
                $message = 'Pembatalan transaksi via Refund berhasil.';
            } elseif ($validated['cancel_type'] === 'transfer_credit') {
                $this->salesService->handleOperKredit(
                    $transaction,
                    $validated['new_buyer_id'],
                    $validated['new_sales_staff_id'] ?? null
                );
                $message = 'Transfer Kredit berhasil, pembeli baru sudah ditetapkan.';
            } elseif ($validated['cancel_type'] === 'delete') {
                $this->salesService->handleHapusPenjualan($transaction);
                $message = 'Transaksi penjualan berhasil dihapus permanen.';
            }

            return response()->json([
                'message' => $message,
                'data'    => $validated['cancel_type'] !== 'delete'
                    ? $transaction->fresh()->load('buyer', 'salesStaff', 'plot')
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
