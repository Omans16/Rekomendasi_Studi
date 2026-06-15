<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilRekomendasi extends Model
{
    use HasFactory;

    protected $table = 'hasil_rekomendasi';

    protected $fillable = [
        // Identitas siswa
        'nama_siswa',
        'jurusan_smk',              // singkatan: "TKJ"
        'jurusan_smk_lengkap',
        'rata_pai',
        'rata_ppkn',
        'rata_ind',
        'rata_mtk',
        'rata_ing',
        'ukk',

        // ── Fitur turunan (dihitung Flask, disimpan untuk referensi)
        'nilai_max',
        'nilai_min',
        'nilai_std',                // std_nilai dari Flask (ddof=0)

        // Hasil prediksi Random Forest
        'status_prediksi',          // int: 1 = berpotensi, 0 = tidak
        'label_prediksi',           // string: "Teridentifikasi Studi Lanjut" / "Tidak Teridentifikasi Studi Lanjut"
        'probabilitas_kuliah',      // float 0-1: probabilitas_studi_lanjut dari Flask
        'probabilitas_persen',      // float: 72.3
        'kategori_probabilitas',    // "Tinggi" / "Sedang" / "Rendah"
        'threshold_rf',             // float: threshold RF yang dipakai saat prediksi

        // Hasil KNN Similarity
        'avg_neighbor_similarity',  // float: rata-rata similarity dari N tetangga
        'similarity_threshold',     // float|null: threshold KNN (null = tidak aktif)
        'knn_warning',              // string|null: pesan jika similarity di bawah threshold

        // Rekomendasi KNN (satu JSON array) 
        // Setiap item: { ranking, nama_universitas, program_studi,
        //                jumlah_alumni, similarity_score,
        //                frequency_score, final_score }
        'rekomendasi',

        // Interpretasi naratif dari Flask
        'interpretasi',
    ];

    protected $casts = [
        // JSON → array otomatis
        'rekomendasi'              => 'array',

        // Integer
        'status_prediksi'          => 'integer',

        // Float
        'probabilitas_kuliah'      => 'float',
        'probabilitas_persen'      => 'float',
        'threshold_rf'             => 'float',
        'avg_neighbor_similarity'  => 'float',
        'similarity_threshold'     => 'float',
        'rata_pai'                 => 'float',
        'rata_ppkn'                => 'float',
        'rata_ind'                 => 'float',
        'rata_mtk'                 => 'float',
        'rata_ing'                 => 'float',
        'ukk'                      => 'float',
        'nilai_max'                => 'float',
        'nilai_min'                => 'float',
        'nilai_std'                => 'float',
    ];

    // Scope: filter yang berpotensi studi lanjut
    // status_prediksi sekarang integer (1 = berpotensi)
    public function scopeKuliah($query)
    {
        return $query->where('status_prediksi', 1);
    }

    // Scope: filter yang tidak berpotensi studi lanjut
    public function scopeTidakKuliah($query)
    {
        return $query->where('status_prediksi', 0);
    }

    // Scope: filter berdasarkan jurusan SMK (singkatan)
    public function scopeByJurusan($query, string $jurusan)
    {
        return $query->where('jurusan_smk', strtoupper($jurusan));
    }

    // Accessor: ambil rekomendasi teratas (ranking 1)
    public function getRekomendasiTeratasAttribute(): ?array
    {
        $rek = $this->rekomendasi;
        if (empty($rek)) return null;
        return collect($rek)->firstWhere('ranking', 1) ?? $rek[0];
    }

    // // Accessor: ada rekomendasi atau tidak
    public function getAdaRekomendasiAttribute(): bool
    {
        return !empty($this->rekomendasi);
    }
}