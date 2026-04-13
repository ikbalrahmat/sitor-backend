<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil 100 log aktivitas terbaru
        $logs = ActivityLog::with('user:id,nama') // Ambil nama user jika ada
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        return response()->json($logs);
    }
}