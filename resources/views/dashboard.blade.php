@extends('layouts.app')

@section('title', 'Dashboard - SIPAK')

@section('content')
<div class="space-y-6">
  <!-- Sapaan -->
  <div class="relative rounded-[20px] overflow-hidden bg-gradient-to-r from-blue-900 to-blue-600" style="box-shadow: 0 2px 8px rgba(10, 29, 66, 0.08), 0 8px 24px rgba(10, 29, 66, 0.06);">
  <!-- Pola seperti halaman login (.pola-fade): opacity 0.55, fade kanan → kiri -->
  <div class="pointer-events-none absolute inset-0" style="background-image: url('{{ asset('image/pola.png') }}'); background-size: 150px auto; background-repeat: repeat; background-position: right center; opacity: 0.55; -webkit-mask-image: linear-gradient(to left, rgba(0,0,0,1) 0%, rgba(0,0,0,0.85) 30%, rgba(0,0,0,0.35) 60%, rgba(0,0,0,0) 85%); mask-image: linear-gradient(to left, rgba(0,0,0,1) 0%, rgba(0,0,0,0.85) 30%, rgba(0,0,0,0.35) 60%, rgba(0,0,0,0) 85%);"></div>

  <div class="relative z-10 p-6 flex flex-wrap items-center gap-4 justify-between">
    <div>
      <p class="text-xs font-medium text-blue-200">Selamat datang,</p>
      <h2 class="text-xl sm:text-2xl font-extrabold mt-0.5 text-white">{{ auth()->user()->name }}</h2>
      <p class="text-xs text-blue-200 mt-1">
        <i class="fa-solid fa-building mr-1.5"></i>{{ auth()->user()->perangkatDaerah->nama ?? 'Administrator Diskominfo' }}
      </p>
    </div>
    <a href="{{ route('aplikasi.create') }}" class="text-xs font-semibold px-5 py-2.5 rounded-xl bg-amber-400 text-blue-950 hover:bg-amber-300 transition shadow-sm">
      <i class="fa-solid fa-plus mr-1.5"></i>Ajukan Aplikasi
    </a>
  </div>
</div>

  <!-- Statistik umum -->
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="w-9 h-9 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center mb-3"><i class="fa-solid fa-circle-check"></i></div>
      <p class="text-2xl font-extrabold">{{ $aplikasiTayang }}</p>
      <p class="text-xs text-slate-500 font-medium">Aplikasi Aktif</p>
      <p class="text-[11px] text-slate-400 mt-0.5">Sudah ditayangkan</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="w-9 h-9 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center mb-3"><i class="fa-solid fa-folder-open"></i></div>
      <p class="text-2xl font-extrabold">{{ $aplikasiku }}</p>
      <p class="text-xs text-slate-500 font-medium">Aplikasi Instansiku</p>
      <p class="text-[11px] text-slate-400 mt-0.5">{{ auth()->user()->perangkatDaerah->nama ?? 'Administrator Diskominfo' }}</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center mb-3"><i class="fa-solid fa-building"></i></div>
      <p class="text-2xl font-extrabold">{{ $totalSkpd }}</p>
      <p class="text-xs text-slate-500 font-medium">SKPD Terdaftar</p>
      <p class="text-[11px] text-slate-400 mt-0.5">{{ $skpdBerakun }} berakun • {{ $totalSkpd - $skpdBerakun }} belum</p>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
      <div class="w-9 h-9 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center mb-3"><i class="fa-solid fa-layer-group"></i></div>
      <p class="text-2xl font-extrabold">{{ $totalAplikasi }}</p>
      <p class="text-xs text-slate-500 font-medium">Total Aplikasi</p>
      <p class="text-[11px] text-slate-400 mt-0.5">Tayang, pengembangan &amp; pengajuan</p>
    </div>
  </div>

  <!-- Diagram instansi dengan aplikasi terbanyak -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-bold text-sm">Instansi dengan Aplikasi Terbanyak</h3>
        <p class="text-xs text-slate-500">Top 5 Perangkat Daerah</p>
      </div>
      <span class="text-[11px] font-semibold px-3 py-1 rounded-full bg-blue-50 text-blue-700">Diagram Bundar</span>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
      <div class="relative h-64"><canvas id="skpdPie"></canvas></div>
      <ul class="space-y-2.5">
        @foreach ($topSkpd as $i => $skpd)
        <li class="flex items-center gap-3 text-sm">
          <span class="w-3.5 h-3.5 rounded-full shrink-0" data-pie-color="{{ $i }}"></span>
          <span class="font-medium flex-1">{{ $skpd->nama }}</span>
          <span class="font-extrabold text-blue-800">{{ $skpd->aplikasi_count }}</span>
        </li>
        @endforeach
      </ul>
    </div>
  </div>

  <!-- Aplikasi instansi lain: scroll otomatis -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="font-bold text-sm">Aplikasi Instansi Lain</h3>
        <p class="text-xs text-slate-500">Berjalan otomatis • arahkan kursor untuk jeda</p>
      </div>
      <a href="{{ route('aplikasi.index') }}" class="text-xs font-semibold px-4 py-2 rounded-xl bg-blue-700 text-white hover:bg-blue-800 transition">
        Lihat Semua<i class="fa-solid fa-arrow-right ml-1.5"></i>
      </a>
    </div>
    <div class="overflow-hidden marquee-paused -mx-1 px-1">
      <div class="marquee-track py-1">
        @foreach ($aplikasiLain as $app)
        <div class="w-64 shrink-0 rounded-xl border border-slate-200 p-4 bg-slate-50 hover:bg-white hover:shadow transition cursor-pointer" onclick="openAppDetail({{ $app->id }})">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-1">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaVerifikasi() }}">{{ $app->labelVerifikasi() }}</span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaStatus() }}">{{ ucfirst($app->status) }}</span>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase">{{ $app->platform }}</span>
          </div>
          <p class="font-bold text-sm truncate">{{ $app->nama }}</p>
          <p class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-building mr-1"></i>{{ $app->perangkatDaerah->singkatan ?? '-' }}</p>
          <p class="text-[11px] text-slate-400 mt-1 line-clamp-2">{{ $app->deskripsi }}</p>
        </div>
        @endforeach
        @foreach ($aplikasiLain as $app)
        <div class="w-64 shrink-0 rounded-xl border border-slate-200 p-4 bg-slate-50 hover:bg-white hover:shadow transition cursor-pointer" onclick="openAppDetail({{ $app->id }})" aria-hidden="true">
          <div class="flex items-center justify-between mb-2">
            <div class="flex items-center gap-1">
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaVerifikasi() }}">{{ $app->labelVerifikasi() }}</span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $app->warnaStatus() }}">{{ ucfirst($app->status) }}</span>
            </div>
            <span class="text-[10px] font-semibold text-slate-400 uppercase">{{ $app->platform }}</span>
          </div>
          <p class="font-bold text-sm truncate">{{ $app->nama }}</p>
          <p class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-building mr-1"></i>{{ $app->perangkatDaerah->singkatan ?? '-' }}</p>
          <p class="text-[11px] text-slate-400 mt-1 line-clamp-2">{{ $app->deskripsi }}</p>
        </div>
        @endforeach
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const palette = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444'];
  document.querySelectorAll('[data-pie-color]').forEach(el => {
    el.style.backgroundColor = palette[parseInt(el.dataset.pieColor, 10) % palette.length];
  });
  const canvas = document.getElementById('skpdPie');
  if (canvas && window.Chart) {
    new Chart(canvas, {
      type: 'doughnut',
      data: {
        labels: {!! json_encode($topSkpd->pluck('singkatan')) !!},
        datasets: [{ data: {!! json_encode($topSkpd->pluck('aplikasi_count')) !!}, backgroundColor: palette, borderWidth: 2, borderColor: '#fff' }]
      },
      options: { maintainAspectRatio: false, cutout: '58%', plugins: { legend: { display: false } } }
    });
  }
});
</script>
@endpush
