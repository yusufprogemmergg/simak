<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLicense extends Model
{
    protected $table = 'user_licenses';

    protected $fillable = [
        'user_id',
        'license_key',
        'license_type',
        'status',
        'start_date',
        'end_date',
        'max_projects',
        'max_employees',
        'features',
    ];
    
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'features' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Relasi dengan user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Cek apakah lisensi aktif
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && 
               $this->start_date <= now() && 
               $this->end_date >= now();
    }
    
    /**
     * Cek apakah lisensi kadaluarsa
     */
    public function isExpired(): bool
    {
        return $this->end_date < now();
    }
    
    /**
     * Cek sisa hari lisensi
     */
    public function remainingDays(): int
    {
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->end_date, false);
    }
    
    /**
     * Scope untuk lisensi aktif
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }
    
    /**
     * Scope untuk lisensi pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    /**
     * Scope untuk lisensi expired
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', now());
    }
}