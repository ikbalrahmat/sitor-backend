<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Audit;
use App\Models\DiklatPersonel; // Pastikan nama Model sesuai dengan tabel sertifikat lu
use Carbon\Carbon;

class MatriksRisikoController extends Controller
{
    public function index(Request $request)
    {
        $tahun = $request->query('tahun', date('Y')); // Default ambil tahun saat ini (2026)

        // 1. AMBIL KEBUTUHAN: Dari daftar Rencana Audit tahun ini
        $audits = Audit::with('requirements')->where('tahun', $tahun)->get();

        $kebutuhan = [];
        foreach ($audits as $audit) {
            foreach ($audit->requirements as $req) {
                $jenis = strtoupper($req->jenis_sertifikat); // Format ke huruf besar agar seragam
                if (!isset($kebutuhan[$jenis])) {
                    $kebutuhan[$jenis] = 0;
                }
                $kebutuhan[$jenis] += $req->jumlah_kebutuhan;
            }
        }

        // 2. AMBIL KETERSEDIAAN: Dari tabel riwayat kompetensi (Sertifikat yang Terealisasi)
        // Kita hanya hitung sertifikat yang benar-benar SUDAH DIIKUTI
        $diklats = DiklatPersonel::where('jenis', 'Sertifikasi')
                    ->whereNotNull('realisasi_diklat')
                    ->where('realisasi_diklat', '!=', '-')
                    ->get();

        $ketersediaan = [];
        $today = Carbon::today();

        foreach ($diklats as $d) {
            // Hitung status expired
            $status = 'Aktif';
            if ($d->tanggal_expired) {
                $expDate = Carbon::parse($d->tanggal_expired)->startOfDay();
                $diffDays = $today->diffInDays($expDate, false);
                if ($diffDays < 0) {
                    $status = 'Expired'; // Kalau expired, tidak dihitung!
                }
            }

            // Jika sertifikat masih aktif, tambahkan ke ketersediaan
            if ($status === 'Aktif') {
                $namaSertifikat = strtoupper($d->realisasi_diklat);
                if (!isset($ketersediaan[$namaSertifikat])) {
                    $ketersediaan[$namaSertifikat] = 0;
                }
                $ketersediaan[$namaSertifikat] += 1;
            }
        }

        // 3. BANGUN MATRIKS PERBANDINGAN (RISK LEVEL)
        $matriks = [];
        foreach ($kebutuhan as $jenis => $butuh) {
            $tersedia = $ketersediaan[$jenis] ?? 0;
            $gap = $tersedia - $butuh; // Kalau minus, berarti kita kekurangan orang
            
            if ($tersedia >= $butuh) {
                $level = 'LOW'; // Aman
            } elseif ($tersedia > 0) {
                $level = 'MEDIUM'; // Ada, tapi kurang
            } else {
                $level = 'HIGH'; // Kritis, tidak ada sama sekali yang punya
            }

            $matriks[] = [
                'sertifikat' => $jenis,
                'kebutuhan' => $butuh,
                'tersedia' => $tersedia,
                'gap' => $gap,
                'risk_level' => $level
            ];
        }

        return response()->json([
            'tahun' => $tahun,
            'matriks' => $matriks,
            'total_audit' => $audits->count(),
        ]);
    }
}