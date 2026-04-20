<?php

namespace App\Services;

use App\Models\FlexiblePayment;
use App\Models\FlexiblePaymentAllocation;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;

class FleksiblePaymentService
{
    /**
     * Proses pembayaran fleksibel — simpan dan alokasikan ke installments secara FIFO.
     */
    public function processPayment(array $data)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::findOrFail($data['transaction_id']);

            // Validate nominal tidak melebihi sisa
            $remaining = $transaction->total_amount - $transaction->total_paid;
            if ($data['amount'] > $remaining) {
                throw new Exception("Nominal pembayaran tidak boleh lebih dari sisa kekurangan (" . number_format($remaining) . ")");
            }

            // Buat record flexible payment
            $payment = FlexiblePayment::create([
                'transaction_id' => $transaction->id,
                'amount'         => $data['amount'],
                'paid_date'      => $data['paid_date'],
                'notes'          => $data['notes'] ?? null,
                'status'         => 'pending',
                'created_by'     => auth()->id() ?? null,
            ]);

            // Update totals
            $transaction->increment('total_paid', (float)$payment->amount);
            $transaction->increment('total_flexible_paid', (float)$payment->amount);

            // Record Payment History
            $paymentHistory = \App\Models\PaymentHistory::create([
                'transaction_id'     => $transaction->id,
                'date'               => $payment->paid_date,
                'notes'              => 'Flexible Payment',
                'amount'             => $payment->amount,
                'referenceable_type' => FlexiblePayment::class,
                'referenceable_id'   => $payment->id,
            ]);

            // Auto Journal ke Buku Kas
            \App\Models\CashFlow::create([
                'date'               => $payment->paid_date,
                'type'               => 'income',
                'category'           => 'Pembayaran Fleksibel',
                'amount'             => $payment->amount,
                'notes'              => 'Pembayaran Fleksibel Transaksi: ' . $transaction->transaction_number,
                'referenceable_type' => \App\Models\PaymentHistory::class,
                'referenceable_id'   => $paymentHistory->id,
            ]);

            // Jika installment, lakukan auto-alokasi FIFO
            if ($transaction->payment_method === 'installment') {
                $this->allocateToInstallments($transaction, $payment);
            }

            // Cek apakah lunas
            $this->checkAndSetPaidOff($transaction);

            DB::commit();

            return $payment;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Alokasi FIFO ke installments
     */
    private function allocateToInstallments(Transaction $transaction, FlexiblePayment $payment)
    {
        $remainingFund = $payment->amount;

        $installments = $transaction->installments()
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('installment_number', 'asc')
            ->get();

        foreach ($installments as $installment) {
            if ($remainingFund <= 0) break;

            $needed          = $installment->remaining_amount;
            $allocatedAmount = min($remainingFund, $needed);

            FlexiblePaymentAllocation::create([
                'flexible_payment_id' => $payment->id,
                'installment_id'      => $installment->id,
                'allocated_amount'    => $allocatedAmount,
            ]);

            $installment->updatePayment();

            $remainingFund -= $allocatedAmount;
        }
    }

    /**
     * Mengecek dan auto set transaksi menjadi paid_off
     */
    public function checkAndSetPaidOff(Transaction $transaction)
    {
        $transaction->refresh();

        if ($transaction->total_paid >= $transaction->total_amount) {
            $transaction->update([
                'status'          => 'paid_off',
                'settlement_date' => now()->toDateString(),
            ]);
        }
    }
}
