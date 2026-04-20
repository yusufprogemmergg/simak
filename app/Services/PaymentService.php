<?php

namespace App\Services;

use App\Models\PaymentHistory;
use App\Models\CashFlow;
use App\Models\Transaction;
use App\Models\Installment;
use App\Models\FlexiblePayment;
use Illuminate\Support\Facades\DB;
use Exception;

class PaymentService
{
    /**
     * Membatalkan pembayaran secara universal berdasarkan PaymentHistory
     */
    public function cancelPayment($paymentId)
    {
        DB::beginTransaction();

        try {
            $payment     = PaymentHistory::findOrFail($paymentId);
            $transaction = Transaction::findOrFail($payment->transaction_id);

            // 1. Revert side effects berdasarkan tipe referensi
            if ($payment->referenceable_type === Transaction::class) {
                $this->handleCancelTransactionPayment($payment, $transaction);
            } elseif ($payment->referenceable_type === Installment::class) {
                $this->handleCancelInstallmentPayment($payment, $transaction);
            } elseif ($payment->referenceable_type === FlexiblePayment::class) {
                $this->handleCancelFlexiblePayment($payment, $transaction);
            } else {
                // Fallback: kurangi nominal saja dari total_paid
                $transaction->decrement('total_paid', (float)$payment->amount);
            }

            // 2. Hapus CashFlow terkait
            CashFlow::where('referenceable_type', PaymentHistory::class)
                ->where('referenceable_id', $payment->id)
                ->delete();

            // 3. Hapus PaymentHistory itu sendiri
            $payment->delete();

            // 4. Update status transaksi
            $this->revalidateTransactionStatus($transaction);

            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle pembatalan pembayaran DP atau Pelunasan Langsung
     */
    private function handleCancelTransactionPayment(PaymentHistory $payment, Transaction $transaction)
    {
        if (
            strpos(strtolower($payment->notes), 'dp') !== false ||
            strpos(strtolower($payment->notes), 'down payment') !== false
        ) {
            $transaction->update(['dp_status' => 'unpaid']);
        }

        $transaction->decrement('total_paid', (float)$payment->amount);
    }

    /**
     * Handle pembatalan pembayaran Angsuran spesifik
     */
    private function handleCancelInstallmentPayment(PaymentHistory $payment, Transaction $transaction)
    {
        $installment = Installment::find($payment->referenceable_id);
        if ($installment) {
            $installment->update([
                'status'           => 'unpaid',
                'remaining_amount' => $installment->amount,
                'paid_date'        => null,
            ]);
        }

        $transaction->decrement('total_paid', (float)$payment->amount);
    }

    /**
     * Handle pembatalan pembayaran Fleksibel
     */
    private function handleCancelFlexiblePayment(PaymentHistory $payment, Transaction $transaction)
    {
        $flexiblePayment = FlexiblePayment::find($payment->referenceable_id);
        if ($flexiblePayment) {
            // Revert alokasi-alokasi ke angsuran
            foreach ($flexiblePayment->allocations as $allocation) {
                $installment = $allocation->installment;
                if ($installment) {
                    $allocation->delete();
                    $installment->updatePayment();
                }
            }

            $transaction->decrement('total_paid', (float)$flexiblePayment->amount);
            $transaction->decrement('total_flexible_paid', (float)$flexiblePayment->amount);

            $flexiblePayment->delete();
        } else {
            $transaction->decrement('total_paid', (float)$payment->amount);
        }
    }

    /**
     * Re-validasi status transaksi setelah pembatalan
     */
    private function revalidateTransactionStatus(Transaction $transaction)
    {
        $transaction->refresh();
        if ($transaction->status === 'paid_off' && $transaction->total_paid < $transaction->total_amount) {
            $transaction->update([
                'status'          => 'active',
                'settlement_date' => null,
            ]);
        }
    }
}
