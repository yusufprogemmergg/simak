<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Angsuran extends Model
{
    use HasFactory;

    protected $table = 'angsuran';

    protected $fillable = [
        'penjualan_id',
        'bulan_ke',
        'tanggal_jatuh_tempo',
        'tanggal_bayar',
        'nominal',
        'sisa_setelah_bayar',
        'status',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'date',
        'nominal' => 'decimal:2',
        'sisa_setelah_bayar' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi dengan penjualan
     */
    public function penjualan()
    {
        return $this->belongsTo(SalesTransaction::class, 'penjualan_id');
    }

    /**
     * Relasi dengan alokasi pembayaran fleksibel
     */
    public function alokasiPembayaran()
    {
        return $this->hasMany(AlokasiPembayaranFleksibel::class);
    }

    /**
     * Cek apakah angsuran sudah lunas
     */
    public function isLunas()
    {
        return $this->status === 'lunas';
    }

    /**
     * Cek apakah angsuran terlambat
     */
    public function isTerlambat()
    {
        return $this->status === 'terlambat';
    }

    /**
     * Update status dan sisa berdasarkan total alokasi yang sudah masuk
     */
    public function updatePembayaran()
    {
        $totalAlokasi = $this->alokasiPembayaran()->sum('nominal_dialokasikan');
        
        $sisa = $this->nominal - $totalAlokasi;
        $status = 'unpaid';
        
        if ($totalAlokasi > 0 && $sisa > 0) {
            $status = 'partial';
        } elseif ($totalAlokasi > 0 && $sisa <= 0) {
            $status = 'paid';
            $sisa = 0;
            if (!$this->tanggal_bayar) {
                $this->tanggal_bayar = now()->toDateString();
            }
        }

        $this->update([
            'sisa_setelah_bayar' => $sisa,
            'status' => $status,
            'tanggal_bayar' => $this->tanggal_bayar,
        ]);
        
        return true;
    }
}