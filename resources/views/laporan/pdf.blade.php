<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Laporan SIPAK</title>
<style>
  @page { margin: 60px 40px 50px 40px; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #1e293b; }
  .kop { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 6px; }
  .kop h1 { font-size: 15px; margin: 0; }
  .kop p { font-size: 10px; margin: 2px 0 0 0; color: #475569; }
  .meta { font-size: 9px; color: #475569; margin-bottom: 12px; }
  .app { border: 1px solid #cbd5e1; border-radius: 6px; margin-bottom: 12px; page-break-inside: avoid; }
  .app-head { background: #eff6ff; padding: 6px 10px; border-bottom: 1px solid #cbd5e1; }
  .app-head .nama { font-size: 12px; font-weight: bold; }
  .app-head .instansi { font-size: 9px; color: #475569; }
  table.detail { width: 100%; border-collapse: collapse; }
  table.detail td { border-bottom: 1px solid #f1f5f9; padding: 4px 10px; vertical-align: top; }
  table.detail td.k { width: 32%; color: #64748b; }
  .footer { position: fixed; bottom: -30px; left: 0; right: 0; text-align: center; font-size: 8px; color: #94a3b8; }
  .footer .pagenum:before { content: counter(page); }
</style>
</head>
<body>
  <div class="footer">SIPAK Kabupaten Barito Kuala — Halaman <span class="pagenum"></span></div>

  <div class="kop">
    <h1>LAPORAN DATA APLIKASI</h1>
    <p>Sistem Informasi Pendataan Aplikasi — Kabupaten Barito Kuala</p>
  </div>
  <p class="meta">
    Diunduh pada: {{ $diunduhPada }} &nbsp;|&nbsp; Jumlah data: {{ $aplikasi->count() }}
    @if ($filter)
      &nbsp;|&nbsp; Filter:
      @foreach ($filter as $k => $v)
        {{ $k }}={{ $k === 'status' ? ($statusList[$v] ?? $v) : ($k === 'platform' ? ($platformList[$v] ?? $v) : $v) }}@if (! $loop->last), @endif
      @endforeach
    @else
      &nbsp;|&nbsp; Filter: semua data
    @endif
  </p>

  @php $ya = fn ($v) => $v ? 'Ya' : 'Tidak'; @endphp

  @forelse ($aplikasi as $i => $a)
  <div class="app">
    <div class="app-head">
      <div class="nama">{{ $i + 1 }}. {{ $a->nama }}{{ $a->singkatan ? ' ('.$a->singkatan.')' : '' }}</div>
      <div class="instansi">{{ $a->perangkatDaerah->nama ?? '-' }} — {{ $a->labelVerifikasi() }} — {{ ucfirst($a->status) }}</div>
    </div>
    <table class="detail">
      <tr><td class="k">Tahun / Platform / URL</td><td>{{ $a->tahun ?? '-' }} / {{ $platformList[$a->platform] ?? $a->platform }} / {{ $a->url ?? '-' }}</td></tr>
      @if ($a->deskripsi)<tr><td class="k">Deskripsi</td><td>{{ $a->deskripsi }}</td></tr>@endif
      <tr><td class="k">Vendor / PIC / Kontak</td><td>{{ $a->vendor ?? '-' }} / {{ $a->pic_nama ?? '-' }} / {{ $a->pic_kontak ?? '-' }}</td></tr>
      <tr><td class="k">Jumlah Pengguna</td><td>{{ $a->jumlah_pengguna ?? '-' }}</td></tr>
      <tr><td class="k">Server / Teknologi / DB</td><td>{{ $a->server_hosting ?? '-' }} / {{ $a->teknologi ?? '-' }} / {{ $a->db_teknologi ?? '-' }}</td></tr>
      <tr><td class="k">API / SPLP</td><td>{{ $ya($a->punya_api) }} / {{ $ya($a->splp) }}</td></tr>
      @if ($a->sistem_terintegrasi)<tr><td class="k">Terintegrasi</td><td>{{ $a->sistem_terintegrasi }}</td></tr>@endif
      <tr><td class="k">Audit / Backup / DR</td><td>{{ $ya($a->audit_keamanan) }} / {{ $ya($a->backup_data) }} / {{ $ya($a->disaster_recovery) }}</td></tr>
      @if ($a->catatan_verifikasi)<tr><td class="k">Catatan</td><td>{{ $a->catatan_verifikasi }}</td></tr>@endif
    </table>
  </div>
  @empty
  <p>Tidak ada data sesuai filter.</p>
  @endforelse
</body>
</html>
