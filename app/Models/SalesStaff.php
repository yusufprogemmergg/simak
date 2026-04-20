<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesStaff extends Model
{
    protected $table = 'sales_staff';

    protected $fillable = [
        'user_id',
        'owner_id',
        'name',
        'phone',
        'total_units_sold',
        'total_revenue',
    ];

    protected $casts = [
        'total_units_sold' => 'integer',
        'total_revenue'    => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'sales_staff_id');
    }
}
