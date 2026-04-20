<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class SalesTransactionService
{
    /**
     * Store new Transaction
     */
    public function createTransaction(array $data)
    {
        DB::beginTransaction();

        try {
            $transaction = Transaction::create($data);

            // Ubah status plot menjadi sold
            $plot = \App\Models\Plot::find($transaction->plot_id);
            if ($plot) {
                $plot->updateStatus('sold');
            }

            if ($transaction->payment_method === 'installment') {
                $this->handleInstallment($transaction);
            } elseif ($transaction->payment_method === 'bank_mortgage') {
                $this->handleBankMortgage($transaction);
            } elseif ($transaction->payment_method === 'full_cash') {
                $this->handleFullCash($transaction);
            }

            DB::commit();

            // Sync stats setelah commit
            if ($transaction->sales_staff_id) {
                $this->syncSalesStats($transaction->sales_staff_id);
            }

            return $transaction->load('installments');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing Transaction
     */
    public function updateTransaction(Transaction $transaction, array $data)
    {
        DB::beginTransaction();

        try {
            // Evaluasi Uang Muka
            if (array_key_exists('down_payment_amount', $data) && $data['down_payment_amount'] != $transaction->down_payment_amount) {
                if ($transaction->dp_status === 'paid') {
                    throw new Exception('Tidak dapat mengubah nominal DP karena status DP sudah lunas.');
                }
            }

            // Recalculate Totals
            $basePrice = array_key_exists('base_price', $data) ? $data['base_price'] : $transaction->base_price;

            $discountType = $data['discount_type'] ?? 'nominal';
            if (array_key_exists('discount_amount', $data)) {
                if ($discountType === 'percent') {
                    $discountNominal = $basePrice * ($data['discount_amount'] / 100);
                } else {
                    $discountNominal = $data['discount_amount'];
                }
                $data['discount_amount'] = $discountNominal;
            } else {
                $discountNominal = $transaction->discount_amount;
            }

            $netPrice  = $basePrice - $discountNominal;
            $ppjbFee   = array_key_exists('ppjb_fee', $data) ? $data['ppjb_fee'] : $transaction->ppjb_fee;
            $shmFee    = array_key_exists('shm_fee', $data) ? $data['shm_fee'] : $transaction->shm_fee;
            $otherFees = array_key_exists('other_fees', $data) ? $data['other_fees'] : $transaction->other_fees;
            $bookingFee = array_key_exists('booking_fee', $data) ? $data['booking_fee'] : $transaction->booking_fee;
            $isIncluded = array_key_exists('is_unit_included', $data) ? $data['is_unit_included'] : $transaction->is_unit_included;

            $grandTotal = $netPrice + $ppjbFee + $shmFee + $otherFees + ($isIncluded ? 0 : $bookingFee);

            $data['base_price']    = $basePrice;
            $data['net_price']     = $netPrice;
            $data['grand_total']   = $grandTotal;
            $data['total_amount']  = $grandTotal;

            $transaction->update($data);

            if ($transaction->payment_method === 'installment') {
                $this->handleUpdateInstallment($transaction);
            } elseif ($transaction->payment_method === 'bank_mortgage') {
                $transaction->installments()->whereNotIn('status', ['paid', 'partial'])->delete();
                $this->handleBankMortgage($transaction);
            } elseif ($transaction->payment_method === 'full_cash') {
                $transaction->installments()->whereNotIn('status', ['paid', 'partial'])->delete();
                $this->handleFullCash($transaction);
            }

            DB::commit();

            $this->syncSalesStats($transaction->sales_staff_id);

            return $transaction->load('installments');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle Update Installment
     */
    private function handleUpdateInstallment(Transaction $transaction)
    {
        if (empty($transaction->tenor_months) || $transaction->tenor_months < 1) {
            throw new Exception('Tenor wajib diisi untuk metode angsuran');
        }

        $remaining = $transaction->grand_total;
        if ($transaction->down_payment_amount > 0) {
            $remaining -= $transaction->down_payment_amount;
        }

        $lockedInstallments   = $transaction->installments()->whereIn('status', ['paid', 'partial'])->get();
        $countLocked          = $lockedInstallments->count();
        $totalNominalLocked   = $lockedInstallments->sum('amount');

        $remainingDebt   = $remaining - $totalNominalLocked;
        $remainingTenor  = $transaction->tenor_months - $countLocked;

        $transaction->installments()->whereNotIn('status', ['paid', 'partial'])->delete();

        if ($remainingDebt <= 0) return;

        if ($remainingTenor < 1) {
            throw new Exception(
                "Sisa tenor tidak mencukupi untuk tagihan sebesar " . number_format($remainingDebt) .
                ". Sudah ada $countLocked bulan yang terbayar. Harap tambah jumlah tenor."
            );
        }

        $amountPerMonth = $remainingDebt / $remainingTenor;
        $startDate      = $transaction->booking_date ? Carbon::parse($transaction->booking_date) : now();
        $dueDay         = $transaction->due_day ?? $startDate->day;

        for ($i = 1; $i <= $remainingTenor; $i++) {
            $monthNumber = $countLocked + $i;
            $date        = $startDate->copy()->addMonths($monthNumber);
            $date->day(min($dueDay, $date->daysInMonth));

            if ($i == $remainingTenor) {
                $amountPerMonth = $remainingDebt;
            }

            $transaction->installments()->create([
                'installment_number' => $monthNumber,
                'due_date'           => $date->toDateString(),
                'amount'             => $amountPerMonth,
                'remaining_amount'   => $amountPerMonth,
                'status'             => 'unpaid',
                'notes'              => 'Angsuran ke-' . $monthNumber,
            ]);

            $remainingDebt -= $amountPerMonth;
        }
    }

    /**
     * Handle Angsuran In House (installment)
     */
    private function handleInstallment(Transaction $transaction)
    {
        if (empty($transaction->tenor_months) || $transaction->tenor_months < 1) {
            throw new Exception('Tenor wajib diisi untuk metode angsuran');
        }

        $remaining  = $transaction->grand_total;
        $startDate  = $transaction->booking_date ? Carbon::parse($transaction->booking_date) : now();

        if ($transaction->down_payment_amount > 0) {
            $remaining -= $transaction->down_payment_amount;
        }

        $amountPerMonth = $remaining / $transaction->tenor_months;
        $dueDay         = $transaction->due_day ?? $startDate->day;

        for ($i = 1; $i <= $transaction->tenor_months; $i++) {
            $date = $startDate->copy()->addMonths($i);
            $date->day(min($dueDay, $date->daysInMonth));

            if ($i == $transaction->tenor_months) {
                $amountPerMonth = $remaining;
            }

            $transaction->installments()->create([
                'installment_number' => $i,
                'due_date'           => $date->toDateString(),
                'amount'             => $amountPerMonth,
                'remaining_amount'   => $amountPerMonth,
                'status'             => 'unpaid',
                'notes'              => 'Angsuran ke-' . $i,
            ]);

            $remaining -= $amountPerMonth;
        }
    }

    /**
     * Handle KPR Bank
     */
    private function handleBankMortgage(Transaction $transaction)
    {
        // KPR: Sisa ditangani bank, tidak ada angsuran dari developer.
    }

    /**
     * Handle Full Cash
     */
    private function handleFullCash(Transaction $transaction)
    {
        // Tidak ada angsuran. Pembayaran dilakukan via flexible payment.
    }

    /**
     * Handle Cancel Refund
     */
    public function handleCancelRefund(Transaction $transaction, $nominal)
    {
        DB::beginTransaction();
        try {
            $transaction->update(['status' => 'refunded']);

            // Kembalikan plot ke tersedia
            $plot = \App\Models\Plot::find($transaction->plot_id);
            if ($plot) {
                $plot->updateStatus('available');
            }

            // Record pengeluaran di CashFlow
            if ($nominal > 0) {
                \App\Models\CashFlow::create([
                    'date'               => now()->toDateString(),
                    'type'               => 'expense',
                    'category'           => 'Refund Penjualan',
                    'amount'             => $nominal,
                    'notes'              => 'Refund pembatalan transaksi: ' . $transaction->transaction_number,
                    'referenceable_type' => Transaction::class,
                    'referenceable_id'   => $transaction->id,
                ]);
            }

            DB::commit();

            if ($transaction->sales_staff_id) {
                $this->syncSalesStats($transaction->sales_staff_id);
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle Oper Kredit
     */
    public function handleOperKredit(Transaction $transaction, $newBuyerId, $newSalesStaffId = null)
    {
        $oldSalesStaffId = $transaction->sales_staff_id;
        $updateData      = ['buyer_id' => $newBuyerId];
        if ($newSalesStaffId) {
            $updateData['sales_staff_id'] = $newSalesStaffId;
        }
        $transaction->update($updateData);

        if ($oldSalesStaffId) $this->syncSalesStats($oldSalesStaffId);
        if ($newSalesStaffId && $newSalesStaffId != $oldSalesStaffId) $this->syncSalesStats($newSalesStaffId);
    }

    /**
     * Handle Hapus Penjualan
     */
    public function handleHapusPenjualan(Transaction $transaction)
    {
        $salesStaffId = $transaction->sales_staff_id;
        DB::beginTransaction();
        try {
            // Hapus CashFlow yang referensinya ke PaymentHistory milik transaksi ini
            $paymentHistoryIds = $transaction->paymentHistories()->pluck('id');
            if ($paymentHistoryIds->isNotEmpty()) {
                \App\Models\CashFlow::where('referenceable_type', \App\Models\PaymentHistory::class)
                                    ->whereIn('referenceable_id', $paymentHistoryIds)
                                    ->delete();
            }

            // Hapus CashFlow yang secara langsung mereferensikan transaksi ini
            \App\Models\CashFlow::where('referenceable_type', Transaction::class)
                                ->where('referenceable_id', $transaction->id)
                                ->delete();

            // Hapus dependensi (cascade sudah di DB, tapi kita manual untuk aman)
            $transaction->installments()->delete();
            $transaction->flexiblePayments()->delete();
            $transaction->paymentHistories()->delete();

            // Kembalikan plot ke tersedia
            $plot = \App\Models\Plot::find($transaction->plot_id);
            if ($plot) {
                $plot->updateStatus('available');
            }

            $transaction->delete();

            DB::commit();

            if ($salesStaffId) {
                $this->syncSalesStats($salesStaffId);
            }
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Sync Sales Staff Statistics
     */
    public function syncSalesStats($salesStaffId)
    {
        if (!$salesStaffId) return;

        $salesRecord = \App\Models\SalesStaff::find($salesStaffId);
        if (!$salesRecord) return;

        $stats = Transaction::where('sales_staff_id', $salesStaffId)
            ->whereNotIn('status', ['cancelled', 'refunded'])
            ->selectRaw('count(*) as total_unit, sum(grand_total) as total_rev')
            ->first();

        $salesRecord->update([
            'total_units_sold' => $stats->total_unit ?? 0,
            'total_revenue'    => $stats->total_rev ?? 0,
        ]);
    }
}
