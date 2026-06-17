<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_prediksis', function (Blueprint $table) {
            $table->id();

            // Relasi ke user login
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identitas siswa
            $table->string('nisn')->nullable();
            $table->string('nama_siswa')->nullable();

            // Data jurusan
            $table->string('jurusan_smk');
            $table->string('jurusan_smk_lengkap')->nullable();

            // Nilai input
            $table->decimal('rata_pai', 5, 2);
            $table->decimal('rata_ppkn', 5, 2);
            $table->decimal('rata_ind', 5, 2);
            $table->decimal('rata_mtk', 5, 2);
            $table->decimal('rata_ing', 5, 2);
            $table->decimal('ukk', 5, 2);

            // Fitur turunan
            $table->decimal('nilai_max', 5, 2)->nullable();
            $table->decimal('nilai_min', 5, 2)->nullable();
            $table->decimal('nilai_std', 5, 2)->nullable();

            // Output Random Forest
            $table->tinyInteger('prediksi_rf')->nullable();
            $table->string('status_rf')->nullable();
            $table->decimal('probabilitas_studi_lanjut', 8, 4)->nullable();
            $table->decimal('threshold_rf', 8, 4)->nullable();

            // Status KNN
            $table->boolean('knn_dijalankan')->default(false);

            // Output Flask/KNN
            $table->json('profil_siswa')->nullable();
            $table->json('alumni_terdekat')->nullable();
            $table->text('narasi_rekomendasi')->nullable();
            $table->json('kualitas_rekomendasi')->nullable();
            $table->json('rekomendasi_final')->nullable();

            // Pesan dan response mentah Flask
            $table->text('pesan')->nullable();
            $table->json('response_flask')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_prediksis');
    }
};