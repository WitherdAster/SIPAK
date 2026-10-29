<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jaminan di level database: satu SKPD hanya boleh punya satu akun.
     * NULL tetap boleh banyak (akun admin tidak terikat instansi).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unique('perangkat_daerah_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['perangkat_daerah_id']);
        });
    }
};
