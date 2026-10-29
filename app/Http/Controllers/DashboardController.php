<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\PerangkatDaerah;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Dashboard user SKPD: statistik umum, diagram instansi teratas,
     * dan marquee aplikasi dari instansi lain.
     */
    public function __invoke(): View
    {
        $user = auth()->user();
        $skpdId = $user->perangkat_daerah_id;

        $aplikasiTayang = Aplikasi::terverifikasi()->count();
        $totalAplikasi = Aplikasi::count();
        $totalSkpd = PerangkatDaerah::count();
        $skpdBerakun = User::where('role', 'operator')->whereNotNull('perangkat_daerah_id')->count();
        $aplikasiku = $skpdId ? Aplikasi::where('perangkat_daerah_id', $skpdId)->count() : 0;

        // 5 instansi dengan aplikasi diterima terbanyak (untuk diagram bundar)
        $topSkpd = PerangkatDaerah::withCount(['aplikasi' => fn ($q) => $q->terverifikasi()])
            ->orderByDesc('aplikasi_count')
            ->take(5)
            ->get();

        // Aplikasi diterima dari instansi lain (marquee), terbaru dulu
        $aplikasiLain = Aplikasi::with('perangkatDaerah')
            ->terverifikasi()
            ->when($skpdId, fn ($q) => $q->where('perangkat_daerah_id', '!=', $skpdId))
            ->latest()
            ->take(12)
            ->get();

        return view('dashboard', compact(
            'aplikasiTayang',
            'totalAplikasi',
            'totalSkpd',
            'skpdBerakun',
            'aplikasiku',
            'topSkpd',
            'aplikasiLain',
        ));
    }
}
