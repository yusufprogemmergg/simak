<?php

namespace App\Services;

use App\Models\FleksiblePayment;
use App\Models\SalesTransaction;
use App\Models\AlokasiPembayaranFleksibel;
use Illuminate\Support\Facades\DB;
use Exception;

class FleksiblePaymentService
{
    /**
     * Proses terima/approve pembayaran fleksibel.
     * Fungsi ini menyimpan langsung pembayaran dan melakukan alokasi FIFO ke angsuran.
     */
    public function processPayment(array $data)
    {
        DB::beginTransaction();

        try {
            $transaction = SalesTransaction::findOrFail($data['sales_transaction_id']);

            // Validate nominal tidak melebihi sisa kekurangan pembayaran
            $sisaKekurangan = $transaction->total_amount - $transaction->total_paid;
            if ($data['nominal'] > $sisaKekurangan) {
                throw new Exception("Nominal pembayaran tidak boleh lebih dari sisa kekurangan perhitungan (".number_format($sisaKekurangan).")");
            }

            // Create record fleksible payment
            $pembayaran = FleksiblePayment::create([
                'sales_transaction_id' => $transaction->id,
                'nominal' => $data['nominal'],
                'tanggal_bayar' => $data['tanggal_bayar'],
                'catatan' => $data['catatan'] ?? null,
                'created_by' => auth()->id() ?? null,
            ]);

            // Update ke parent transaction
            $transaction->increment('total_paid', (float)$pembayaran->nominal);
            $transaction->increment('total_flexible_paid', (float)$pembayaran->nominal);

            // Record Payment History for Flexible Payment
            $paymentHistory = \App\Models\PaymentHistory::create([
                'sales_transaction_id' => $transaction->id,
                'tanggal' => $pembayaran->tanggal_bayar,
                'keterangan' => 'Flexible Payment',
                'amount' => $pembayaran->nominal
            ]);

            // Auto Journal ke Buku Kas
            \App\Models\CashFlow::create([
                'tanggal' => $pembayaran->tanggal_bayar,
                'tipe_transaksi' => 'pemasukan',
                'kategori' => 'Pembayaran Fleksibel',
                'nominal' => $pembayaran->nominal,
                'keterangan' => 'Pembayaran Fleksibel Transaksi: ' . $transaction->nomor_transaksi,
                'referensi_type' => \App\Models\PaymentHistory::class,
                'referensi_id' => $paymentHistory->id,
            ]);

            // Jika angsuran in house, lakukan auto-alokasi waterfall
            if ($transaction->metode_pembayaran === 'angsuran_in_house') {
                $this->allocateToAngsuran($transaction, $pembayaran);
            }

            // Setelah alokasi (atau untuk metode lain), cek apakah lunas
            $this->checkAndSetPaidOff($transaction);

            DB::commit();

            return $pembayaran;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Alokasi sistem FIFO secara otomatis ke tabel angsuran
     */
    private function allocateToAngsuran(SalesTransaction $transaction, FleksiblePayment $pembayaran)
    {
        $sisaDanaMembayar = $pembayaran->nominal;

        // Ambil urutan angsuran terlama (berdasarkan bulan_ke) yang belum lunas
        $daftarAngsuran = $transaction->angsuran()
            ->whereIn('status', ['unpaid', 'partial'])
            ->orderBy('bulan_ke', 'asc')
            ->get();

        foreach ($daftarAngsuran as $angsuran) {
            if ($sisaDanaMembayar <= 0) break;

            // Hitung kebutuhan angsuran ini
            $kebutuhan = $angsuran->sisa_setelah_bayar;

            // Cek porsi untuk dibayar pada angsuran ini
            $nominalAlokasi = min($sisaDanaMembayar, $kebutuhan);

            // Buat record alokasi pembayaran pivot
            AlokasiPembayaranFleksibel::create([
                'fleksible_payment_id' => $pembayaran->id,
                'angsuran_id' => $angsuran->id,
                'nominal_dialokasikan' => $nominalAlokasi,
            ]);

            // Update status/jumlah pada model Angsuran terkait
            // Ini akan memanggil logic yang mengurangkan sisa_setelah_bayar berdasarkan alokasi_id
            $angsuran->updatePembayaran();

            // Kurangi sisa dana si customer
            $sisaDanaMembayar -= $nominalAlokasi;
        }
    }

    /**
     * Mengecek dan auto set transaksi menjadi paid_off
     */
    public function checkAndSetPaidOff(SalesTransaction $transaction)
    {
        // Menyegarkan model untuk mendapatkan total_paid terbaru
        $transaction->refresh();
        
        // Jika lunas
        if ($transaction->total_paid >= $transaction->total_amount) {
            $transaction->update([
                'status_penjualan' => 'paid_off',
                'tanggal_pelunasan' => now()->toDateString(),
            ]);
        }
    }
}
