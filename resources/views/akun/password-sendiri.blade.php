@extends('layouts.app')

@section('title', 'Ubah Password - SIPAK')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
  <div>
    <h2 class="text-xl font-extrabold">Ubah Password</h2>
    <p class="text-xs text-slate-500">Perbarui password akun Anda sendiri. Berlaku untuk admin maupun operator instansi.</p>
  </div>

  @if (session('success'))
  <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-xs font-medium text-emerald-700">{{ session('success') }}</div>
  @endif
  @if ($errors->any())
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $errors->first() }}</div>
  @endif

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <div class="flex items-start gap-3">
      <div class="w-11 h-11 rounded-xl bg-blue-900 text-amber-400 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-{{ $akun->role === 'admin' ? 'user-shield' : 'user-tie' }}"></i>
      </div>
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <p class="font-bold text-sm">{{ $akun->name }}</p>
          @if ($akun->role === 'admin')
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">Administrator</span>
          @else
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Operator Instansi</span>
          @endif
        </div>
        <p class="text-[11px] text-slate-500 mt-0.5 font-mono">{{ $akun->username }} • {{ $akun->email }}</p>
        @if ($akun->perangkatDaerah)
        <p class="text-[11px] text-slate-500 mt-0.5"><i class="fa-solid fa-building mr-1"></i>{{ $akun->perangkatDaerah->nama }}</p>
        @endif
      </div>
    </div>
    <p class="mt-4 text-[11px] rounded-xl bg-blue-50 border border-blue-100 text-blue-800 px-3 py-2.5"><i class="fa-solid fa-shield-halved mr-1.5"></i>Masukkan password lama untuk keamanan. Sesi Anda saat ini tetap aktif setelah password diganti.</p>
  </div>

  <form action="{{ route('profil.password.update') }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
    @csrf
    @method('PUT')

    <div>
      <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password_lama">Password Lama *</label>
      <input id="password_lama" name="password_lama" type="password" required class="w-full rounded-xl border-slate-300 text-sm" placeholder="Password yang digunakan sekarang" autocomplete="current-password" autofocus>
    </div>

    <div>
      <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password">Password Baru *</label>
      <input id="password" name="password" type="password" required minlength="8" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Minimal 8 karakter" autocomplete="new-password">
      <p class="text-[11px] text-slate-400 mt-1">Minimal 8 karakter dan harus berbeda dari password lama. Sebaiknya gabungkan huruf, angka, dan simbol.</p>
    </div>

    <div>
      <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password_confirmation">Konfirmasi Password Baru *</label>
      <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Ulangi password baru" autocomplete="new-password">
    </div>

    <div class="flex gap-2 pt-1">
      <a href="{{ route('dashboard') }}" class="flex-1 text-center py-3 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 transition">Batal</a>
      <button class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 transition" type="submit"><i class="fa-solid fa-key mr-1.5"></i>Simpan Password</button>
    </div>
  </form>
</div>
@endsection
