<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FlexiblePaymentAllocation extends Model
{
    use HasFactory;

    protected $table = 'flexible_payment_allocations';

    protected $fillable = [
        'flexible_payment_id',
        'installment_id',
        'allocated_amount',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Relasi dengan pembayaran fleksibel
     */
    public function flexiblePayment()
    {
        return $this->belongsTo(FlexiblePayment::class, 'flexible_payment_id');
    }

    /**
     * Relasi dengan angsuran
     */
    public function installment()
    {
        return $this->belongsTo(Installment::class, 'installment_id');
    }
}
