<?php

namespace App\Providers;

use App\Models\Aplikasi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Badge jumlah pengajuan menunggu untuk navbar admin
        View::composer('layouts.app', function ($view) {
            $pengajuanMenunggu = 0;

            if (Auth::check() && Auth::user()->role === 'admin') {
                $pengajuanMenunggu = Aplikasi::where('verifikasi', 'diajukan')->count();
            }

            $view->with('pengajuanMenunggu', $pengajuanMenunggu);
        });
    }
}
