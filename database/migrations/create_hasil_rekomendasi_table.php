<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_rekomendasi', function (Blueprint $table) {
            $table->id();

            //  Identitas siswa
            $table->string('nama_siswa', 100);
            $table->string('jurusan_smk', 10);              // singkatan: "TKJ"
            $table->string('jurusan_smk_lengkap')->nullable();

            // Nilai input (sesuai form & Flask final) 
            $table->decimal('rata_pai',  5, 2);
            $table->decimal('rata_ppkn', 5, 2);
            $table->decimal('rata_ind',  5, 2);
            $table->decimal('rata_mtk',  5, 2);
            $table->decimal('rata_ing',  5, 2);
            $table->decimal('ukk',       5, 2);

            // Fitur turunan (dihitung Flask) 
            $table->decimal('nilai_max', 5, 2)->nullable();
            $table->decimal('nilai_min', 5, 2)->nullable();
            $table->decimal('nilai_std', 7, 4)->nullable(); // std bisa 0.0000–99.9999

            // Hasil prediksi Random Forest
            // status_prediksi: 1 = berpotensi studi lanjut, 0 = tidak
            $table->tinyInteger('status_prediksi')->default(0);
            $table->string('label_prediksi')->nullable();   // teks status dari Flask
            $table->decimal('probabilitas_kuliah', 6, 4);  // 0.0000 – 1.0000
            $table->decimal('probabilitas_persen', 5, 1)->nullable(); // 0.0 – 100.0
            $table->string('kategori_probabilitas', 10)->nullable();  // "Tinggi" / "Sedang" / "Rendah"
            $table->decimal('threshold_rf', 5, 4)->nullable();        // threshold saat prediksi

            // Hasil KNN Similarity
            $table->decimal('avg_neighbor_similarity', 7, 4)->nullable();
            $table->decimal('similarity_threshold', 7, 4)->nullable();  // null = tidak aktif
            $table->text('knn_warning')->nullable();

            // Rekomendasi KNN (satu JSON array)
            // Setiap item: { ranking, nama_universitas, program_studi,
            //                jumlah_alumni, similarity_score,
            //                frequency_score, final_score }
            $table->json('rekomendasi')->nullable();

            // Interpretasi naratif dari Flask
            $table->text('interpretasi')->nullable();

            $table->timestamps();

            // Index untuk filter & sorting
            $table->index('jurusan_smk');
            $table->index('status_prediksi');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_rekomendasi');
    }
};