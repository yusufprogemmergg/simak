<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentHistory extends Model
{
    use HasFactory;

    protected $table = 'payment_history';

    protected $fillable = [
        'sales_transaction_id',
        'tanggal',
        'keterangan',
        'amount',
    ];

    protected $casts = [
        'tanggal' => 'date', 
        'amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan transaksi penjualan
     */
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
    }
}
