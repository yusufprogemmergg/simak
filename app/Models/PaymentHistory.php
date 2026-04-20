<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'payment_histories';

    protected $fillable = [
        'transaction_id',
        'date',
        'notes',
        'amount',
        'referenceable_type',
        'referenceable_id',
    ];

    protected $casts = [
        'date'       => 'date',
        'amount'     => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi polimorfik ke target pembayaran (DP, Angsuran, atau Flexible)
     */
    public function referenceable()
    {
        return $this->morphTo();
    }

    /**
     * Relasi dengan transaksi penjualan
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }
}
