<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom detail pengajuan sesuai PRD: pengelolaan, infrastruktur,
     * integrasi, dan keamanan.
     */
    public function up(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            // Pengelolaan
            $table->string('vendor')->nullable()->after('url');
            $table->string('pic_nama')->nullable()->after('vendor');
            $table->string('pic_kontak')->nullable()->after('pic_nama');
            $table->unsignedInteger('jumlah_pengguna')->nullable()->after('pic_kontak');
            // Infrastruktur
            $table->string('server_hosting')->nullable()->after('jumlah_pengguna');
            $table->string('teknologi')->nullable()->after('server_hosting');
            $table->string('db_teknologi')->nullable()->after('teknologi');
            // Integrasi
            $table->boolean('punya_api')->default(false)->after('db_teknologi');
            $table->text('sistem_terintegrasi')->nullable()->after('punya_api');
            $table->boolean('splp')->default(false)->after('sistem_terintegrasi');
            // Keamanan
            $table->boolean('audit_keamanan')->default(false)->after('splp');
            $table->boolean('backup_data')->default(false)->after('audit_keamanan');
            $table->boolean('disaster_recovery')->default(false)->after('backup_data');
        });
    }

    public function down(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->dropColumn([
                'vendor', 'pic_nama', 'pic_kontak', 'jumlah_pengguna',
                'server_hosting', 'teknologi', 'db_teknologi',
                'punya_api', 'sistem_terintegrasi', 'splp',
                'audit_keamanan', 'backup_data', 'disaster_recovery',
            ]);
        });
    }
};
