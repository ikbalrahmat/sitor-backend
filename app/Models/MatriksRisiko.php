<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MatriksRisiko extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'ops_ti', 
        'keuangan_fraud', 
        'kepatuhan',
        'catatan_ops_ti',
        'catatan_keuangan_fraud',
        'catatan_kepatuhan'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}