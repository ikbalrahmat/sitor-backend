<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_kerja',
        'name',
        'kriteria',
    ];

    public function evaluations()
    {
        return $this->hasMany(AuditEvaluation::class);
    }
}
