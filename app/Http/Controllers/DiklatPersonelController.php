<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DiklatPersonel;
use Illuminate\Support\Facades\Storage;

class DiklatPersonelController extends Controller
{
    // Ambil data (Super Admin bisa lihat semua, User biasa cuma lihat miliknya sendiri)
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Super Admin, Admin, dan Manajemen bisa melihat SEMUA data diklat
        if (in_array($user->role, ['Super Admin', 'Admin', 'Manajemen'])) {
            $diklat = DiklatPersonel::with('user:id,nama')->get();
        } else {
            // User hanya bisa melihat datanya sendiri
            $diklat = DiklatPersonel::where('user_id', $user->id)->with('user:id,nama')->get();
        }

        return response()->json($diklat);
    }

    // Simpan data baru
    public function store(Request $request)
    {
        $data = $request->all();
        
        // PERBAIKAN: Ambil user_id dari request React (jika Admin yang input), 
        // atau otomatis pakai ID yang login (jika user biasa).
        $data['user_id'] = $request->user_id ?? $request->user()->id; 

        // Proses Upload File Sertifikat
        if ($request->hasFile('sertifikat_file')) {
            // Simpan ke folder storage/app/public/sertifikat
            $path = $request->file('sertifikat_file')->store('sertifikat', 'public');
            $data['sertifikat_path'] = $path;
        }

        $diklat = DiklatPersonel::create($data);
        return response()->json(['message' => 'Data diklat berhasil disimpan', 'data' => $diklat]);
    }

    // Update data (Untuk Edit Realisasi & Ganti Sertifikat)
    public function update(Request $request, $id)
    {
        $diklat = DiklatPersonel::findOrFail($id);
        $data = $request->all();

        // Proses Update File (Hapus file lama, upload yang baru)
        if ($request->hasFile('sertifikat_file')) {
            // Hapus file fisik lama jika sebelumnya sudah ada
            if ($diklat->sertifikat_path) {
                Storage::disk('public')->delete($diklat->sertifikat_path);
            }
            
            // Simpan file fisik baru
            $path = $request->file('sertifikat_file')->store('sertifikat', 'public');
            $data['sertifikat_path'] = $path;
        }

        $diklat->update($data);
        return response()->json(['message' => 'Data diklat berhasil diupdate', 'data' => $diklat]);
    }

    // Hapus data
    public function destroy($id)
    {
        $diklat = DiklatPersonel::findOrFail($id);
        
        // Hapus file fisiknya juga jika ada
        if ($diklat->sertifikat_path) {
            Storage::disk('public')->delete($diklat->sertifikat_path);
        }
        
        $diklat->delete();
        return response()->json(['message' => 'Data diklat berhasil dihapus']);
    }
}