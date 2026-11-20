<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KantinTransaksi;
use App\Models\KantinTransaksiItem;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KantinLaporanController extends Controller
{
    public function index(Request $request)
    {
        $admin        = auth('admin')->user();
        $allowedTokos = $admin->getAllowedTokos();

        $periode = $request->get('periode', 'harian');

        // Kasir satu toko: kunci ke tokonya, abaikan query ?toko=
        if (count($allowedTokos) === 1) {
            $selectedToko = $allowedTokos[0];
        } else {
            $selectedToko = $request->get('toko', 'all');
            if ($selectedToko !== 'all' && !in_array($selectedToko, $allowedTokos)) {
                $selectedToko = 'all';
            }
        }

        $query = KantinTransaksi::with(['santri', 'kasir', 'items'])
            ->whereIn('toko', $allowedTokos);

        if ($selectedToko !== 'all') {
            $query->where('toko', $selectedToko);
        }

        $now       = Carbon::now();
        $startDate = null;
        $endDate   = null;

        if ($periode === 'harian') {
            $tanggal   = $request->get('tanggal', $now->toDateString());
            $startDate = Carbon::parse($tanggal)->startOfDay();
            $endDate   = Carbon::parse($tanggal)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($periode === 'mingguan') {
            $startDate = $now->copy()->startOfWeek();
            $endDate   = $now->copy()->endOfWeek();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($periode === 'bulanan') {
            $bulan     = $request->get('bulan', $now->month);
            $tahun     = $request->get('tahun', $now->year);
            $startDate = Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth();
            $endDate   = Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($periode === 'custom' && $request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate   = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        $totalOmset     = (clone $query)->sum('total_harga');
        $totalModal     = (clone $query)->sum('total_modal');
        $labaBersih     = (clone $query)->sum('laba_bersih');
        $totalTransaksi = (clone $query)->count();

        $transaksis = $query->latest()->paginate(25)->withQueryString();

        return view('admin.kantin.laporan.index', compact(
            'transaksis',
            'totalOmset',
            'totalModal',
            'labaBersih',
            'totalTransaksi',
            'periode',
            'selectedToko',
            'allowedTokos',
            'startDate',
            'endDate'
        ));
    }
}
