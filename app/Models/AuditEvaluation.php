<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditEvaluation extends Model
{
    use HasFactory;

    protected $fillable = [
        'audit_topic_id',
        'user_id',
        'status',
        'keterangan',
    ];

    public function topic()
    {
        return $this->belongsTo(AuditTopic::class, 'audit_topic_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
