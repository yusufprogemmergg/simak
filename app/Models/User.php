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
        'phone',
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
    ];

    /**
     * Relasi dengan license
     */
    public function license()
    {
        return $this->hasOne(UserLicense::class);
    }

    /**
     * Relasi dengan company profile
     */
    public function companyProfile()
    {
        return $this->hasOne(CompanyProfile::class, 'owner_id');
    }

    /**
     * Relasi dengan transaksi (sebagai sales staff)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'sales_staff_id');
    }

    /**
     * Relasi dengan flexible payment yang dibuat
     */
    public function flexiblePayments()
    {
        return $this->hasMany(FlexiblePayment::class, 'created_by');
    }

    /**
     * Cek apakah user memiliki lisensi aktif
     */
    public function hasActiveLicense()
    {
        return $this->license && $this->license->isActive();
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

    /**
     * Relasi dengan sales staff record
     */
    public function salesStaff()
    {
        return $this->hasOne(SalesStaff::class);
    }

    /**
     * Relasi dengan semua sales staff dalam tim (untuk owner)
     */
    public function salesTeam()
    {
        return $this->hasMany(SalesStaff::class, 'owner_id');
    }
}