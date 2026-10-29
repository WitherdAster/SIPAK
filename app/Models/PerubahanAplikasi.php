<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerubahanAplikasi extends Model
{
    use HasFactory;

    public const STATUS = [
        'menunggu' => 'Menunggu Persetujuan',
        'disetujui' => 'Disetujui',
        'ditolak' => 'Ditolak',
    ];

    public const FIELD_LABEL = [
        'nama' => 'Nama Aplikasi',
        'singkatan' => 'Singkatan',
        'deskripsi' => 'Deskripsi',
        'tahun' => 'Tahun Pembuatan',
        'platform' => 'Platform',
        'url' => 'URL / Domain',
        'status' => 'Status Operasional',
        'vendor' => 'Pengembang / Vendor',
        'pic_nama' => 'Nama PIC',
        'pic_kontak' => 'Kontak PIC',
        'jumlah_pengguna' => 'Jumlah Pengguna',
        'server_hosting' => 'Server / Hosting',
        'teknologi' => 'Teknologi',
        'db_teknologi' => 'Database',
        'punya_api' => 'Menyediakan API',
        'sistem_terintegrasi' => 'Sistem Terintegrasi',
        'splp' => 'Terhubung SPLP / SPLPD',
        'audit_keamanan' => 'Sudah Audit Keamanan',
        'backup_data' => 'Backup Data',
        'disaster_recovery' => 'Disaster Recovery',
    ];

    protected $table = 'perubahan_aplikasi';

    protected $fillable = [
        'aplikasi_id',
        'user_id',
        'data',
        'status',
        'catatan_admin',
        'diputuskan_oleh',
        'diputuskan_at',
    ];

    protected $casts = [
        'data' => 'array',
        'diputuskan_at' => 'datetime',
    ];

    public function aplikasi(): BelongsTo
    {
        return $this->belongsTo(Aplikasi::class);
    }

    public function pengaju(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function pemutus(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diputuskan_oleh');
    }

    public function menunggu(): bool
    {
        return $this->status === 'menunggu';
    }

    public function labelStatus(): string
    {
        return self::STATUS[$this->status] ?? ucfirst($this->status);
    }

    public function warnaStatus(): string
    {
        return match ($this->status) {
            'disetujui' => 'bg-emerald-100 text-emerald-700',
            'ditolak' => 'bg-red-100 text-red-700',
            default => 'bg-sky-100 text-sky-700',
        };
    }

    public function atribut(): array
    {
        return $this->data['atribut'] ?? [];
    }

    public function dokumenBaru(): array
    {
        return $this->data['dokumen'] ?? [];
    }

    public function dokumenDihapus(): array
    {
        return $this->data['hapus_dokumen'] ?? [];
    }
}
