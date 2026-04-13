<?php

namespace App\Services;

use App\Models\FleksiblePayment;
use App\Models\SalesTransaction;
use App\Models\AlokasiPembayaranFleksibel;
use Illuminate\Support\Facades\DB;
use Exception;

class FleksibelPaymentService
{
    /**
     * Proses terima/approve pembayaran fleksibel.
     * Fungsi ini akan melakukan alokasi FIFO ke angsuran jika metode bayarnya angsuran.
     */
    public function terimaPembayaran(FleksiblePayment $pembayaran)
    {
        if ($pembayaran->status === 'diterima') {
            throw new Exception("Pembayaran sudah pernah diterima sebelumnya");
        }

        DB::beginTransaction();

        try {
            // Update status menjadi diterima
            $pembayaran->update(['status' => 'diterima']);

            $transaction = $pembayaran->penjualan;
            $transaction->increment('total_paid', (float)$pembayaran->nominal);

            \App\Models\PaymentHistory::create([
                'sales_transaction_id' => $transaction->id,
                'tanggal' => now()->toDateString(),
                'keterangan' => 'Flexible Payment',
                'amount' => $pembayaran->nominal
            ]);

            // Jika angsuran in house, lakukan auto-alokasi FIFO
            if ($transaction->metode_pembayaran === 'angsuran_in_house') {
                $this->allocateToAngsuran($transaction, $pembayaran);
            }

            // Setelah alokasi (atau untuk metode lain), cek apakah lunas
            $this->checkAndSetPaidOff($transaction);

            DB::commit();

            return true;
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
                'pembayaran_fleksibel_id' => $pembayaran->id,
                'angsuran_id' => $angsuran->id,
                'nominal_dialokasikan' => $nominalAlokasi,
            ]);

            // Update status/jumlah pada model Angsuran terkait
            // Ini akan memanggil logic yang mengurangkan sisa_setelah_bayar
            $angsuran->updatePembayaran();

            // Kurangi sisa dana si customer
            $sisaDanaMembayar -= $nominalAlokasi;
        }

        // Kalau sisaDanaMembayar > 0, uangnya sisa bisa dianggap kembalian / overpayment
    }

    /**
     * Mengecek dan auto set transaksi menjadi paid_off
     */
    public function checkAndSetPaidOff(SalesTransaction $transaction)
    {
        // Menyegarkan model untuk mendapatkan total_paid terbaru
        $transaction->refresh();
        
        // Jika lunas
        if ($transaction->total_paid >= $transaction->grand_total) {
            $transaction->update([
                'status_penjualan' => 'paid_off',
                'tanggal_pelunasan' => now()->toDateString(),
            ]);
        }
    }
}
