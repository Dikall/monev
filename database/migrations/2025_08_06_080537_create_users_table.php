<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('public_body_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name')->nullable(); // hanya untuk admin dan superadmin
            $table->string('username', 100)->nullable()->unique(); // hanya untuk admin dan superadmin
            $table->string('alamat')->nullable(); // hanya digunakan badan publik
            $table->string('telepon')->nullable(); // hanya digunakan badan publik
            $table->string('website')->nullable(); // hanya digunakan badan publik

            $table->string('email')->nullable(); // email login
            $table->string('password');

            // Khusus untuk badan publik
            $table->string('nama_responden')->nullable();
            $table->string('jabatan_responden')->nullable();
            $table->string('nohp_responden')->nullable();
            $table->string('email_responden')->unique()->nullable();
            $table->string('nama_ppid')->nullable();
            $table->string('nohp_ppid')->nullable();
            $table->string('email_ppid')->nullable();

            $table->boolean('is_aktif')->default(false); // untuk badan publik

            $table->tinyInteger('type')->default(0); // 0: badan publik, 1: admin, 2: superadmin
            $table->rememberToken();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};