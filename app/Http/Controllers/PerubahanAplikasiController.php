<?php

namespace App\Http\Controllers;

use App\Models\DokumenAplikasi;
use App\Models\PerubahanAplikasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PerubahanAplikasiController extends Controller
{
    /**
     * Halaman perbandingan data lama vs data yang diajukan.
     */
    public function show(PerubahanAplikasi $perubahan): View
    {
        abort_unless($perubahan->menunggu(), 404);

        $perubahan->load(['aplikasi.perangkatDaerah', 'aplikasi.dokumens', 'pengaju']);

        $perbandingan = $this->bandingkan($perubahan);

        return view('verifikasi.perubahan', compact('perubahan', 'perbandingan'));
    }

    /**
     * Setujui perubahan → data & dokumen baru diterapkan ke aplikasi tayang.
     */
    public function setujui(Request $request, PerubahanAplikasi $perubahan): RedirectResponse
    {
        abort_unless($perubahan->menunggu(), 403, 'Pengajuan perubahan ini sudah diputuskan.');

        $validated = $request->validate([
            'catatan_admin' => ['nullable', 'string', 'max:2000'],
        ]);

        DB::transaction(function () use ($perubahan, $validated) {
            $aplikasi = $perubahan->aplikasi;

            $aplikasi->update($perubahan->atribut());

            foreach ($perubahan->dokumenDihapus() as $jenis) {
                if ($lama = $aplikasi->dokumens()->where('jenis', $jenis)->first()) {
                    Storage::disk('public')->delete($lama->path);
                    $lama->delete();
                }
            }

            foreach ($perubahan->dokumenBaru() as $jenis => $berkas) {
                if ($lama = $aplikasi->dokumens()->where('jenis', $jenis)->first()) {
                    Storage::disk('public')->delete($lama->path);
                    $lama->delete();
                }

                $aplikasi->dokumens()->create([
                    'jenis' => $jenis,
                    'nama_asli' => $berkas['nama_asli'],
                    'path' => $berkas['path'],
                ]);
            }

            $perubahan->update([
                'status' => 'disetujui',
                'catatan_admin' => $validated['catatan_admin'] ?? null,
                'diputuskan_oleh' => auth()->id(),
                'diputuskan_at' => now(),
            ]);
        });

        return redirect()->route('verifikasi.index')
            ->with('success', "Perubahan data {$perubahan->aplikasi->nama} disetujui dan sudah diterapkan.");
    }

    /**
     * Tolak perubahan → data lama tetap tayang, dokumen sementara dibuang.
     */
    public function tolak(Request $request, PerubahanAplikasi $perubahan): RedirectResponse
    {
        abort_unless($perubahan->menunggu(), 403, 'Pengajuan perubahan ini sudah diputuskan.');

        $validated = $request->validate([
            'catatan_admin' => ['required', 'string', 'max:2000'],
        ], [
            'catatan_admin.required' => 'Catatan wajib diisi agar instansi tahu alasan perubahan ditolak.',
        ]);

        foreach ($perubahan->dokumenBaru() as $berkas) {
            if (! empty($berkas['path'])) {
                Storage::disk('public')->delete($berkas['path']);
            }
        }

        $perubahan->update([
            'status' => 'ditolak',
            'catatan_admin' => $validated['catatan_admin'],
            'diputuskan_oleh' => auth()->id(),
            'diputuskan_at' => now(),
        ]);

        return redirect()->route('verifikasi.index')
            ->with('success', "Perubahan data {$perubahan->aplikasi->nama} ditolak. Data lama tetap tayang dan catatan sudah dikirim ke instansi.");
    }

    /**
     * Buka dokumen sementara pengajuan perubahan langsung di browser.
     */
    public function lihatDokumen(PerubahanAplikasi $perubahan, string $jenis): StreamedResponse
    {
        return $this->kirimDokumen($perubahan, $jenis, inline: true);
    }

    /**
     * Unduh dokumen sementara milik pengajuan perubahan (khusus admin).
     */
    public function unduhDokumen(PerubahanAplikasi $perubahan, string $jenis): StreamedResponse
    {
        return $this->kirimDokumen($perubahan, $jenis, inline: false);
    }

    private function kirimDokumen(PerubahanAplikasi $perubahan, string $jenis, bool $inline): StreamedResponse
    {
        abort_unless($perubahan->menunggu(), 404);
        abort_unless(array_key_exists($jenis, DokumenAplikasi::JENIS), 404);

        $berkas = $perubahan->dokumenBaru()[$jenis] ?? null;

        abort_if(! $berkas, 404);
        abort_if(! Storage::disk('public')->exists($berkas['path']), 404);

        $headers = [
            'Content-Type' => Storage::disk('public')->mimeType($berkas['path']) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return $inline
            ? Storage::disk('public')->response($berkas['path'], $berkas['nama_asli'], $headers)
            : Storage::disk('public')->download($berkas['path'], $berkas['nama_asli'], $headers);
    }

    /**
     * Susun baris perbandingan hanya untuk field yang berubah.
     */
    private function bandingkan(PerubahanAplikasi $perubahan): array
    {
        $aplikasi = $perubahan->aplikasi;
        $atribut = $perubahan->atribut();

        $baris = [];

        foreach (PerubahanAplikasi::FIELD_LABEL as $key => $label) {
            $lama = $this->tampilkan($key, $aplikasi->{$key});
            $baru = array_key_exists($key, $atribut)
                ? $this->tampilkan($key, $atribut[$key])
                : $lama;

            if ($lama === $baru) {
                continue;
            }

            $baris[] = compact('label', 'lama', 'baru');
        }

        return $baris;
    }

    private function tampilkan(string $key, mixed $nilai): string
    {
        if (in_array($key, ['punya_api', 'splp', 'audit_keamanan', 'backup_data', 'disaster_recovery'], true)) {
            return $nilai ? 'Ya' : 'Tidak';
        }

        if (in_array($key, ['platform', 'status'], true)) {
            return $nilai ? ucfirst($nilai) : '—';
        }

        if ($nilai === null || $nilai === '') {
            return '—';
        }

        if ($key === 'jumlah_pengguna') {
            return number_format((int) $nilai, 0, ',', '.');
        }

        return (string) $nilai;
    }
}
