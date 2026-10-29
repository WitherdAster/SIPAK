<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Catat waktu verifikasi agar riwayat tercatat otomatis dan berurutan.
     */
    public function up(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->timestamp('diverifikasi_at')->nullable()->after('catatan_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->dropColumn('diverifikasi_at');
        });
    }
};
