<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // FILTERING BERDASARKAN ROLE
        if ($user->role === 'Super Admin') {
            $users = User::where('role', 'Admin')->get();
        } elseif ($user->role === 'Admin') {
            $users = User::whereIn('role', ['User', 'Manajemen'])->get();
        } elseif ($user->role === 'Manajemen') {
            $users = User::where('role', 'User')->get();
        } else {
            $users = User::where('id', $user->id)->get();
        }
        
        return response()->json($users);
    }

    public function store(Request $request)
    {
        // 1. TAMBAHKAN VALIDASI FILE (Requirement 26)
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Wajib gambar, max 2MB
        ]);

        $data = $request->all();
        $data['password'] = Hash::make('Sitor123!@'); 
        $data['is_first_login'] = true;
        $data['status_keaktifan'] = $request->status_keaktifan == 'Aktif' ? true : false;
        
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        
        $user = User::create($data);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'USER_CREATED',
            'description' => 'Membuat akun pengguna baru untuk email: ' . $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'User berhasil dibuat', 'user' => $user]);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        // 2. TAMBAHKAN VALIDASI FILE DI UPDATE (Requirement 26)
        $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Wajib gambar, max 2MB
        ]);

        $data = $request->all();
        $data['status_keaktifan'] = $request->status_keaktifan == 'Aktif' ? true : false;
        
        if ($request->hasFile('photo')) {
            if ($user->photo) {
                Storage::disk('public')->delete($user->photo);
            }
            $data['photo'] = $request->file('photo')->store('photos', 'public');
        }
        
        $user->update($data);

        ActivityLog::create([
            'user_id' => $request->user()->id, 
            'email' => $request->user()->email,
            'event_type' => 'USER_UPDATED',
            'description' => 'Memperbarui data akun pengguna untuk email: ' . $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'User berhasil diupdate', 'user' => $user]);
    }

    public function destroy(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $deletedEmail = $user->email; 
        
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }
        
        $user->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id, 
            'email' => $request->user()->email,
            'event_type' => 'USER_DELETED',
            'description' => 'Menghapus akun pengguna dari sistem dengan email: ' . $deletedEmail,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'User berhasil dihapus']);
    }

    public function unlock(Request $request, $id)
    {
        // Hanya Super Admin yang bisa unlock (bisa dicek lewat middleware/policy, ini penjagaan tambahan)
        if ($request->user()->role !== 'Super Admin') {
            return response()->json(['message' => 'Unauthorized. Hanya Super Admin yang dapat membuka kunci akun.'], 403);
        }

        $user = User::findOrFail($id);
        
        if (!$user->is_locked) {
            return response()->json(['message' => 'Akun pengguna ini tidak dalam keadaan terkunci.'], 400);
        }

        $user->update([
            'is_locked' => false,
            'failed_login_attempts' => 0
        ]);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'USER_UNLOCKED',
            'description' => 'Membuka kunci (unlock) akun pengguna dengan email: ' . $user->email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Kunci akun berhasil dibuka.', 'user' => $user]);
    }
}