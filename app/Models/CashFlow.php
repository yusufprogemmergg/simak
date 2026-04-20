<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashFlow extends Model
{
    use HasFactory;

    protected $table = 'cash_flows';

    protected $fillable = [
        'owner_id',
        'date',
        'type',
        'category',
        'amount',
        'notes',
        'referenceable_type',
        'referenceable_id',
    ];

    protected $casts = [
        'date'   => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Auto set owner_id saat create
     */
    protected static function booted()
    {
        static::creating(function ($cashFlow) {
            if (!$cashFlow->owner_id && auth()->check()) {
                $cashFlow->owner_id = auth()->id();
            }
        });
    }

    /**
     * Relasi ke owner (user)
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the parent referenceable model (e.g. Transaction or PaymentHistory).
     */
    public function referenceable()
    {
        return $this->morphTo();
    }
}
