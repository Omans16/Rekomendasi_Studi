<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UploadSiswaBatch extends Model
{
    protected $fillable = [
        'uploaded_by',
        'original_filename',
        'stored_filename',
        'total_rows',
        'valid_rows',
        'failed_rows',
        'linked_user_count',
        'unlinked_user_count',
        'prediksi_success_count',
        'rekomendasi_success_count',
        'status',
        'summary',
    ];

    protected $casts = [
        'summary' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function rows()
    {
        return $this->hasMany(UploadSiswaRow::class, 'upload_siswa_batch_id');
    }
}