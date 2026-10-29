@extends('layouts.app')

@section('title', 'Edit Akun - SIPAK')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">
  <div>
    <h2 class="text-xl font-extrabold">Edit Akun: {{ $akun->username }}</h2>
    <p class="text-xs text-slate-500">Perbarui data akun operator instansi</p>
  </div>

  @if ($errors->any())
  <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-medium text-red-700">{{ $errors->first() }}</div>
  @endif

  <form action="{{ route('akun.update', $akun) }}" method="POST" class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
    @csrf @method('PUT')
    @include('akun._form')
    <div class="flex gap-2">
      <a href="{{ route('akun.index') }}" class="flex-1 text-center py-3 rounded-xl text-sm font-semibold bg-slate-100 hover:bg-slate-200 transition">Batal</a>
      <button class="flex-1 py-3 rounded-xl text-sm font-semibold text-white bg-blue-700 hover:bg-blue-800 transition" type="submit"><i class="fa-solid fa-floppy-disk mr-1.5"></i>Simpan</button>
    </div>
  </form>
</div>
@endsection
