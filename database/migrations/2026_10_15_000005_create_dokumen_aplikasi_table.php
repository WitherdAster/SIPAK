<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen pendukung pengajuan: SOP, manual, teknis, PKS,
     * laporan audit, dan dokumen pendukung lainnya.
     */
    public function up(): void
    {
        Schema::create('dokumen_aplikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aplikasi_id')->constrained('aplikasi')->cascadeOnDelete();
            $table->string('jenis', 30); // sop, manual, teknis, pks, audit, pendukung
            $table->string('nama_asli');
            $table->string('path');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dokumen_aplikasi');
    }
};
