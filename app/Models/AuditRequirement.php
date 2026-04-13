<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditRequirement extends Model
{
    use HasFactory;

    protected $fillable = ['audit_id', 'jenis_sertifikat', 'jumlah_kebutuhan'];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }
}