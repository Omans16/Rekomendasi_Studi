<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // Login utama menggunakan NISN
            $table->string('nisn')->unique();

            // Nama pengguna: siswa/admin/guru BK
            $table->string('name');

            // Password login
            $table->string('password');

            // Hak akses
            $table->enum('role', ['admin', 'guru_bk', 'siswa'])
                ->default('siswa');

            // Khusus siswa kelas 12
            $table->unsignedTinyInteger('kelas')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};