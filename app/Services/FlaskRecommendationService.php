<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class FlaskRecommendationService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.flask.base_url'), '/');
    }

    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(20)->get($this->baseUrl . '/');

            return $response->json() ?? [
                'success' => false,
                'message' => 'Response Flask tidak valid.',
            ];
        } catch (ConnectionException $error) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke API Flask. Pastikan Flask berjalan di ' . $this->baseUrl,
            ];
        } catch (\Throwable $error) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi Flask: ' . $error->getMessage(),
            ];
        }
    }

    public function ambilDaftarJurusan(): array
    {
        try {
            $response = Http::timeout(20)->get($this->baseUrl . '/jurusan');

            if (!$response->successful()) {
                return [];
            }

            $json = $response->json();

            return $json['data'] ?? [];
        } catch (\Throwable $error) {
            return [];
        }
    }

    public function prediksiRekomendasi(array $dataInput): array
    {
        try {
            $payload = [
                'Jurusan_Smk' => $dataInput['jurusan_smk'],
                'rata_pai' => (float) $dataInput['rata_pai'],
                'rata_ppkn' => (float) $dataInput['rata_ppkn'],
                'rata_ind' => (float) $dataInput['rata_ind'],
                'rata_mtk' => (float) $dataInput['rata_mtk'],
                'rata_ing' => (float) $dataInput['rata_ing'],
                'UKK' => (float) $dataInput['ukk'],
            ];

            $response = Http::timeout(60)
                ->acceptJson()
                ->asJson()
                ->post($this->baseUrl . '/predict', $payload);

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $json['message'] ?? 'Proses prediksi dari Flask gagal.',
                    'detail' => $json,
                ];
            }

            return $json ?? [
                'success' => false,
                'message' => 'Response Flask kosong atau tidak valid.',
            ];
        } catch (ConnectionException $error) {
            return [
                'success' => false,
                'message' => 'Tidak dapat terhubung ke API Flask. Pastikan Flask sudah berjalan.',
            ];
        } catch (RequestException $error) {
            return [
                'success' => false,
                'message' => 'Request ke API Flask gagal: ' . $error->getMessage(),
            ];
        } catch (\Throwable $error) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses prediksi: ' . $error->getMessage(),
            ];
        }
    }

    public function ambilInfoModel(): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . '/info-model');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $json['message'] ?? 'Gagal mengambil informasi model.',
                    'data' => [],
                ];
            }

            return $json ?? [
                'success' => false,
                'message' => 'Response info model tidak valid.',
                'data' => [],
            ];
        } catch (\Throwable $error) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil informasi model: ' . $error->getMessage(),
                'data' => [],
            ];
        }
    }


    public function ambilFeatureImportance(): array
    {
        try {
            $response = Http::timeout(30)
                ->get($this->baseUrl . '/feature-importance');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $json['message'] ?? 'Gagal mengambil feature importance.',
                    'data' => [],
                ];
            }

            return $json ?? [
                'success' => false,
                'message' => 'Response feature importance tidak valid.',
                'data' => [],
            ];
        } catch (\Throwable $error) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil feature importance: ' . $error->getMessage(),
                'data' => [],
            ];
        }
    }

    public function ambilEvaluation(): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . '/evaluation');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $json['message'] ?? 'Gagal mengambil evaluasi model.',
                    'data' => [],
                ];
            }

            return $json ?? [
                'success' => false,
                'message' => 'Response evaluasi model tidak valid.',
                'data' => [],
            ];
        } catch (\Throwable $error) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil evaluasi model: ' . $error->getMessage(),
                'data' => [],
            ];
        }
    }

    public function ambilDashboardStats(): array
    {
        try {
            $response = Http::timeout(30)->get($this->baseUrl . '/dashboard-stats');

            $json = $response->json();

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'message' => $json['message'] ?? 'Gagal mengambil statistik dashboard.',
                    'data' => [],
                ];
            }

            return $json ?? [
                'success' => false,
                'message' => 'Response statistik dashboard tidak valid.',
                'data' => [],
            ];

        } catch (\Throwable $error) {
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil statistik dashboard: ' . $error->getMessage(),
                'data' => [],
            ];
        }
    }
}