<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penilaians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_body_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tahun_id')->constrained('tahuns')->cascadeOnDelete();
            $table->decimal('nilai_presentasi', 8, 2)->nullable(); 
            $table->string('file_bukti_presentasi')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('tanggal_publish')->nullable();
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->unique(['public_body_id', 'tahun_id']); 
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penilaians');
    }
};
