<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlexiblePayment extends Model
{
    use HasFactory;

    protected $table = 'flexible_payments';

    protected $fillable = [
        'transaction_id',
        'amount',
        'paid_date',
        'notes',
        'payment_method',
        'payment_proof',
        'status',
        'created_by',
    ];

    protected $casts = [
        'amount'     => 'decimal:2',
        'paid_date'  => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan transaksi
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Relasi dengan user yang membuat
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi dengan alokasi pembayaran
     */
    public function allocations()
    {
        return $this->hasMany(FlexiblePaymentAllocation::class, 'flexible_payment_id');
    }
}
