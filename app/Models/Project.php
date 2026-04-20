<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $table = 'projects';

    protected $fillable = [
        'owner_id',
        'name',
        'location',
        'notes',
        'total_units',
    ];

    protected $casts = [
        'total_units' => 'integer',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /**
     * Relasi dengan plot (kavling)
     */
    public function plots()
    {
        return $this->hasMany(Plot::class);
    }
}