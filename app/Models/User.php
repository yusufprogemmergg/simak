<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'password',
        'no_telepon',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at'     => 'datetime',
        'email_verified_at' => 'datetime',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        // last_login_ip adalah string IP address, tidak perlu di-cast
    ];

    /**
     * Relasi dengan license
     */
    public function license()
    {
        return $this->hasOne(UserLicense::class);
    }

    public function profilePerusahaan()
    {
        return $this->hasOne(ProfilePerusahaan::class, 'owner_id');
    }

    /**
     * Relasi dengan penjualan (sebagai sales)
     */
    public function sales_transactions()
    {
        return $this->hasMany(SalesTransaction::class, 'sales_id');
    }

    /**
     * Relasi dengan pembayaran fleksibel yang dibuat
     */
    public function pembayaranFleksibel()
    {
        return $this->hasMany(FleksiblePayment::class, 'created_by');
    }

    /**
     * Cek apakah user memiliki lisensi aktif
     */
    public function hasActiveLicense()
    {
        return $this->license && $this->license->isActive();
    }

    /**
     * Cek apakah user bisa membuat project baru
     */
    public function canCreateProject()
    {
        if (!$this->hasActiveLicense()) {
            return false;
        }

        $projectCount = Project::where('created_by', $this->id)->count();
        return $projectCount < $this->license->max_projects;
    }

    /**
     * Cek apakah user adalah owner
     */
    public function isOwner()
    {
        return $this->role === 'owner';
    }

    /**
     * Cek apakah user adalah salesman
     */
    public function isSalesman()
    {
        return $this->role === 'salesman';
    }

    public function sales()
    {
        return $this->hasOne(Sales::class);
    }

    public function salesTeam()
    {
        return $this->hasMany(Sales::class, 'owner_id');
    }
}