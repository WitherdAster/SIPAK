<?php

namespace App\Http\Controllers;

use App\Models\Aplikasi;
use App\Models\PerubahanAplikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerifikasiController extends Controller
{
    /**
     * Daftar pengajuan menunggu verifikasi + riwayat.
     */
    public function index(): View
    {
        $menunggu = Aplikasi::with(['perangkatDaerah', 'creator'])
            ->where('verifikasi', 'diajukan')
            ->latest()
            ->paginate(10, ['*'], 'menunggu');

        $riwayat = Aplikasi::with('perangkatDaerah')
            ->whereIn('verifikasi', ['terverifikasi', 'dikembalikan'])
            ->orderByRaw('diverifikasi_at IS NULL, diverifikasi_at DESC')
            ->take(10)
            ->get();

        $perubahan = PerubahanAplikasi::with(['aplikasi.perangkatDaerah', 'pengaju'])
            ->where('status', 'menunggu')
            ->latest()
            ->paginate(10, ['*'], 'perubahan');

        $riwayatPerubahan = PerubahanAplikasi::with(['aplikasi.perangkatDaerah', 'pengaju'])
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->orderByRaw('diputuskan_at IS NULL, diputuskan_at DESC')
            ->take(10)
            ->get();

        return view('verifikasi.index', compact('menunggu', 'riwayat', 'perubahan', 'riwayatPerubahan'));
    }

    /**
     * Terima pengajuan → tayang di Semua Aplikasi.
     */
    public function terima(Aplikasi $aplikasi): RedirectResponse
    {
        abort_unless($aplikasi->verifikasi === 'diajukan', 403, 'Hanya pengajuan yang menunggu verifikasi yang dapat diterima.');

        $aplikasi->update([
            'verifikasi' => 'terverifikasi',
            'catatan_verifikasi' => null,
            'diverifikasi_at' => now(),
        ]);

        return back()->with('success', "Pengajuan {$aplikasi->nama} diterima dan sudah tayang.");
    }

    /**
     * Minta perbaikan → wajib beri catatan + tidak tayang.
     * Instansi dapat memperbaiki data lalu mengirim ulang pengajuan.
     */
    public function perbaikan(Request $request, Aplikasi $aplikasi): RedirectResponse
    {
        abort_unless($aplikasi->verifikasi === 'diajukan', 403, 'Hanya pengajuan yang menunggu verifikasi yang dapat diminta perbaikan.');

        $validated = $request->validate([
            'catatan_verifikasi' => ['required', 'string', 'max:2000'],
        ], [
            'catatan_verifikasi.required' => 'Catatan perbaikan wajib diisi agar instansi tahu apa yang harus diperbaiki.',
        ]);

        $aplikasi->update([
            'verifikasi' => 'dikembalikan',
            'catatan_verifikasi' => $validated['catatan_verifikasi'],
            'diverifikasi_at' => now(),
        ]);

        return back()->with('success', "Pengajuan {$aplikasi->nama} dikembalikan untuk perbaikan. Catatan sudah dikirim ke instansi.");
    }
}
