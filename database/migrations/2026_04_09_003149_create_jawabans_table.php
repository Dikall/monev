<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jawabans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');

            $table->foreignId('public_body_id')
                ->constrained('public_bodies')
                ->onDelete('cascade');

            $table->foreignId('pertanyaan_id')
                ->constrained('pertanyaans')
                ->onDelete('cascade');

            $table->foreignId('tahun_id')
                ->constrained('tahuns')
                ->onDelete('cascade');

            // Jawaban Ya/Tidak
            $table->tinyInteger('jawaban')->nullable(); // 1 = Ya, 0 = Tidak

            // Data pendukung
            $table->text('links')->nullable(); // disimpan sebagai JSON array of links
            $table->string('dokumen_path')->nullable(); // path file PDF

            $table->boolean('is_submitted')->default(false);
            $table->timestamp('submitted_at')->nullable();

            $table->boolean('is_verified')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();

            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            // Satu jawaban per pertanyaan per badan publik per tahun
            $table->unique(['public_body_id', 'pertanyaan_id', 'tahun_id'], 'unique_jawaban');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jawabans');
    }
};