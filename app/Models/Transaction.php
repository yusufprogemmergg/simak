<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'transactions';

    protected $fillable = [
        'transaction_number',
        'plot_id',
        'buyer_id',
        'sales_staff_id',
        'owner_id',
        'payment_method',
        'booking_date',
        'base_price',
        'discount_amount',
        'net_price',
        'ppjb_fee',
        'shm_fee',
        'other_fees',
        'booking_fee',
        'grand_total',
        'is_unit_included',
        'tenor_months',
        'due_day',
        'down_payment_percent',
        'down_payment_amount',
        'installment_estimate',
        'total_amount',
        'total_paid',
        'total_flexible_paid',
        'notes',
        'status',
        'dp_status',
        'mortgage_status',
        'settlement_date',
        'cancellation_notes',
    ];

    protected $casts = [
        'booking_date'      => 'date',
        'settlement_date'   => 'date',

        'base_price'        => 'decimal:2',
        'discount_amount'   => 'decimal:2',
        'net_price'         => 'decimal:2',
        'ppjb_fee'          => 'decimal:2',
        'shm_fee'           => 'decimal:2',
        'other_fees'        => 'decimal:2',
        'booking_fee'       => 'decimal:2',
        'grand_total'       => 'decimal:2',
        'total_amount'      => 'decimal:2',
        'total_paid'        => 'decimal:2',
        'total_flexible_paid' => 'decimal:2',

        'down_payment_percent' => 'decimal:2',
        'down_payment_amount'  => 'decimal:2',

        'is_unit_included'  => 'boolean',

        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = ['installment_estimate'];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    public function plot()
    {
        return $this->belongsTo(Plot::class, 'plot_id');
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function salesStaff()
    {
        return $this->belongsTo(SalesStaff::class, 'sales_staff_id');
    }

    // Multi-company ownership
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function installments()
    {
        return $this->hasMany(Installment::class, 'transaction_id');
    }

    public function flexiblePayments()
    {
        return $this->hasMany(FlexiblePayment::class, 'transaction_id');
    }

    public function paymentHistories()
    {
        return $this->hasMany(PaymentHistory::class, 'transaction_id');
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($transaction) {
            // Auto set owner (multi-tenant)
            if (!$transaction->owner_id && auth()->check()) {
                $transaction->owner_id = auth()->id();
            }

            // Auto generate transaction_number berbasis timestamp
            if (!$transaction->transaction_number) {
                $transaction->transaction_number = 'TRX-' . now()->format('YmdHisv');
            }

            // Auto set total_amount if missing
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

    public function isPaidOff()
    {
        return $this->status === 'paid_off';
    }

    public function isActive()
    {
        return $this->status === 'active';
    }

    public function totalPaymentMade()
    {
        return $this->total_paid;
    }

    public function remainingBalance()
    {
        return $this->grand_total - $this->totalPaymentMade();
    }

    /**
     * Virtual attribute — estimasi angsuran per bulan
     * Dihitung dari: (grand_total - down_payment_amount) / tenor_months
     */
    public function getInstallmentEstimateAttribute()
    {
        if ($this->payment_method !== 'installment' || empty($this->tenor_months) || $this->tenor_months <= 0) {
            return 0;
        }

        $principal = $this->grand_total - ($this->down_payment_amount ?? 0);
        return max(0, $principal / $this->tenor_months);
    }
}
