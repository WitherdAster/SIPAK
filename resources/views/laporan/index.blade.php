@extends('layouts.app')

@section('title', 'Laporan - SIPAK')

@section('content')
<div class="space-y-5">
  <div>
    <h2 class="text-xl font-extrabold">{{ $judul ?? 'Laporan Aplikasi' }}</h2>
    <p class="text-xs text-slate-500">{{ $subjudul ?? 'Unduh rekap keseluruhan aplikasi (PDF). Kosongkan filter untuk mengunduh semua data.' }}</p>
  </div>

  <form action="{{ route($ruteFilter ?? 'laporan.index') }}" method="GET" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="f-status">Status</label>
        <select id="f-status" name="status" class="w-full rounded-xl border-slate-300 text-sm">
          <option value="">Semua Status</option>
          @foreach ($statusList as $val => $label)
          <option value="{{ $val }}" {{ ($filter['status'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="f-tahun">Tahun</label>
        <select id="f-tahun" name="tahun" class="w-full rounded-xl border-slate-300 text-sm">
          <option value="">Semua Tahun</option>
          @foreach ($tahunList as $t)
          <option value="{{ $t }}" {{ (string) ($filter['tahun'] ?? '') === (string) $t ? 'selected' : '' }}>{{ $t }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="f-platform">Platform</label>
        <select id="f-platform" name="platform" class="w-full rounded-xl border-slate-300 text-sm">
          <option value="">Semua Platform</option>
          @foreach ($platformList as $val => $label)
          <option value="{{ $val }}" {{ ($filter['platform'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="flex flex-wrap items-center gap-2 mt-5">
      <button class="text-xs font-semibold px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 transition" type="submit"><i class="fa-solid fa-filter mr-1.5"></i>Terapkan Filter</button>
      <a href="{{ route($ruteFilter ?? 'laporan.index') }}" class="text-xs font-semibold px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 transition">Reset</a>
      <a href="{{ route($ruteUnduh ?? 'laporan.unduh', array_filter($filter)) }}" class="text-xs font-semibold px-5 py-2.5 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition ml-auto"><i class="fa-solid fa-download mr-1.5"></i>Unduh PDF ({{ $jumlah }} data)</a>
    </div>
  </form>
</div>
@endsection
