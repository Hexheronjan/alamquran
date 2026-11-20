<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminMonitoringWaliController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['santri' => function ($q) {
            $q->select('id', 'user_id', 'nama_lengkap', 'kelas', 'nis', 'status');
        }]);

        // Filter status aktivitas
        $filter = $request->get('filter', 'all');

        if ($filter === 'online') {
            $query->where('last_activity_at', '>=', Carbon::now()->subMinutes(10));
        } elseif ($filter === 'hari_ini') {
            $query->whereDate('last_login_at', Carbon::today());
        } elseif ($filter === 'paling_aktif') {
            $query->where('login_count', '>', 0)->orderByDesc('login_count');
        } elseif ($filter === 'belum_pernah') {
            $query->where(function ($q) {
                $q->whereNull('last_login_at')->orWhere('login_count', 0);
            });
        }

        // Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('no_wa', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('santri', function ($sq) use ($search) {
                      $sq->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nis', 'like', "%{$search}%");
                  });
            });
        }

        // Default sort: yang sedang aktif atau terakhir login di atas
        if ($filter !== 'paling_aktif') {
            $query->orderByRaw('CASE WHEN last_activity_at >= ? THEN 1 ELSE 2 END', [Carbon::now()->subMinutes(10)])
                  ->orderByDesc('last_activity_at')
                  ->orderByDesc('last_login_at');
        }

        $walis = $query->paginate(20)->withQueryString();

        // Metrik Ringkasan
        $tenMinutesAgo = Carbon::now()->subMinutes(10);
        $totalWali = User::count();
        $onlineNowCount = User::where('last_activity_at', '>=', $tenMinutesAgo)->count();
        $loginHariIniCount = User::whereDate('last_login_at', Carbon::today())->count();
        $pernahLoginCount = User::where('login_count', '>', 0)->count();
        $belumPernahCount = User::where(function ($q) {
            $q->whereNull('last_login_at')->orWhere('login_count', 0);
        })->count();

        return view('admin.monitoring_wali.index', compact(
            'walis',
            'filter',
            'totalWali',
            'onlineNowCount',
            'loginHariIniCount',
            'pernahLoginCount',
            'belumPernahCount'
        ));
    }
}
