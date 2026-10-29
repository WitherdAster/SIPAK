<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DokumenAplikasi extends Model
{
    use HasFactory;

    protected $table = 'dokumen_aplikasi';

    public const JENIS = [
        'sop' => 'SOP',
        'manual' => 'Manual Pengguna',
        'teknis' => 'Dokumentasi Teknis',
        'pks' => 'PKS',
        'audit' => 'Laporan Audit',
        'pendukung' => 'Dokumen Pendukung',
    ];

    protected $fillable = ['aplikasi_id', 'jenis', 'nama_asli', 'path'];

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class);
    }

    public function labelJenis(): string
    {
        return self::JENIS[$this->jenis] ?? ucfirst($this->jenis);
    }
}
