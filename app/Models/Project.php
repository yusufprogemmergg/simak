<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $table = 'project';

    protected $fillable = [
        'owner_id',
        'nama_project',
        'lokasi',
        'catatan',
        'total_unit',
    ];

    protected $casts = [
        'total_unit' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan kavling
     */
    public function kavling()
    {
        return $this->hasMany(Kavling::class);
    }
}