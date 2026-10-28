<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Platform kini bisa lebih dari satu, disimpan sebagai daftar JSON.
     */
    public function up(): void
    {
        Schema::table('aplikasi', function (Blueprint $table) {
            $table->text('platform')->nullable()->change();
        });

        DB::table('aplikasi')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $decoded = json_decode((string) $row->platform, true);
                $platform = is_array($decoded) ? $decoded : [$row->platform];

                DB::table('aplikasi')->where('id', $row->id)->update([
                    'platform' => json_encode(array_values(array_filter($platform))),
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::table('aplikasi')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $decoded = json_decode((string) $row->platform, true);
                $pertama = is_array($decoded)
                    ? ($decoded[0] ?? 'web')
                    : ($row->platform ?: 'web');

                DB::table('aplikasi')->where('id', $row->id)->update([
                    'platform' => $pertama,
                ]);
            }
        });

        Schema::table('aplikasi', function (Blueprint $table) {
            $table->string('platform', 30)->default('web')->change();
        });
    }
};
