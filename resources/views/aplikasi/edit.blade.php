@extends('layouts.app')

@section('title', 'Edit Data Aplikasi - SIPAK')

@php
  $isAdmin = auth()->user()->role === 'admin';
  $revisi = $aplikasi->butuhPerbaikan();
  $berjalan = $aplikasi->verifikasi === 'terverifikasi';
  $pending = $pending ?? null;
  $terkunci = $pending && ! $isAdmin;
  $submitLabel = $isAdmin
      ? 'Simpan Perubahan'
      : ($revisi ? 'Kirim Ulang Perbaikan' : ($berjalan ? 'Ajukan Perubahan' : 'Simpan & Kirim Verifikasi'));
  $submitIcon = $isAdmin ? 'fa-floppy-disk' : ($revisi ? 'fa-rotate-right' : 'fa-paper-plane');
@endphp

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
  <div>
    <h2 class="text-xl font-extrabold">{{ $revisi && ! $isAdmin ? 'Perbaikan Pengajuan Aplikasi' : 'Edit Data Aplikasi' }}</h2>
    <p class="text-xs text-slate-500">
      @if ($revisi && ! $isAdmin)
      Perbaiki data <span class="font-semibold text-blue-700">{{ $aplikasi->nama }}</span> sesuai catatan Diskominfo, lalu kirim ulang untuk diverifikasi.
      @elseif ($isAdmin)
      Mengubah data <span class="font-semibold text-blue-700">{{ $aplikasi->nama }}</span> sebagai admin. Status verifikasi tidak berubah.
      @elseif ($berjalan)
      Perubahan data <span class="font-semibold text-blue-700">{{ $aplikasi->nama }}</span> akan direview Diskominfo sebelum diterapkan.
      @else
      Ubah data <span class="font-semibold text-blue-700">{{ $aplikasi->nama }}</span>. Perubahan akan diverifikasi Diskominfo.
      @endif
    </p>
  </div>

  @if ($revisi && ! $isAdmin)
  <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
    <div class="flex items-start gap-3">
      <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-screwdriver-wrench"></i>
      </div>
      <div>
        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Catatan Perbaikan dari Diskominfo</p>
        <p class="text-sm text-amber-900 mt-1 whitespace-pre-line">{{ $aplikasi->catatan_verifikasi ?: 'Tidak ada catatan.' }}</p>
        <p class="text-[11px] text-amber-700/80 mt-2"><i class="fa-regular fa-clock mr-1"></i>Dikembalikan {{ $aplikasi->diverifikasi_at?->format('d M Y H:i') ?? '-' }}</p>
      </div>
    </div>
  </div>
  @elseif (! $isAdmin && $berjalan)
  <div class="rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-800">
    <i class="fa-solid fa-circle-info mr-1.5"></i>Aplikasi ini sudah berjalan. Perubahan akan masuk <span class="font-semibold">antrean persetujuan admin</span>. Data lama tetap tayang dan tidak berubah sampai pengajuan disetujui.
  </div>
  @elseif ($isAdmin)
  <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600">
    <i class="fa-solid fa-user-shield mr-1.5 text-blue-700"></i>Sebagai admin Diskominfo, perubahan langsung tersimpan tanpa mengubah status verifikasi aplikasi.
    @if ($pending)
    <span class="block mt-1.5 text-amber-700"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Ada pengajuan perubahan yang menunggu. Menyimpan form ini akan <span class="font-semibold">membatalkan pengajuan tersebut</span>.</span>
    @endif
  </div>
  @endif

  @if ($terkunci)
  <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
    <div class="flex items-start gap-3">
      <div class="w-9 h-9 rounded-xl bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-hourglass-half"></i>
      </div>
      <div class="flex-1">
        <p class="text-xs font-bold uppercase tracking-wider text-sky-700">Pengajuan Perubahan Sedang Direview</p>
        <p class="text-sm text-sky-900 mt-1">Perubahan data <span class="font-semibold">{{ $aplikasi->nama }}</span> sudah dikirim dan sedang menunggu persetujuan Diskominfo. Data lama tetap tayang sampai disetujui.</p>
        <p class="text-[11px] text-sky-700/80 mt-2"><i class="fa-regular fa-clock mr-1"></i>Diajukan {{ $pending->created_at?->format('d M Y H:i') ?? '-' }} oleh {{ $pending->pengaju->name ?? 'instansi' }}</p>
        <div class="mt-3 flex flex-wrap gap-2">
          <a href="{{ route('aplikasi.milikku') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white border border-sky-200 px-4 py-2 text-xs font-semibold text-sky-700 hover:bg-sky-100 transition"><i class="fa-solid fa-arrow-left"></i>Kembali</a>
          <form action="{{ route('aplikasi.perubahan.batal', $aplikasi) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan perubahan ini? Data yang diajukan akan dibuang.')">
            @csrf
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-red-600 px-4 py-2 text-xs font-semibold text-white hover:bg-red-700 transition"><i class="fa-solid fa-xmark"></i>Batalkan Pengajuan</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  @else
  @include('aplikasi._form', [
    'aplikasi' => $aplikasi,
    'action' => route('aplikasi.update', $aplikasi),
    'method' => 'PUT',
    'submitLabel' => $submitLabel,
    'submitIcon' => $submitIcon,
  ])
  @endif
</div>
@endsection
