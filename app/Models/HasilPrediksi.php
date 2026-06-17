<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilPrediksi extends Model
{
    use HasFactory;

    protected $table = 'hasil_prediksis';

    protected $fillable = [
        'user_id',
        'upload_batch_id',
        'nisn',
        'nama_siswa',

        'jurusan_smk',
        'jurusan_smk_lengkap',

        'rata_pai',
        'rata_ppkn',
        'rata_ind',
        'rata_mtk',
        'rata_ing',
        'ukk',

        'nilai_max',
        'nilai_min',
        'nilai_std',

        'prediksi_rf',
        'status_rf',
        'probabilitas_studi_lanjut',
        'kategori_probabilitas',
        'threshold_rf',
        'knn_dijalankan',

        'profil_siswa',
        'alumni_terdekat',
        'narasi_rekomendasi',
        'kualitas_rekomendasi',
        'rekomendasi_final',
        'pesan',
        'response_flask',

        'sumber',
        'error_message',
    ];

    protected $casts = [
        'rata_pai' => 'decimal:2',
        'rata_ppkn' => 'decimal:2',
        'rata_ind' => 'decimal:2',
        'rata_mtk' => 'decimal:2',
        'rata_ing' => 'decimal:2',
        'ukk' => 'decimal:2',

        'nilai_max' => 'decimal:2',
        'nilai_min' => 'decimal:2',
        'nilai_std' => 'decimal:4',

        'probabilitas_studi_lanjut' => 'decimal:4',
        'threshold_rf' => 'decimal:4',
        'knn_dijalankan' => 'boolean',

        'profil_siswa' => 'array',
        'alumni_terdekat' => 'array',
        'kualitas_rekomendasi' => 'array',
        'rekomendasi_final' => 'array',
        'response_flask' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function uploadBatch()
    {
        return $this->belongsTo(UploadSiswaBatch::class, 'upload_batch_id');
    }
}