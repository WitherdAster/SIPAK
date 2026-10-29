<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Master Perangkat Daerah / SKPD / instansi.
     */
    public function up(): void
    {
        Schema::create('perangkat_daerah', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('singkatan', 20)->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('perangkat_daerah_id')
                ->nullable()
                ->after('role')
                ->constrained('perangkat_daerah')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('perangkat_daerah_id');
        });

        Schema::dropIfExists('perangkat_daerah');
    }
};
