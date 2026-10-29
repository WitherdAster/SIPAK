<?php

namespace App\Http\Controllers;

use App\Models\DokumenAplikasi;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DokumenController extends Controller
{
    /**
     * Buka dokumen langsung di browser (inline). PDF/gambar akan tampil
     * tanpa perlu diunduh. Aturan akses sama seperti unduh.
     */
    public function lihat(DokumenAplikasi $dokumen): StreamedResponse
    {
        return $this->kirim($dokumen, inline: true);
    }

    /**
     * Unduh dokumen. Aturan sama seperti detail: yang tayang publik
     * boleh diunduh semua user login; sisanya hanya pemilik dan admin.
     */
    public function unduh(DokumenAplikasi $dokumen): StreamedResponse
    {
        return $this->kirim($dokumen, inline: false);
    }

    private function kirim(DokumenAplikasi $dokumen, bool $inline): StreamedResponse
    {
        $dokumen->loadMissing('aplikasi');

        $user = auth()->user();
        $aplikasi = $dokumen->aplikasi;

        abort_if(
            $aplikasi->verifikasi !== 'terverifikasi'
                && $user->role !== 'admin'
                && $aplikasi->perangkat_daerah_id !== $user->perangkat_daerah_id,
            403
        );

        abort_if(! Storage::disk('public')->exists($dokumen->path), 404);

        $headers = [
            'Content-Type' => Storage::disk('public')->mimeType($dokumen->path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return $inline
            ? Storage::disk('public')->response($dokumen->path, $dokumen->nama_asli, $headers)
            : Storage::disk('public')->download($dokumen->path, $dokumen->nama_asli, $headers);
    }
}
