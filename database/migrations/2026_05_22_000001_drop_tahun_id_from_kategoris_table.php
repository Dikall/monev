<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadikan Kategori bersifat global (tidak terikat tahun).
     * Hapus foreign key dan kolom tahun_id dari tabel kategoris.
     */
    public function up(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            // Drop foreign key constraint terlebih dahulu
            $table->dropForeign(['tahun_id']);
            // Lalu drop kolom
            $table->dropColumn('tahun_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategoris', function (Blueprint $table) {
            $table->foreignId('tahun_id')
                ->nullable()
                ->constrained('tahuns')
                ->onDelete('cascade');
        });
    }
};
