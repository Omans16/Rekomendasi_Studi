<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadSiswaRow extends Model
{
    protected $fillable = [
        'upload_siswa_batch_id',
        'row_number',
        'nisn',
        'nama_siswa',
        'jurusan_smk',
        'user_id',
        'hasil_prediksi_id',
        'status',
        'message',
        'payload',
        'response',
    ];

    protected $casts = [
        'payload' => 'array',
        'response' => 'array',
    ];

    public function batch()
    {
        return $this->belongsTo(UploadSiswaBatch::class, 'upload_siswa_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function hasilPrediksi()
    {
        return $this->belongsTo(HasilPrediksi::class, 'hasil_prediksi_id');
    }
}