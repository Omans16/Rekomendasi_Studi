<?php

// ================================================================
// app/Services/FlaskService.php
// ================================================================
// Service layer untuk semua komunikasi ke Flask API.
// Dipakai oleh Controller agar logic HTTP-call tidak tersebar.
//
// PENYESUAIAN dari versi sebelumnya:
// 1. Config key: flask.base_url → services.flask.url
//                flask.timeout  → services.flask.timeout
//    (sesuai config/services.php yang sudah dibuat)
//
// 2. getJurusanList() — disesuaikan response Flask final:
//    Flask /jurusan-list return: { "jurusan": [ {singkatan, nama_lengkap, jumlah_alumni} ] }
//    Diformat ulang menjadi: [ ['value' => 'TKJ', 'label' => 'TKJ — Teknik Jaringan... (60 alumni)'] ]
//    Siap dipakai langsung di dropdown blade.
//
// 3. uploadAlumni() — DIHAPUS.
//    Flask final tidak memiliki endpoint /upload-alumni.
//    Update dataset CBF dilakukan langsung di notebook.
//
// 4. isHealthy() — tambah cek key status === 'ok' agar lebih akurat.
// ================================================================

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlaskService
{
    private string $baseUrl;
    private int    $timeout;

    public function __construct()
    {
        // Key config: services.flask.url (bukan flask.base_url)
        // Tambahkan di config/services.php:
        //   'flask' => ['url' => env('FLASK_URL', 'http://localhost:5000'), 'timeout' => 10]
        // Tambahkan di .env:
        //   FLASK_URL=http://localhost:5000
        $this->baseUrl = rtrim(config('services.flask.url', 'http://localhost:5000'), '/');
        $this->timeout = (int) config('services.flask.timeout', 30);
    }

    // ──────────────────────────────────────────────
    // 1. Prediksi + Rekomendasi
    //
    //    Payload yang dikirim ke Flask:
    //    {
    //      "Jurusan_Smk" : "TKJ",     ← singkatan, Flask yang terjemahkan
    //      "rata_pai"    : 85,
    //      "rata_ppkn"   : 82,
    //      "rata_ind"    : 80,
    //      "rata_mtk"    : 88,
    //      "rata_ing"    : 78,
    //      "UKK"         : 85
    //    }
    //
    //    Return: array response Flask atau null jika gagal
    // ──────────────────────────────────────────────
    public function predict(array $data): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->asJson()
                ->post("{$this->baseUrl}/predict", $data);

            if ($response->successful()) {
                $json = $response->json();

                // Jika Flask return field error, anggap gagal
                if (isset($json['error'])) {
                    Log::error('FlaskService::predict – Flask error', [
                        'error' => $json['error'],
                    ]);
                    return null;
                }

                return $json;
            }

            Log::error('FlaskService::predict – response gagal', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('FlaskService::predict – exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ──────────────────────────────────────────────
    // 2. Ambil statistik dashboard
    //
    //    Response Flask:
    //    {
    //      "total_alumni"        : 364,
    //      "total_universitas"   : 22,
    //      "total_jurusan_kuliah": 43,
    //      "total_jurusan_smk"   : 14,
    //      "alumni_per_jurusan"  : { "TKJ": { "nama_lengkap": "...", "jumlah_alumni": 60 } },
    //      "top_universitas"     : [ { "universitas": "...", "jumlah": 45 } ],
    //      "top_jurusan_kuliah"  : [ { "jurusan_kuliah": "...", "jumlah": 18 } ],
    //      "tahun_lulus_min"     : 2020,
    //      "tahun_lulus_max"     : 2024
    //    }
    // ──────────────────────────────────────────────
    public function getDashboard(): ?array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/dashboard");

            return $response->successful() ? $response->json() : null;

        } catch (\Exception $e) {
            Log::error('FlaskService::getDashboard – exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ──────────────────────────────────────────────
    // 3. Ambil daftar jurusan SMK
    //
    //    Response Flask /jurusan-list:
    //    {
    //      "jurusan": [
    //        { "singkatan": "TKJ", "nama_lengkap": "Teknik Jaringan...", "jumlah_alumni": 60 },
    //        ...
    //      ]
    //    }
    //
    //    Return (diformat untuk dropdown blade):
    //    [
    //      [ 'value' => 'TKJ', 'label' => 'TKJ — Teknik Jaringan... (60 alumni)' ],
    //      ...
    //    ]
    // ──────────────────────────────────────────────
    public function getJurusanList(): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->get("{$this->baseUrl}/jurusan-list");

            if (! $response->successful()) {
                return [];
            }

            $items = $response->json('jurusan', []);

            // Format untuk dropdown:
            // value = singkatan (yang dikirim ke Flask saat predict)
            // label = teks yang ditampilkan ke user
            return collect($items)->map(fn($j) => [
                'value' => $j['singkatan'],
                'label' => "{$j['singkatan']} — {$j['nama_lengkap']} ({$j['jumlah_alumni']} alumni)",
            ])->toArray();

        } catch (\Exception $e) {
            Log::error('FlaskService::getJurusanList – exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    // ──────────────────────────────────────────────
    // 4. Health check (boolean)
    //    Dipakai di setiap halaman untuk cek Flask aktif
    // ──────────────────────────────────────────────
    public function isHealthy(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful() && ($response->json('status') === 'ok');
        } catch (\Exception $e) {
            return false;
        }
    }

    // ──────────────────────────────────────────────
    // 5. Health check (full response array)
    //    Dipakai untuk mengambil total_alumni_cbf,
    //    total_fitur_rf, jurusan_tersedia di info-model
    //
    //    Response Flask /health:
    //    {
    //      "status"           : "ok",
    //      "model"            : "BalancedRandomForest + TF-IDF CBF (Hybrid)",
    //      "total_alumni_cbf" : 364,
    //      "total_fitur_rf"   : 42,
    //      "jurusan_tersedia" : ["DPIB","TKJ", ...]
    //    }
    // ──────────────────────────────────────────────
    public function getHealth(): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/health");
            return $response->successful() ? $response->json() : null;
        } catch (\Exception $e) {
            Log::error('FlaskService::getHealth – exception', [
                'message' => $e->getMessage(),
            ]);
            return null;
        }
    }

    // ──────────────────────────────────────────────
// 6. Ambil feature importance + detail fitur dari /model-info
//
//    Response Flask /model-info:
//    {
//      "model_name"         : "BalancedRandomForestClassifier",
//      "total_fitur"        : 42,
//      "total_fitur_numerik": 9,
//      "total_fitur_ohe"    : 33,
//      "fitur_numerik"      : [...],
//      "fitur_ohe_jurusan"  : [...],
//      "feature_importance" : { "rata_mtk": 0.18, ... },
//      "confusion_matrix"   : null,
//      "performa_notebook"  : { "precision": 0.37, ... }
//    }
// ──────────────────────────────────────────────
public function getModelInfo(): ?array
{
    try {
        $response = Http::timeout($this->timeout)
            ->get("{$this->baseUrl}/model-info");

        return $response->successful() ? $response->json() : null;

    } catch (\Exception $e) {
        Log::error('FlaskService::getModelInfo – exception', [
            'message' => $e->getMessage(),
        ]);
        return null;
    }
}
}
