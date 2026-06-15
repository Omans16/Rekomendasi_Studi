<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menjalankan migration.
     */
    public function up(): void
    {
        Schema::create('hasil_prediksis', function (Blueprint $table) {
            $table->id();

            // Input siswa
            $table->string('jurusan_smk');
            $table->decimal('rata_pai', 5, 2);
            $table->decimal('rata_ppkn', 5, 2);
            $table->decimal('rata_ind', 5, 2);
            $table->decimal('rata_mtk', 5, 2);
            $table->decimal('rata_ing', 5, 2);
            $table->decimal('ukk', 5, 2);

            // Hasil utama Random Forest
            $table->unsignedTinyInteger('prediksi_rf')->nullable();
            $table->string('status_rf')->nullable();
            $table->decimal('probabilitas_studi_lanjut', 8, 4)->nullable();
            $table->decimal('threshold_rf', 8, 4)->nullable();
            $table->boolean('knn_dijalankan')->default(false);

            // Hasil KNN dan response lengkap dari Flask
            $table->json('profil_siswa')->nullable();
            $table->json('alumni_terdekat')->nullable();
            $table->text('narasi_rekomendasi')->nullable();
            $table->json('kualitas_rekomendasi')->nullable();
            $table->json('rekomendasi_final')->nullable();
            $table->text('pesan')->nullable();
            $table->json('response_flask')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Membatalkan migration.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_prediksis');
    }
};