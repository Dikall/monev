<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ubah jawabans.user_id dari cascade delete menjadi nullOnDelete
     * agar data rekap kuesioner tetap ada walau akun badan publik dihapus.
     */
    public function up(): void
    {
        Schema::table('jawabans', function (Blueprint $table) {
            // Drop foreign key lama (cascade)
            $table->dropForeign(['user_id']);

            // Re-add dengan nullOnDelete agar jawaban tidak ikut terhapus
            $table->foreignId('user_id')
                ->nullable()
                ->change();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawabans', function (Blueprint $table) {
            $table->dropForeign(['user_id']);

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
