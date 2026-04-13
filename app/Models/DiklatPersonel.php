<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiklatPersonel extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'tahun', 'jenis', 
        'rencana_diklat', 'rencana_penyelenggara', 'rencana_jadwal',
        'realisasi_diklat', 'realisasi_penyelenggara', 'realisasi_jadwal',
        'sertifikat_path', 'nomor_sertifikat', 'tanggal_sertifikat', 
        'tanggal_expired', 'nilai_cpe', 'biaya', 'kualifikasi'
    ];

    // Relasi balik ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}