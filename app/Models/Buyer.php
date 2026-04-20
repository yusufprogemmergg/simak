<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Buyer extends Model
{
    use HasFactory;

    protected $table = 'buyers';

    protected $fillable = [
        'owner_id',
        'name',
        'phone',
        'email',
        'address',
        'nik',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan transaksi
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'buyer_id');
    }
}