<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FleksiblePayment extends Model
{
    use HasFactory;

    protected $table = 'fleksible_payments';

    protected $fillable = [
        'sales_transaction_id',
        'nominal',
        'tanggal_bayar',
        'catatan',
        'created_by',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'tanggal_bayar' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan penjualan
     */
    public function salesTransaction()
    {
        return $this->belongsTo(SalesTransaction::class, 'sales_transaction_id');
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
    public function alokasiPembayaran()
    {
        return $this->hasMany(AlokasiPembayaranFleksibel::class);
    }

}