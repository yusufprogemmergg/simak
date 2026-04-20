<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Installment;
use App\Models\Transaction;
use App\Services\FleksiblePaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class AngsuranController extends Controller
{
    private $paymentService;

    public function __construct(FleksiblePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    // Direct pay installment — verifikasi kepemilikan via Transaction
    public function payLunas($id, Request $request)
    {
        $request->validate([
            'paid_date' => 'required|date'
        ]);

        $installment = Installment::with('transaction')->findOrFail($id);

        // Pastikan transaksi induk milik owner yang login
        $transaction = Transaction::where('id', $installment->transaction_id)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        if ($installment->status === 'paid' || $installment->remaining_amount <= 0) {
            return response()->json(['message' => 'Angsuran ini sudah lunas'], 400);
        }

        DB::beginTransaction();

        try {
            $amountToPay = (float) ($installment->remaining_amount ?? 0);

            $installment->update([
                'status'           => 'paid',
                'remaining_amount' => 0,
                'paid_date'        => $request->paid_date
            ]);

            $transaction->total_paid = ($transaction->total_paid ?? 0) + $amountToPay;
            $transaction->save();

            $payment = \App\Models\PaymentHistory::create([
                'transaction_id'     => $transaction->id,
                'date'               => $request->paid_date,
                'notes'              => 'Angsuran Payment ke - ' . $installment->installment_number,
                'amount'             => $amountToPay,
                'referenceable_type' => Installment::class,
                'referenceable_id'   => $installment->id,
            ]);

            \App\Models\CashFlow::create([
                'date'               => $request->paid_date,
                'type'               => 'income',
                'category'           => 'Cicilan Angsuran',
                'amount'             => $amountToPay,
                'notes'              => 'Pembayaran Cicilan Angsuran ke-' . $installment->installment_number . ' Transaksi: ' . $transaction->transaction_number,
                'referenceable_type' => \App\Models\PaymentHistory::class,
                'referenceable_id'   => $payment->id,
            ]);

            $this->paymentService->checkAndSetPaidOff($transaction);

            DB::commit();

            return response()->json([
                'message'         => 'Angsuran ke-' . $installment->installment_number . ' berhasil langsung dilunasi',
                'data_installment' => $installment->fresh(),
                'total_paid_now'   => $transaction->fresh()->total_paid
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