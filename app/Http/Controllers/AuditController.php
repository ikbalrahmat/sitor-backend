<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\AuditTeam;
use App\Models\User;
use App\Models\ActivityLog;

class AuditController extends Controller
{
    // 1. Ambil Semua Audit + Syarat + Anggota Tim
    public function index()
    {
        $audits = Audit::with(['requirements', 'teams.user'])->orderBy('created_at', 'desc')->get();
        return response()->json($audits);
    }

    // 2. Buat Proyek Audit Baru
    public function store(Request $request)
    {
        $request->validate([
            'nama_audit' => 'required|string',
            'tahun' => 'required|integer',
            'requirements' => 'array' // Syarat sertifikat
        ]);

        $audit = Audit::create($request->only('nama_audit', 'tahun', 'tanggal_mulai', 'tanggal_selesai', 'status'));

        // Simpan Syarat (Requirements)
        if ($request->has('requirements')) {
            foreach ($request->requirements as $req) {
                $audit->requirements()->create([
                    'jenis_sertifikat' => $req['jenis_sertifikat'],
                    'jumlah_kebutuhan' => $req['jumlah_kebutuhan']
                ]);
            }
        }

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'AUDIT_CREATED',
            'description' => "Membuat Proyek Audit Baru: {$audit->nama_audit}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Audit berhasil dibuat', 'audit' => $audit->load('requirements')]);
    }

    // 3. Ambil Daftar Auditor untuk "Smart Filter"
    public function getAuditors()
    {
        // Ambil User yang aktif beserta riwayat sertifikasinya
        $users = User::whereIn('role', ['User', 'Manajemen'])
            ->where('status_keaktifan', true)
            ->with(['diklatPersonels' => function($query) {
                $query->where('jenis', 'Sertifikasi')
                      ->whereNotNull('realisasi_diklat')
                      ->where('realisasi_diklat', '!=', '-');
            }])
            ->get();
            
        return response()->json($users);
    }

    // 4. Masukkan Auditor ke dalam Tim
    public function storeTeam(Request $request, $auditId)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'peran' => 'required|string'
        ]);

        if (AuditTeam::where('audit_id', $auditId)->where('user_id', $request->user_id)->exists()) {
            return response()->json(['message' => 'Auditor sudah ada di tim ini'], 400);
        }

        $team = AuditTeam::create([
            'audit_id' => $auditId,
            'user_id' => $request->user_id,
            'peran' => $request->peran
        ]);

        $audit = Audit::find($auditId);
        $userAssigned = User::find($request->user_id);

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'AUDIT_TEAM_ASSIGNED',
            'description' => "Menugaskan {$userAssigned->nama} ({$request->peran}) ke audit {$audit->nama_audit}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Auditor berhasil ditugaskan', 'team' => $team->load('user')]);
    }

    // 5. Hapus Auditor dari Tim
    public function destroyTeam(Request $request, $auditId, $teamId)
    {
        $team = AuditTeam::where('audit_id', $auditId)->where('id', $teamId)->firstOrFail();
        $userRemoved = User::find($team->user_id);
        $audit = Audit::find($auditId);
        
        $team->delete();

        ActivityLog::create([
            'user_id' => $request->user()->id,
            'email' => $request->user()->email,
            'event_type' => 'AUDIT_TEAM_REMOVED',
            'description' => "Mencabut penugasan {$userRemoved->nama} dari audit {$audit->nama_audit}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return response()->json(['message' => 'Auditor berhasil dihapus dari tim']);
    }
}