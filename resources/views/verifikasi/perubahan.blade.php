@extends('layouts.app')

@section('title', 'Review Perubahan Data - SIPAK')

@section('content')
<div class="max-w-4xl mx-auto space-y-5">
  <div class="flex flex-wrap items-start gap-3 justify-between">
    <div>
      <a href="{{ route('verifikasi.index') }}" class="text-[11px] font-semibold text-slate-500 hover:text-blue-700"><i class="fa-solid fa-arrow-left mr-1"></i>Kembali ke Verifikasi</a>
      <h2 class="text-xl font-extrabold mt-1">Review Perubahan Data</h2>
      <p class="text-xs text-slate-500">Bandingkan data lama dengan yang diajukan sebelum mengambil keputusan.</p>
    </div>
    <span class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-sky-100 text-sky-700"><i class="fa-solid fa-hourglass-half mr-1"></i>{{ $perubahan->labelStatus() }}</span>
  </div>

  @if ($errors->any())
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $errors->first() }}</div>
  @endif

  <!-- Info pengajuan -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <div class="flex flex-wrap items-start gap-3 justify-between">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Aplikasi</p>
        <p class="font-bold text-lg">{{ $perubahan->aplikasi->nama ?? '-' }} @if($perubahan->aplikasi?->singkatan)<span class="text-slate-400 font-medium text-sm">({{ $perubahan->aplikasi->singkatan }})</span>@endif</p>
        <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-building mr-1"></i>{{ $perubahan->aplikasi->perangkatDaerah->nama ?? '-' }}</p>
        <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-user mr-1"></i>Diajukan oleh {{ $perubahan->pengaju->name ?? '-' }} • {{ $perubahan->created_at?->format('d M Y H:i') ?? '-' }}</p>
      </div>
      <div class="rounded-xl bg-amber-50 border border-amber-200 px-3 py-2.5 text-[11px] text-amber-800 max-w-xs">
        <i class="fa-solid fa-circle-info mr-1"></i>Selama menunggu, aplikasi tetap tayang dengan data lama.
      </div>
    </div>
  </div>

  <!-- Perbandingan data -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h3 class="font-bold text-sm mb-1">Perbandingan Data</h3>
    <p class="text-[11px] text-slate-400 mb-4">Hanya field yang berubah yang ditampilkan.</p>

    @if (empty($perbandingan))
    <p class="text-sm text-slate-400 text-center py-6 rounded-xl bg-slate-50">Tidak ada perubahan pada field data. Periksa bagian dokumen di bawah.</p>
    @else
    <div class="overflow-x-auto">
      <table class="w-full text-xs">
        <thead><tr class="text-left text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-100">
          <th class="py-2 pr-3 w-40">Field</th><th class="py-2 pr-3">Data Lama (Tayang)</th><th class="py-2">Data Diajukan</th>
        </tr></thead>
        <tbody>
          @foreach ($perbandingan as $row)
          <tr class="border-b border-slate-50 align-top">
            <td class="py-2.5 pr-3 font-semibold text-slate-600">{{ $row['label'] }}</td>
            <td class="py-2.5 pr-3 text-slate-400 line-through decoration-slate-300">{{ $row['lama'] }}</td>
            <td class="py-2.5 font-medium text-sky-800">{{ $row['baru'] }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>

  <!-- Perubahan dokumen -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h3 class="font-bold text-sm mb-1">Perubahan Dokumen</h3>
    <p class="text-[11px] text-slate-400 mb-4">Dokumen baru belum diterapkan sampai pengajuan disetujui.</p>

    @if (empty($perubahan->dokumenBaru()) && empty($perubahan->dokumenDihapus()))
    <p class="text-sm text-slate-400 text-center py-6 rounded-xl bg-slate-50">Tidak ada perubahan dokumen.</p>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 mb-2"><i class="fa-solid fa-file-arrow-up mr-1"></i>Dokumen Baru / Pengganti</p>
        @forelse ($perubahan->dokumenBaru() as $jenis => $berkas)
        <div class="rounded-xl border border-emerald-200 bg-emerald-50/50 px-3 py-2.5 mb-2">
          <p class="text-[10px] font-bold text-emerald-700">{{ \App\Models\DokumenAplikasi::JENIS[$jenis] ?? ucfirst($jenis) }}</p>
          <div class="flex items-center gap-2">
            <a href="{{ route('verifikasi.perubahan.dokumen.lihat', [$perubahan, $jenis]) }}" target="_blank" rel="noopener" class="text-xs font-medium text-emerald-800 hover:underline break-all"><i class="fa-solid fa-file-lines mr-1"></i>{{ $berkas['nama_asli'] }}</a>
            <a href="{{ route('verifikasi.perubahan.dokumen', [$perubahan, $jenis]) }}" class="text-emerald-700 hover:text-emerald-900 shrink-0" title="Unduh"><i class="fa-solid fa-download text-[10px]"></i></a>
          </div>
        </div>
        @empty
        <p class="text-xs text-slate-400">Tidak ada dokumen baru.</p>
        @endforelse
      </div>
      <div>
        <p class="text-[10px] font-bold uppercase tracking-wider text-red-700 mb-2"><i class="fa-solid fa-file-circle-minus mr-1"></i>Dokumen Dihapus</p>
        @forelse ($perubahan->dokumenDihapus() as $jenis)
        <div class="rounded-xl border border-red-200 bg-red-50/50 px-3 py-2.5 mb-2">
          <p class="text-xs font-semibold text-red-800">{{ \App\Models\DokumenAplikasi::JENIS[$jenis] ?? ucfirst($jenis) }}</p>
        </div>
        @empty
        <p class="text-xs text-slate-400">Tidak ada dokumen yang dihapus.</p>
        @endforelse
      </div>
    </div>
    @endif
  </div>

  <!-- Keputusan -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <h3 class="font-bold text-sm mb-1">Keputusan Admin</h3>
    <p class="text-[11px] text-slate-400 mb-4">Setujui untuk menerapkan data baru ke aplikasi tayang, atau tolak agar data lama tetap berlaku.</p>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
      <form action="{{ route('verifikasi.perubahan.setujui', $perubahan) }}" method="POST" class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-4">
        @csrf
        <p class="text-xs font-bold text-emerald-800 mb-2"><i class="fa-solid fa-circle-check mr-1"></i>Setujui Perubahan</p>
        <label class="block text-[11px] font-semibold text-emerald-800 mb-1.5" for="catatan_setujui">Catatan (opsional)</label>
        <textarea id="catatan_setujui" name="catatan_admin" rows="3" maxlength="2000" class="w-full text-xs rounded-xl border-slate-300 px-3 py-2" placeholder="Catatan untuk instansi..."></textarea>
        <button type="submit" class="mt-3 w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 transition"><i class="fa-solid fa-check mr-1.5"></i>Setujui & Terapkan Data Baru</button>
      </form>

      <form action="{{ route('verifikasi.perubahan.tolak', $perubahan) }}" method="POST" class="rounded-xl border border-red-200 bg-red-50/40 p-4">
        @csrf
        <p class="text-xs font-bold text-red-800 mb-2"><i class="fa-solid fa-circle-xmark mr-1"></i>Tolak Perubahan</p>
        <label class="block text-[11px] font-semibold text-red-800 mb-1.5" for="catatan_tolak">Alasan penolakan *</label>
        <textarea id="catatan_tolak" name="catatan_admin" required rows="3" maxlength="2000" class="w-full text-xs rounded-xl border-slate-300 px-3 py-2" placeholder="Jelaskan data mana yang salah agar instansi bisa memperbaiki..."></textarea>
        <button type="submit" class="mt-3 w-full py-2.5 rounded-xl text-xs font-semibold text-white bg-red-600 hover:bg-red-700 transition"><i class="fa-solid fa-xmark mr-1.5"></i>Tolak & Pertahankan Data Lama</button>
      </form>
    </div>
  </div>
</div>
@endsection
