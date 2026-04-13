<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserLicense extends Model
{
    protected $table = 'user_licenses';

    protected $fillable = [
        'user_id',    // nullable — null = key belum dipakai
        'license_key',
        'note',       // catatan super_admin (untuk siapa key ini)
        'status',     // 'available' | 'active' | 'revoked'
        'start_date', // diisi saat owner mendaftar
    ];

    protected $casts = [
        'start_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELATIONS
    |--------------------------------------------------------------------------
    */

    /** Owner yang menggunakan license ini */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    /**
     * Cek apakah license sudah dipakai
     */
    public function isUsed(): bool
    {
        return !is_null($this->user_id);
    }

    /**
     * Cek apakah license tersedia untuk dipakai owner baru
     */
    public function isAvailable(): bool
    {
        return is_null($this->user_id) && $this->status === 'available';
    }

    /**
     * Cek apakah license aktif (dipakai oleh owner)
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && !is_null($this->user_id);
    }

    /*
    |--------------------------------------------------------------------------
    | SCOPES
    |--------------------------------------------------------------------------
    */

    /** Key yang belum dipakai */
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')->whereNull('user_id');
    }

    /** Key yang sudah aktif dipakai owner */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->whereNotNull('user_id');
    }

    /** Key yang dinonaktifkan */
    public function scopeRevoked($query)
    {
        return $query->where('status', 'revoked');
    }

    /*
    |--------------------------------------------------------------------------
    | STATIC GENERATOR
    |--------------------------------------------------------------------------
    */

    /**
     * Generate license key unik
     * Format: SIMAK-YYYYMM-XXXXXXXX (huruf besar + angka)
     */
    public static function generateKey(): string
    {
        do {
            $key = 'SIMAK-' . now()->format('Ym') . '-' . strtoupper(Str::random(8));
        } while (self::where('license_key', $key)->exists());

        return $key;
    }
}