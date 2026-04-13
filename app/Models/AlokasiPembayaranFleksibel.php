<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlokasiPembayaranFleksibel extends Model
{
    use HasFactory;

    protected $table = 'alokasi_pembayaran_fleksibel';

    protected $fillable = [
        'fleksible_payment_id',
        'angsuran_id',
        'nominal_dialokasikan',
    ];

    protected $casts = [
        'nominal_dialokasikan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan pembayaran fleksibel
     */
    public function fleksiblePayment()
    {
        return $this->belongsTo(FleksiblePayment::class, 'fleksible_payment_id');
    }

    /**
     * Relasi dengan angsuran
     */
    public function angsuran()
    {
        return $this->belongsTo(Angsuran::class);
    }
}