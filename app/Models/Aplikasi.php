<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aplikasi extends Model
{
    use HasFactory;

    protected $table = 'aplikasi';

    protected $fillable = [
        'perangkat_daerah_id',
        'created_by',
        'nama',
        'singkatan',
        'deskripsi',
        'tahun',
        'platform',
        'url',
        'status',
        'verifikasi',
        'catatan_verifikasi',
        'diverifikasi_at',
        'vendor',
        'pic_nama',
        'pic_kontak',
        'jumlah_pengguna',
        'server_hosting',
        'teknologi',
        'db_teknologi',
        'punya_api',
        'sistem_terintegrasi',
        'splp',
        'audit_keamanan',
        'backup_data',
        'disaster_recovery',
    ];

    protected $casts = [
        'punya_api' => 'boolean',
        'splp' => 'boolean',
        'audit_keamanan' => 'boolean',
        'backup_data' => 'boolean',
        'disaster_recovery' => 'boolean',
        'diverifikasi_at' => 'datetime',
    ];

    public function perangkatDaerah(): BelongsTo
    {
        return $this->belongsTo(PerangkatDaerah::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');

    }

    public function dokumens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DokumenAplikasi::class);
    }

    public function perubahanAplikasi(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PerubahanAplikasi::class);
    }

    public function perubahanMenunggu(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PerubahanAplikasi::class)->where('status', 'menunggu')->latestOfMany();
    }

    public function perubahanTerakhir(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PerubahanAplikasi::class)->latestOfMany();
    }

    /**
     * Hanya yang diterima admin yang boleh tayang publik.
     */
    public function scopeTerverifikasi($query)
    {
        return $query->where('verifikasi', 'terverifikasi');
    }

    /**
     * Label status pengajuan untuk dipantau instansi.
     */
    public function labelVerifikasi(): string
    {
        return match ($this->verifikasi) {
            'terverifikasi' => 'Diterima',
            'dikembalikan' => 'Perlu Perbaikan',
            default => 'Menunggu',
        };
    }

    public function warnaVerifikasi(): string
    {
        return match ($this->verifikasi) {
            'terverifikasi' => 'bg-emerald-100 text-emerald-700',
            'dikembalikan' => 'bg-orange-100 text-orange-700',
            default => 'bg-amber-100 text-amber-700',
        };
    }

    /**
     * Cek apakah pengajuan sedang menunggu perbaikan dari instansi.
     */
    public function butuhPerbaikan(): bool
    {
        return $this->verifikasi === 'dikembalikan';
    }

    /**
     * Warna badge status operasional (aktif/nonaktif/pengembangan).
     */
    public function warnaStatus(): string
    {
        return match ($this->status) {
            'aktif' => 'bg-blue-100 text-blue-700',
            'nonaktif' => 'bg-slate-200 text-slate-600',
            'pengembangan' => 'bg-purple-100 text-purple-700',
            default => 'bg-slate-100 text-slate-500',
        };
    }
}
