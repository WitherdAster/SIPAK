@extends('layouts.app')

@section('title', 'Akun Instansi - SIPAK')

@section('content')
<div class="space-y-5">
  <div class="flex flex-wrap items-center gap-3 justify-between">
    <div>
      <h2 class="text-xl font-extrabold">Kelola Akun</h2>
      <p class="text-xs text-slate-500">Kelola akun admin & operator • {{ $totalSkpd }} SKPD terdaftar</p>
    </div>
    <div class="flex gap-2">
      <form action="{{ route('akun.index') }}" method="GET" class="flex gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Cari nama / username..." class="text-sm rounded-xl border-slate-300 px-4 py-2 w-52">
        <button class="text-xs font-semibold px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 transition" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
      </form>
      <a href="{{ route('akun.create') }}" class="text-xs font-semibold px-4 py-2 rounded-xl bg-blue-700 text-white hover:bg-blue-800 transition"><i class="fa-solid fa-plus mr-1.5"></i>Tambah Akun</a>
    </div>
  </div>

  @if (session('success'))
  <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700">{{ session('success') }}</div>
  @endif

  <!-- Ringkasan cakupan: master SKPD vs akun -->
  <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center shrink-0"><i class="fa-solid fa-building"></i></div>
      <div>
        <p class="text-xl font-extrabold">{{ $totalSkpd }}</p>
        <p class="text-[11px] text-slate-500 font-medium">SKPD terdaftar (master)</p>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0"><i class="fa-solid fa-user-check"></i></div>
      <div>
        <p class="text-xl font-extrabold">{{ $skpdBerakun }}</p>
        <p class="text-[11px] text-slate-500 font-medium">Sudah punya akun</p>
      </div>
    </div>
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0"><i class="fa-solid fa-user-clock"></i></div>
      <div>
        <p class="text-xl font-extrabold">{{ $skpdBelumAkun->count() }}</p>
        <p class="text-[11px] text-slate-500 font-medium">Belum punya akun</p>
      </div>
    </div>
  </div>

  <!-- Akun Administrator (diri sendiri & akun cadangan) -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
      <div>
        <h3 class="font-bold text-sm"><i class="fa-solid fa-user-shield text-blue-700 mr-1.5"></i>Akun Administrator</h3>
        <p class="text-[11px] text-slate-500 mt-0.5">Sesama admin boleh mengubah password sendiri maupun akun admin lainnya (akun cadangan).</p>
      </div>
      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">{{ $admin->count() }} akun</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
      @forelse ($admin as $a)
      <div class="rounded-xl border border-slate-200 p-4 flex items-center gap-3">
        <div class="w-9 h-9 rounded-lg bg-blue-900 text-amber-400 flex items-center justify-center shrink-0">
          <i class="fa-solid fa-user-shield text-xs"></i>
        </div>
        <div class="min-w-0">
          <div class="flex items-center gap-1.5">
            <p class="font-bold text-xs truncate">{{ $a->name }}</p>
            @if ($a->id === auth()->id())
            <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 shrink-0">Anda</span>
            @endif
          </div>
          <p class="text-[11px] text-slate-500 font-mono truncate">{{ $a->username }} • {{ $a->email }}</p>
        </div>
        <a href="{{ route('akun.password.edit', $a) }}" class="ml-auto shrink-0 text-[11px] font-semibold px-3 py-1.5 rounded-lg bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100 transition"><i class="fa-solid fa-key mr-1"></i>Ubah Password</a>
      </div>
      @empty
      <p class="text-sm text-slate-400 py-4">Belum ada akun administrator.</p>
      @endforelse
    </div>
  </div>

  <!-- SKPD yang belum punya akun operator -->
  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
      <div>
        <h3 class="font-bold text-sm"><i class="fa-solid fa-user-plus text-amber-600 mr-1.5"></i>SKPD Belum Punya Akun</h3>
        <p class="text-[11px] text-slate-500 mt-0.5">Master SKPD tetap 47. Buat akun agar operator instansi bisa login (1 SKPD = 1 akun).</p>
      </div>
      <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $skpdBelumAkun->count() }} SKPD</span>
    </div>
    @if ($skpdBelumAkun->isEmpty())
    <p class="text-sm text-slate-400 py-4 text-center">Semua SKPD sudah punya akun.</p>
    @else
    <div class="max-h-72 overflow-y-auto -mx-1 px-1">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
        @foreach ($skpdBelumAkun as $s)
        <div class="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
          <div class="min-w-0">
            <p class="text-xs font-semibold truncate">{{ $s->nama }}</p>
            <p class="text-[10px] text-slate-400 font-mono">{{ $s->singkatan ?: '-' }}</p>
          </div>
          <a href="{{ route('akun.create', ['skpd' => $s->id]) }}" class="ml-auto shrink-0 text-[10px] font-bold px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition"><i class="fa-solid fa-plus mr-1"></i>Buat Akun</a>
        </div>
        @endforeach
      </div>
    </div>
    @endif
  </div>

  <!-- Akun Operator Instansi -->
  <div class="flex items-center justify-between">
    <h3 class="font-bold text-sm">Akun Operator Instansi</h3>
    <span class="text-[11px] text-slate-500">{{ $akun->total() }} akun • 1 instansi = 1 akun</span>
  </div>

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead><tr class="text-left text-slate-400 uppercase text-[10px] tracking-wider border-b border-slate-100 bg-slate-50">
          <th class="py-3 px-4">Instansi</th><th class="py-3 px-4">Username</th><th class="py-3 px-4">Email</th><th class="py-3 px-4 text-center">Aplikasi</th><th class="py-3 px-4 text-right">Aksi</th>
        </tr></thead>
        <tbody>
          @forelse ($akun as $a)
          <tr class="border-b border-slate-50 hover:bg-slate-50/60">
            <td class="py-3 px-4">
              <p class="font-bold">{{ $a->name }}</p>
              <p class="text-[11px] text-slate-500">{{ $a->perangkatDaerah->nama ?? '-' }}</p>
            </td>
            <td class="py-3 px-4 font-mono text-xs">{{ $a->username }}</td>
            <td class="py-3 px-4 text-xs text-slate-500">{{ $a->email }}</td>
            <td class="py-3 px-4 text-center font-extrabold text-blue-800">{{ $a->perangkatDaerah?->aplikasi->count() ?? 0 }}</td>
            <td class="py-3 px-4">
              <div class="flex justify-end gap-1.5">
                <a href="{{ route('akun.edit', $a) }}" class="text-[11px] font-semibold px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition"><i class="fa-solid fa-pen mr-1"></i>Edit</a>
                <form action="{{ route('akun.destroy', $a) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $a->username }}?')">
                  @csrf @method('DELETE')
                  <button class="text-[11px] font-semibold px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition" type="submit"><i class="fa-solid fa-trash mr-1"></i>Hapus</button>
                </form>
              </div>
            </td>
          </tr>
          @empty
          <tr><td colspan="5" class="py-10 text-center text-sm text-slate-400">Belum ada akun instansi.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{ $akun->links() }}
</div>
@endsection
