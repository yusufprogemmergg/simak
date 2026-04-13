<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfilePerusahaan extends Model
{
    use HasFactory;

    protected $table = 'profile_perusahaan';

    protected $fillable = [
        'owner_id',
        'name',
        'npwp',
        'email',
        'telepon',
        'alamat',
        'logo',
        'nama_ttd_admin',
        'catatan_kaki_cetakan',
        'format_faktur',
        'format_kuitansi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}