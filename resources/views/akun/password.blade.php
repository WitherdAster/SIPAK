@extends('layouts.app')

@section('title', 'Ubah Password Admin - SIPAK')

@php $diriSendiri = $akun->id === auth()->id(); @endphp

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
  <div>
    <h2 class="text-xl font-extrabold">Ubah Password Administrator</h2>
    <p class="text-xs text-slate-500">{{ $diriSendiri ? 'Perbarui password akun Anda sendiri.' : 'Perbarui password akun admin lainnya (akun cadangan).' }}</p>
  </div>

  @if ($errors->any())
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $errors->first() }}</div>
  @endif

  <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
    <div class="flex items-start gap-3">
      <div class="w-11 h-11 rounded-xl bg-blue-900 text-amber-400 flex items-center justify-center shrink-0">
        <i class="fa-solid fa-user-shield"></i>
      </div>
      <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-2">
          <p class="font-bold text-sm">{{ $akun->name }}</p>
          @if ($diriSendiri)
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Akun Anda</span>
          @else
          <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">Admin lainnya</span>
          @endif
        </div>
        <p class="text-[11px] text-slate-500 mt-0.5 font-mono">{{ $akun->username }} • {{ $akun->email }}</p>
      </div>
    </div>

    @if ($diriSendiri)
    <p class="mt-4 text-[11px] rounded-xl bg-blue-50 border border-blue-100 text-blue-800 px-3 py-2.5"><i class="fa-solid fa-circle-info mr-1.5"></i>Gunakan password baru ini saat login berikutnya. Sesi Anda saat ini tetap aktif.</p>
    @else
    <p class="mt-4 text-[11px] rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-3 py-2.5"><i class="fa-solid fa-triangle-exclamation mr-1.5"></i>Anda akan mengganti password akun admin lain. Pastikan perubahan ini sudah dikoordinasikan agar akun cadangan tetap bisa dipakai.</p>
    @endif
  </div>

  <form action="{{ route('akun.password.update', $akun) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4" @unless ($diriSendiri) onsubmit="return confirm('Ganti password akun {{ $akun->username }}?')" @endunless>
    @csrf @method('PUT')

    <div>
      <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password">Password Baru *</label>
      <input id="password" name="password" type="password" required minlength="8" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Minimal 8 karakter" autocomplete="new-password" autofocus>
      <p class="text-[11px] text-slate-400 mt-1">Minimal 8 karakter. Sebaiknya gabungkan huruf, angka, dan simbol.</p>
    </div>

    <div>
      <label class="block text-xs font-semibold uppercase tracking-wider text-slate-600 mb-1.5" for="password_confirmation">Konfirmasi Password Baru *</label>
      <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Ulangi password baru" autocomplete="new-password">
    </div>

    <div class="flex gap-2 pt-1">
      <a href="{{ route('akun.index') }}" class="flex-1 text-center py-3 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 transition">Batal</a>
      <button class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 transition" type="submit"><i class="fa-solid fa-key mr-1.5"></i>Simpan Password</button>
    </div>
  </form>
</div>
@endsection
