<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HasilPrediksi extends Model
{
    use HasFactory;

    protected $table = 'hasil_prediksis';

    protected $fillable = [
        'jurusan_smk',
        'rata_pai',
        'rata_ppkn',
        'rata_ind',
        'rata_mtk',
        'rata_ing',
        'ukk',

        'prediksi_rf',
        'status_rf',
        'probabilitas_studi_lanjut',
        'threshold_rf',
        'knn_dijalankan',

        'profil_siswa',
        'alumni_terdekat',
        'narasi_rekomendasi',
        'kualitas_rekomendasi',
        'rekomendasi_final',
        'pesan',
        'response_flask',
    ];

    protected $casts = [
        'rata_pai' => 'decimal:2',
        'rata_ppkn' => 'decimal:2',
        'rata_ind' => 'decimal:2',
        'rata_mtk' => 'decimal:2',
        'rata_ing' => 'decimal:2',
        'ukk' => 'decimal:2',

        'probabilitas_studi_lanjut' => 'decimal:4',
        'threshold_rf' => 'decimal:4',
        'knn_dijalankan' => 'boolean',

        'profil_siswa' => 'array',
        'alumni_terdekat' => 'array',
        'kualitas_rekomendasi' => 'array',
        'rekomendasi_final' => 'array',
        'response_flask' => 'array',
    ];
}