<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Kavling extends Model
{
    use HasFactory;

    protected $table = 'kavling';

    protected $fillable = [
        'project_id',
        'blok_nomor',
        'luas',
        'harga_dasar',
        'status',
    ];

    protected $casts = [
        'luas' => 'decimal:2',
        'harga_dasar' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Relasi dengan penjualan
     */
    public function SalesTransaction()
    {
        return $this->hasMany(SalesTransaction::class, 'kavling_id');
    }

    /**
     * Cek apakah kavling tersedia
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Ubah status kavling
     */
    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }
}