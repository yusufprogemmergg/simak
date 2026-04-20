<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Installment extends Model
{
    use HasFactory;

    protected $table = 'installments';

    protected $fillable = [
        'transaction_id',
        'installment_number',
        'due_date',
        'paid_date',
        'amount',
        'remaining_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'due_date'         => 'date',
        'paid_date'        => 'date',
        'amount'           => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    /**
     * Relasi dengan transaksi
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Relasi dengan alokasi pembayaran fleksibel
     */
    public function flexiblePaymentAllocations()
    {
        return $this->hasMany(FlexiblePaymentAllocation::class, 'installment_id');
    }

    /**
     * Cek apakah angsuran sudah lunas
     */
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    /**
     * Cek apakah angsuran sedang dicicil sebagian
     */
    public function isPartial()
    {
        return $this->status === 'partial';
    }

    /**
     * Update status dan sisa berdasarkan total alokasi yang sudah masuk
     */
    public function updatePayment()
    {
        $totalAllocated = $this->flexiblePaymentAllocations()->sum('allocated_amount');

        $remaining = $this->amount - $totalAllocated;
        $status    = 'unpaid';

        if ($totalAllocated > 0 && $remaining > 0) {
            $status = 'partial';
        } elseif ($totalAllocated > 0 && $remaining <= 0) {
            $status    = 'paid';
            $remaining = 0;
            if (!$this->paid_date) {
                $this->paid_date = now()->toDateString();
            }
        }

        $this->update([
            'remaining_amount' => $remaining,
            'status'           => $status,
            'paid_date'        => $this->paid_date,
        ]);

        return true;
    }
}
