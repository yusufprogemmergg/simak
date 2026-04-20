<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CompanyProfile extends Model
{
    use HasFactory;

    protected $table = 'company_profiles';

    protected $fillable = [
        'owner_id',
        'name',
        'npwp',
        'email',
        'phone',
        'address',
        'logo_path',
        'admin_signature_name',
        'print_footer',
        'invoice_format',
        'receipt_format',
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
