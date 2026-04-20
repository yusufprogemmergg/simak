<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plot extends Model
{
    use HasFactory;

    protected $table = 'plots';

    protected $fillable = [
        'project_id',
        'plot_number',
        'area',
        'base_price',
        'status',
    ];

    protected $casts = [
        'area'       => 'decimal:2',
        'base_price' => 'decimal:2',
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
     * Relasi dengan transaksi
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'plot_id');
    }

    /**
     * Cek apakah plot tersedia
     */
    public function isAvailable()
    {
        return $this->status === 'available';
    }

    /**
     * Ubah status plot
     */
    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
    }
}
