<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'password',
        'jabatan',
        'instansi',
        'unit_kerja',
        'np',
        'status_kepegawaian',
        'status_keaktifan',
        'role',
        'preferences',
        // Tambahan Security
        'is_first_login',
        'password_changed_at',
        'failed_login_attempts',
        'is_locked',
        'photo', // <-- Tambahkan field photo
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password_changed_at' => 'datetime', // Pastikan formatnya datetime
            'password' => 'hashed', // <-- Laravel otomatis mengenkripsi password
            'is_first_login' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }
}