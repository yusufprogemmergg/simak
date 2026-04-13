<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected $table = 'cash_flows';

    protected $fillable = [
        'tanggal',
        'tipe_transaksi',
        'kategori',
        'nominal',
        'keterangan',
        'referensi_type',
        'referensi_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
    ];

    /**
     * Get the parent referensi model (e.g. SalesTransaction or PaymentHistory).
     */
    public function referensi()
    {
        return $this->morphTo();
    }
}
