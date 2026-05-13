<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Models\User;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Send a reset link to the given user.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $email = $request->email;

        // Generate a random token
        $token = Str::random(60);

        // Save token to password_reset_tokens table
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => Hash::make($token),
                'created_at' => Carbon::now()
            ]
        );

        // Generate the reset link (frontend URL)
        // Adjust the URL according to your React frontend port/domain
        $resetUrl = env('FRONTEND_URL', 'http://localhost:5173') . '/reset-password?token=' . $token . '&email=' . urlencode($email);

        // Send the email
        Mail::send('emails.reset_password', ['resetUrl' => $resetUrl], function ($message) use ($email) {
            $message->to($email);
            $message->subject('Instruksi Pemulihan Akses Akun - Si-Tor');
        });

        return response()->json([
            'message' => 'Email instruksi reset password telah dikirim.'
        ], 200);
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $email = $request->email;
        $token = $request->token;

        // Verify token
        $record = DB::table('password_reset_tokens')->where('email', $email)->first();

        if (!$record || !Hash::check($token, $record->token)) {
            return response()->json([
                'message' => 'Token reset password tidak valid atau sudah kadaluarsa.'
            ], 400);
        }

        // Verify expiration (e.g., 60 minutes)
        if (Carbon::parse($record->created_at)->addMinutes(60)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return response()->json([
                'message' => 'Token reset password sudah kadaluarsa. Silakan request ulang.'
            ], 400);
        }

        // Update user password
        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'Pengguna tidak ditemukan.'
            ], 404);
        }

        $user->password = Hash::make($request->password);
        $user->password_changed_at = Carbon::now();
        $user->save();

        // Delete the token
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return response()->json([
            'message' => 'Password berhasil direset. Silakan login dengan password baru.'
        ], 200);
    }
}
