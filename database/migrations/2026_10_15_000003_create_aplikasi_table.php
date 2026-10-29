<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Data aplikasi milik Perangkat Daerah.
     */
    public function up(): void
    {
        Schema::create('aplikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perangkat_daerah_id')->constrained('perangkat_daerah')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('singkatan', 30)->nullable();
            $table->text('deskripsi')->nullable();
            $table->year('tahun')->nullable();
            $table->string('platform', 30)->default('web'); // web, android, ios, desktop
            $table->string('url')->nullable();
            $table->string('status', 30)->default('aktif'); // aktif, nonaktif, pengembangan
            $table->string('verifikasi', 30)->default('diajukan'); // diajukan, terverifikasi, dikembalikan
            $table->text('catatan_verifikasi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aplikasi');
    }
};
