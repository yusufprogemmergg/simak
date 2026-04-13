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
        'username',
        'no_telepon',
        'email',
        'alamat',
        'nik',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan penjualan
     */
    public function SalesTransaction()
    {
        return $this->hasMany(SalesTransaction::class, 'buyer_id');
    }
}