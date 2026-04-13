<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PasswordHistory;
use App\Models\ActivityLog; 
use Carbon\Carbon;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            ActivityLog::create([
                'email' => $request->email,
                'event_type' => 'LOGIN_FAILED',
                'description' => 'Percobaan login gagal. Email tidak terdaftar di sistem.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json(['message' => 'Email atau password salah!'], 401);
        }

        if ($user->is_locked) {
            ActivityLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'event_type' => 'LOGIN_BLOCKED',
                'description' => 'Mencoba login tetapi akun sedang dalam status terblokir.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'message' => 'Akun Anda diblokir karena terlalu banyak percobaan gagal. Silakan hubungi Admin.'
            ], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->failed_login_attempts += 1;
            
            if ($user->failed_login_attempts >= 3) {
                $user->is_locked = true;
                $user->save();
                
                ActivityLog::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'event_type' => 'ACCOUNT_LOCKED',
                    'description' => 'Akun otomatis diblokir karena gagal login 3 kali berturut-turut.',
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]);

                return response()->json([
                    'message' => 'Akun Anda otomatis diblokir karena gagal login 3 kali berturut-turut.'
                ], 403);
            }
            
            $user->save();
            
            ActivityLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'event_type' => 'LOGIN_FAILED',
                'description' => 'Gagal login, password salah. Percobaan ke-' . $user->failed_login_attempts,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $sisa = 3 - $user->failed_login_attempts;
            return response()->json([
                'message' => "Email atau password salah! Sisa percobaan Anda: {$sisa} kali."
            ], 401);
        }

        $user->failed_login_attempts = 0;
        $user->save();

        $requires_password_change = false;
        $login_message = 'Login berhasil.';

        if ($user->is_first_login) {
            $requires_password_change = true;
            $login_message = 'Ini adalah login pertama Anda. Anda wajib mengganti password.';
        } elseif ($user->password_changed_at && Carbon::parse($user->password_changed_at)->addDays(90)->isPast()) {
            $requires_password_change = true;
            $login_message = 'Password Anda sudah kedaluwarsa (lebih dari 90 hari). Silakan ganti password.';
        }

        // ==========================================
        // MENCEGAH MULTI-LOGIN (Requirement 22)
        // ==========================================
        // Hapus semua token yang pernah dimiliki user ini sebelumnya
        $user->tokens()->delete();

        // Baru buat token akses yang baru untuk sesi kali ini
        $token = $user->createToken('auth_token')->plainTextToken;

        ActivityLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'event_type' => 'LOGIN_SUCCESS',
            'description' => 'Pengguna berhasil masuk ke sistem.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json([
            'message' => $login_message,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
            'requires_password_change' => $requires_password_change
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols(),
            ],
        ], [
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'new_password.min' => 'Password minimal harus 8 karakter.',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'event_type' => 'PASSWORD_CHANGE_FAILED',
                'description' => 'Gagal mengubah password. Password lama salah.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json(['message' => 'Password saat ini (lama) salah.'], 400);
        }

        $histories = PasswordHistory::where('user_id', $user->id)
                        ->latest()
                        ->take(3)
                        ->get();

        foreach ($histories as $history) {
            if (Hash::check($request->new_password, $history->password)) {
                return response()->json([
                    'message' => 'Keamanan: Anda tidak boleh menggunakan 3 password yang pernah dipakai sebelumnya.'
                ], 400);
            }
        }

        PasswordHistory::create([
            'user_id' => $user->id,
            'password' => $user->password 
        ]);

        $user->password = Hash::make($request->new_password); 
        $user->is_first_login = false;
        $user->password_changed_at = Carbon::now();
        $user->save();

        ActivityLog::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'event_type' => 'PASSWORD_CHANGED',
            'description' => 'Pengguna berhasil mengubah password akun.',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Password berhasil diubah. Silakan gunakan password baru untuk aktivitas selanjutnya.']);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'email' => $user->email,
                'event_type' => 'LOGOUT',
                'description' => 'Pengguna keluar dari sistem.',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout berhasil']);
    }
}