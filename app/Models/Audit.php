<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    protected $fillable = ['nama_audit', 'tahun', 'tanggal_mulai', 'tanggal_selesai', 'status'];

    // 1 Audit punya BANYAK Syarat
    public function requirements()
    {
        return $this->hasMany(AuditRequirement::class);
    }

    // 1 Audit punya BANYAK Anggota Tim
    public function teams()
    {
        return $this->hasMany(AuditTeam::class);
    }
}