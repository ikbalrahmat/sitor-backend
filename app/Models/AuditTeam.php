<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTeam extends Model
{
    use HasFactory;

    protected $fillable = ['audit_id', 'user_id', 'peran'];

    public function audit()
    {
        return $this->belongsTo(Audit::class);
    }

    // Relasi ke tabel User agar kita tahu siapa nama auditornya
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}