@extends('layouts.app')

@section('title', 'Form Pengajuan Aplikasi - SIPAK')

@section('content')
<div class="max-w-3xl mx-auto space-y-5">
  <div>
    <h2 class="text-xl font-extrabold">Form Pengajuan Aplikasi</h2>
    <p class="text-xs text-slate-500">Diajukan atas nama <span class="font-semibold text-blue-700">{{ auth()->user()->perangkatDaerah->nama ?? '-' }}</span> • menunggu verifikasi Diskominfo</p>
  </div>

  @include('aplikasi._form', [
    'action' => route('aplikasi.store'),
    'method' => 'POST',
    'submitLabel' => 'Kirim Pengajuan',
    'submitIcon' => 'fa-paper-plane',
  ])
</div>
@endsection
