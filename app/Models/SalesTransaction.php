<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SalesTransaction extends Model
{
    use HasFactory;

    protected $table = 'sales_transactions';

    protected $fillable = [
        'nomor_transaksi',
        'kavling_id',
        'buyer_id',
        'sales_id',
        'owner_id',
        'metode_pembayaran',
        'tanggal_booking',
        'harga_dasar',
        'promo_diskon',
        'harga_netto',
        'biaya_ppjb',
        'biaya_shm',
        'biaya_lain',
        'booking_fee',
        'grand_total',
        'sudah_termasuk_unit',
        'tenor',
        'tanggal_jatuh_tempo',
        'uang_muka_persen',
        'uang_muka_nominal',
        'total_amount',
        'total_paid',
        'total_flexible_paid',
        'catatan_transaksi',
        'status_penjualan',
        'status_dp',
        'status_kpr',
        'tanggal_pelunasan',
        'keterangan_batal',
    ];

    protected $casts = [
        'tanggal_booking' => 'date',
        'tanggal_pelunasan' => 'date',

        'harga_dasar' => 'decimal:2',
        'promo_diskon' => 'decimal:2',
        'harga_netto' => 'decimal:2',
        'biaya_ppjb' => 'decimal:2',
        'biaya_shm' => 'decimal:2',
        'biaya_lain' => 'decimal:2',
        'booking_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',

        'uang_muka_persen' => 'decimal:2',
        'uang_muka_nominal' => 'decimal:2',

        'sudah_termasuk_unit' => 'boolean',
        'status_kpr' => 'integer',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['estimasi_angsuran'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function kavling()
    {
        return $this->belongsTo(Kavling::class);
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function sales()
    {
        return $this->belongsTo(Sales::class, 'sales_id');
    }

    // 🔥 PENTING (multi company)
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function angsuran()
    {
        return $this->hasMany(Angsuran::class, 'penjualan_id');
    }

    public function fleksiblePayments()
    {
        return $this->hasMany(FleksiblePayment::class, 'sales_transaction_id');
    }

    public function paymentHistory()
    {
        return $this->hasMany(PaymentHistory::class, 'sales_transaction_id');
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($transaction) {
            // 🔥 Auto set owner (best practice multi tenant)
            if (!$transaction->owner_id && auth()->check()) {
                $transaction->owner_id = auth()->id();
            }

            // 🔥 Auto generate nomor transaksi berbasis timestamp (untuk menjamin keunikan)
            if (!$transaction->nomor_transaksi) {
                // Format: TRX-20240417123045999
                $transaction->nomor_transaksi = 'TRX-' . now()->format('YmdHisv');
            }

            // 🔥 Auto set total_amount if missing
            if (!$transaction->total_amount) {
                $transaction->total_amount = $transaction->grand_total;
            }
        });

    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    public function isLunas()
    {
        return $this->status_penjualan === 'paid_off';
    }

    public function isActive()
    {
        return $this->status_penjualan === 'active';
    }

    public function totalPembayaran()
    {
        return $this->total_paid;
    }

    public function sisaPembayaran()
    {
        return $this->grand_total - $this->totalPembayaran();
    }

    /**
     * Virtual attribute untuk estimasi angsuran
     * Dihitung dari: (grand_total - uang_muka_nominal) / tenor
     */
    public function getEstimasiAngsuranAttribute()
    {
        if ($this->metode_pembayaran !== 'angsuran_in_house' || empty($this->tenor) || $this->tenor <= 0) {
            return 0;
        }

        $sisaPokok = $this->grand_total - ($this->uang_muka_nominal ?? 0);
        return max(0, $sisaPokok / $this->tenor);
    }
}