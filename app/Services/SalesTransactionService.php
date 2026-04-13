<?php

namespace App\Services;

use App\Models\SalesTransaction;
use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;

class SalesTransactionService
{
    /**
     * Store new Sales Transaction
     */
    public function createTransaction(array $data)
    {
        DB::beginTransaction();

        try {
            // Note: owner_id will be auto-set by the model boot if auth()->check()
            $transaction = SalesTransaction::create($data);

            // Ubah status kavling menjadi sold karena sudah dibeli
            $kavling = \App\Models\Kavling::find($transaction->kavling_id);
            if ($kavling) {
                // Bisa disesuaikan jadi 'sold', 'reserved' atau 'active' sesuai preferensi
                $kavling->updateStatus('sold');
            }

            if ($transaction->metode_pembayaran === 'angsuran_in_house') {
                $this->handleAngsuranInHouse($transaction);
            } elseif ($transaction->metode_pembayaran === 'kpr_bank') {
                $this->handleKprBank($transaction);
            } elseif ($transaction->metode_pembayaran === 'cash_keras') {
                $this->handleCashKeras($transaction);
            }

            DB::commit();

            return $transaction->load('angsuran');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update existing Sales Transaction
     */
    public function updateTransaction(SalesTransaction $transaction, array $data)
    {
        DB::beginTransaction();

        try {
            // Evaluasi Uang Muka
            if (array_key_exists('uang_muka_nominal', $data) && $data['uang_muka_nominal'] != $transaction->uang_muka_nominal) {
                if ($transaction->status_dp === 'paid') {
                    throw new Exception('Tidak dapat mengubah nominal DP karena status DP sudah lunas.');
                }
            }

            // Recalculate Totals
            $hargaDasar = array_key_exists('harga_dasar', $data) ? $data['harga_dasar'] : $transaction->harga_dasar;
            
            // Diskon
            $tipeDiskon = $data['tipe_diskon'] ?? 'nominal';
            if (array_key_exists('promo_diskon', $data)) {
                if ($tipeDiskon === 'persen') {
                    $promoDiskonNominal = $hargaDasar * ($data['promo_diskon'] / 100);
                } else {
                    $promoDiskonNominal = $data['promo_diskon'];
                }
                $data['promo_diskon'] = $promoDiskonNominal; // Store absolute nominal to DB
            } else {
                $promoDiskonNominal = $transaction->promo_diskon;
            }

            $hargaNetto = $hargaDasar - $promoDiskonNominal;

            $biayaPpjb = array_key_exists('biaya_ppjb', $data) ? $data['biaya_ppjb'] : $transaction->biaya_ppjb;
            $biayaShm  = array_key_exists('biaya_shm', $data) ? $data['biaya_shm'] : $transaction->biaya_shm;
            $biayaLain = array_key_exists('biaya_lain', $data) ? $data['biaya_lain'] : $transaction->biaya_lain;

            $grandTotal = $hargaNetto + $biayaPpjb + $biayaShm + $biayaLain;

            $data['harga_dasar'] = $hargaDasar;
            $data['harga_netto'] = $hargaNetto;
            $data['grand_total'] = $grandTotal;
            $data['total_amount'] = $grandTotal;

            $transaction->update($data);

            if ($transaction->metode_pembayaran === 'angsuran_in_house') {
                $this->handleUpdateAngsuranInHouse($transaction);
            } elseif ($transaction->metode_pembayaran === 'kpr_bank') {
                // Hapus angsuran berstatus unpaid karena pindah metode pembayaran
                $transaction->angsuran()->whereNotIn('status', ['paid', 'partial'])->delete();
                $this->handleKprBank($transaction);
            } elseif ($transaction->metode_pembayaran === 'cash_keras') {
                // Hapus angsuran berstatus unpaid karena pindah metode pembayaran
                $transaction->angsuran()->whereNotIn('status', ['paid', 'partial'])->delete();
                $this->handleCashKeras($transaction);
            }

            DB::commit();

            return $transaction->load('angsuran');
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle Update Angsuran In House
     */
    private function handleUpdateAngsuranInHouse(SalesTransaction $transaction)
    {
        if (empty($transaction->tenor) || $transaction->tenor < 1) {
            throw new Exception('Tenor wajib diisi untuk metode angsuran_in_house');
        }

        $sisa = $transaction->grand_total;
        if ($transaction->uang_muka_nominal > 0) {
            $sisa -= $transaction->uang_muka_nominal;
        }

        // Cari angsuran yang statusnya sudah terikat pembayaran (paid/partial)
        $lockedAngsurans = $transaction->angsuran()->whereIn('status', ['paid', 'partial'])->get();
        $countLocked = $lockedAngsurans->count();
        $totalNominalLocked = $lockedAngsurans->sum('nominal');

        $sisaHutangBaru = $sisa - $totalNominalLocked;
        $sisaTenorBaru = $transaction->tenor - $countLocked;

        // Bersihkan semua angsuran yang belum dibayar
        $transaction->angsuran()->whereNotIn('status', ['paid', 'partial'])->delete();

        if ($sisaHutangBaru <= 0) {
            // Sudah lunas atau lebih bayar, tidak perlu buat angsuran baru
            return;
        }

        if ($sisaTenorBaru < 1) {
            throw new Exception("Sisa tenor tidak mencukupi untuk tagihan sebesar " . number_format($sisaHutangBaru) . ". Sudah ada $countLocked bulan yang terbayar. Harap tambah jumlah tenor.");
        }

        $nominalPerBulan = $sisaHutangBaru / $sisaTenorBaru;
        $tanggalMulai = $transaction->tanggal_booking ? \Carbon\Carbon::parse($transaction->tanggal_booking) : now();
        $hariJatuhTempo = $transaction->tanggal_jatuh_tempo ?? $tanggalMulai->day;

        for ($i = 1; $i <= $sisaTenorBaru; $i++) {
            $bulanKe = $countLocked + $i;
            $tanggal = $tanggalMulai->copy()->addMonths($bulanKe);
            $tanggal->day(min($hariJatuhTempo, $tanggal->daysInMonth));

            // Fix pembulatan di bulan terakhir
            if ($i == $sisaTenorBaru) {
                $nominalPerBulan = $sisaHutangBaru;
            }

            $transaction->angsuran()->create([
                'bulan_ke' => $bulanKe,
                'tanggal_jatuh_tempo' => $tanggal->toDateString(),
                'nominal' => $nominalPerBulan,
                'sisa_setelah_bayar' => $nominalPerBulan,
                'status' => 'unpaid',
                'keterangan' => 'Angsuran ke-' . $bulanKe
            ]);

            $sisaHutangBaru -= $nominalPerBulan;
        }
    }

    /**
     * Handle Angsuran In House
     */
    private function handleAngsuranInHouse(SalesTransaction $transaction)
    {
        if (empty($transaction->tenor) || $transaction->tenor < 1) {
            throw new Exception('Tenor wajib diisi untuk angsuran_in_house');
        }

        $sisa = $transaction->grand_total;
        
        // Parse tanggal_booking sebagai acuan
        $tanggalMulai = $transaction->tanggal_booking ? \Carbon\Carbon::parse($transaction->tanggal_booking) : now();

        // DP doesn't generate angsuran item, just directly reduce from debt total.
        if ($transaction->uang_muka_nominal > 0) {
            $sisa -= $transaction->uang_muka_nominal;
        }

        // Hitung nominal rata pembagian ke tenor
        $nominalPerBulan = $sisa / $transaction->tenor;

        $hariJatuhTempo = $transaction->tanggal_jatuh_tempo ?? $tanggalMulai->day;

        for ($i = 1; $i <= $transaction->tenor; $i++) {
            $tanggal = $tanggalMulai->copy()->addMonths($i);
            // Handle number of days limit like Feb 28th
            $tanggal->day(min($hariJatuhTempo, $tanggal->daysInMonth));

            // Fix pembulatan di bulan terakhir
            if ($i == $transaction->tenor) {
                $nominalPerBulan = $sisa;
            }

            $transaction->angsuran()->create([
                'bulan_ke' => $i,
                'tanggal_jatuh_tempo' => $tanggal->toDateString(),
                'nominal' => $nominalPerBulan,
                'sisa_setelah_bayar' => $nominalPerBulan,
                'status' => 'unpaid',
                'keterangan' => 'Angsuran ke-' . $i
            ]);

            $sisa -= $nominalPerBulan;
        }
    }

    /**
     * Handle KPR Bank
     */
    private function handleKprBank(SalesTransaction $transaction)
    {
        // KPR: Sisa ditangani bank, tidak ada angsuran dari dev.
        // Asumsi nilai status_kpr = 1 artinya pending/processing
        // if(!$transaction->status_kpr) { $transaction->update(['status_kpr' => 1]); }
    }

    /**
     * Handle Cash Keras
     */
    private function handleCashKeras(SalesTransaction $transaction)
    {
        // Tidak ada angsuran. Semua tagihan dihandle langsung dengan PembayaranFleksibel nanti yang menyasar transaction grand_total
    }

    /**
     * Handle Cancel Refund
     */
    public function handleCancelRefund(SalesTransaction $transaction, $nominal)
    {
        DB::beginTransaction();
        try {
            // Ubah status penjualan menjadi refund
            $transaction->update(['status_penjualan' => 'refund']);

            // Kembalikan kavling ke tersedia
            $kavling = \App\Models\Kavling::find($transaction->kavling_id);
            if ($kavling) {
                $kavling->updateStatus('available');
            }

            // Record pengeluaran di CashFlow
            if ($nominal > 0) {
                \App\Models\CashFlow::create([
                    'tanggal' => now()->toDateString(),
                    'tipe_transaksi' => 'pengeluaran',
                    'kategori' => 'Refund Penjualan',
                    'nominal' => $nominal,
                    'keterangan' => 'Refund pembatalan transaksi: ' . $transaction->nomor_transaksi,
                    // Karena ini bukan dari form UI manual, kita biarkan reference kosong atau menunjuk ke penjualan
                    'referensi_type' => SalesTransaction::class,
                    'referensi_id' => $transaction->id,
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Handle Oper Kredit
     */
    public function handleOperKredit(SalesTransaction $transaction, $newBuyerId, $newSalesId = null)
    {
        $updateData = ['buyer_id' => $newBuyerId];
        if ($newSalesId) {
            $updateData['sales_id'] = $newSalesId;
        }
        $transaction->update($updateData);
    }

    /**
     * Handle Hapus Penjualan
     */
    public function handleHapusPenjualan(SalesTransaction $transaction)
    {
        DB::beginTransaction();
        try {
            // Hapus Payment History dan relasi terkait
            // Menghapus CashFlow yang referensinya mengarah ke PaymentHistory milik transaksi ini
            $paymentHistories = $transaction->paymentHistory()->pluck('id');
            if ($paymentHistories->isNotEmpty()) {
                \App\Models\CashFlow::where('referensi_type', \App\Models\PaymentHistory::class)
                                    ->whereIn('referensi_id', $paymentHistories)
                                    ->delete();
            }

            // Hapus CashFlow yang secara langsung mereferensikan transaksi ini (misalnya dari Refund di atas)
            \App\Models\CashFlow::where('referensi_type', SalesTransaction::class)
                                ->where('referensi_id', $transaction->id)
                                ->delete();

            // Hapus dependensi angsuran, fleksible_payments, payment_history akan otomatis terhapus jika di db ada cascade,
            // tapi kita lakukan manual untuk amannya.
            $transaction->angsuran()->delete();
            $transaction->fleksiblePayments()->delete();
            $transaction->paymentHistory()->delete();

            // Kembalikan kavling ke tersedia
            $kavling = \App\Models\Kavling::find($transaction->kavling_id);
            if ($kavling) {
                $kavling->updateStatus('available');
            }

            // Hapus transaksi bapaknya
            $transaction->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
