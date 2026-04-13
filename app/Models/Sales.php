<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    protected $fillable = [
        'user_id',
        'owner_id',
        'phone',
        'unit_sales',
        'total_revenue',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function SalesTransaction()
    {
        return $this->hasMany(SalesTransaction::class);
    }
}