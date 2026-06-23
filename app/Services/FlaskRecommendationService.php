<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class FlaskRecommendationService
{
    protected string $baseUrl;
    protected string $predictEndpoint;
    protected string $healthEndpoint;
    protected string $jurusanEndpoint;
    protected string $dashboardStatsEndpoint;
    protected string $infoModelEndpoint;
    protected string $featureImportanceEndpoint;
    protected string $evaluationEndpoint;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('flask.base_url'), '/');

        $this->predictEndpoint = config('flask.predict_endpoint', '/predict');
        $this->healthEndpoint = config('flask.health_endpoint', '/health');
        $this->jurusanEndpoint = config('flask.jurusan_endpoint', '/jurusan');
        $this->dashboardStatsEndpoint = config('flask.dashboard_stats_endpoint', '/dashboard-stats');
        $this->infoModelEndpoint = config('flask.info_model_endpoint', '/info-model');
        $this->featureImportanceEndpoint = config('flask.feature_importance_endpoint', '/feature-importance');
        $this->evaluationEndpoint = config('flask.evaluation_endpoint', '/evaluation');

        $this->timeout = (int) config('flask.timeout', 30);
    }

    private function makeUrl(string $endpoint): string
    {
        $endpoint = trim($endpoint);

        if (preg_match('/^https?:\/\//', $endpoint)) {
            return $endpoint;
        }

        return $this->baseUrl . '/' . ltrim($endpoint, '/');
    }

    private function getRequest(string $endpoint, string $context = 'Flask GET'): array
    {
        $url = $this->makeUrl($endpoint);

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(10)
                ->acceptJson()
                ->get($url);

            $json = $response->json();
            $data = is_array($json) ? $json : [
                'raw' => $response->body(),
            ];

            if (!$response->successful()) {
                Log::warning($context . ' gagal.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'status_code' => $response->status(),
                    'message' => $data['message'] ?? $data['error'] ?? 'Gagal mengambil data dari API Flask.',
                    'data' => [],
                    'raw' => $data,
                ];
            }

            return [
                'success' => true,
                'status_code' => $response->status(),
                'message' => $data['message'] ?? 'Berhasil mengambil data dari API Flask.',
                'data' => $this->extractData($data),
                'raw' => $data,
            ];
        } catch (Throwable $e) {
            Log::error($context . ' error.', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Tidak dapat terhubung ke API Flask: ' . $e->getMessage(),
                'data' => [],
                'raw' => [],
            ];
        }
    }

    private function postRequest(string $endpoint, array $payload, string $context = 'Flask POST'): array
    {
        $url = $this->makeUrl($endpoint);

        try {
            $response = Http::timeout($this->timeout)
                ->connectTimeout(10)
                ->acceptJson()
                ->asJson()
                ->post($url, $payload);

            $json = $response->json();
            $data = is_array($json) ? $json : [
                'raw' => $response->body(),
            ];

            if (!$response->successful()) {
                Log::warning($context . ' gagal.', [
                    'url' => $url,
                    'status' => $response->status(),
                    'payload' => $payload,
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'status_code' => $response->status(),
                    'message' => $data['message'] ?? $data['error'] ?? 'Prediksi gagal diproses oleh API Flask.',
                    'response_flask' => $data,
                ];
            }

            $result = $this->flattenPredictResponse($data);
            $result['success'] = $result['success'] ?? true;
            $result['status_code'] = $response->status();

            return $result;
        } catch (Throwable $e) {
            Log::error($context . ' error.', [
                'url' => $url,
                'payload' => $payload,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status_code' => 0,
                'message' => 'Tidak dapat terhubung ke API Flask: ' . $e->getMessage(),
            ];
        }
    }

    private function extractData(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data'])) {
            return $payload['data'];
        }

        return $payload;
    }

    private function extractByKeys(array $payload, array $keys): array
    {
        $data = $this->extractData($payload);

        foreach ($keys as $key) {
            if (isset($data[$key]) && is_array($data[$key])) {
                return $data[$key];
            }
        }

        return is_array($data) ? $data : [];
    }

    private function flattenPredictResponse(array $payload): array
    {
        if (isset($payload['data']) && is_array($payload['data'])) {
            return array_merge($payload['data'], $payload);
        }

        return $payload;
    }

    private function normalizePredictPayload(array $input): array
    {
        $jurusan = $input['jurusan_smk']
            ?? $input['Jurusan_Smk']
            ?? null;

        $ukk = $input['ukk']
            ?? $input['UKK']
            ?? null;

        return [
            // Format tambahan untuk upload/manual Laravel
            'nisn' => $input['nisn'] ?? null,
            'nama_siswa' => $input['nama_siswa'] ?? null,

            // Format lowercase Laravel
            'jurusan_smk' => $jurusan,
            'ukk' => isset($ukk) ? (float) $ukk : null,

            // Format asli yang terlihat dari Flask /health
            'Jurusan_Smk' => $jurusan,
            'UKK' => isset($ukk) ? (float) $ukk : null,

            'rata_pai' => isset($input['rata_pai']) ? (float) $input['rata_pai'] : null,
            'rata_ppkn' => isset($input['rata_ppkn']) ? (float) $input['rata_ppkn'] : null,
            'rata_ind' => isset($input['rata_ind']) ? (float) $input['rata_ind'] : null,
            'rata_mtk' => isset($input['rata_mtk']) ? (float) $input['rata_mtk'] : null,
            'rata_ing' => isset($input['rata_ing']) ? (float) $input['rata_ing'] : null,
        ];
    }

    public function healthCheck(): array
    {
        $response = $this->getRequest($this->healthEndpoint, 'Health check Flask');

        return [
            'success' => $response['success'] ?? false,
            'status_code' => $response['status_code'] ?? 0,
            'message' => $response['message'] ?? null,
            'data' => $response['data'] ?? [],
            'raw' => $response['raw'] ?? [],
        ];
    }

    public function ambilDaftarJurusan(): array
    {
        $response = $this->getRequest($this->jurusanEndpoint, 'Ambil daftar jurusan Flask');

        if (!($response['success'] ?? false)) {
            return [];
        }

        $data = $response['data'] ?? [];

        if (isset($data['jurusan']) && is_array($data['jurusan'])) {
            return $data['jurusan'];
        }

        if (isset($data['daftar_jurusan']) && is_array($data['daftar_jurusan'])) {
            return $data['daftar_jurusan'];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        if (array_is_list($data)) {
            return $data;
        }

        return [];
    }

    public function ambilDashboardStats(): array
    {
        $response = $this->getRequest($this->dashboardStatsEndpoint, 'Ambil dashboard stats Flask');

        if (!($response['success'] ?? false)) {
            return $response;
        }

        $raw = $response['raw'] ?? [];
        $data = $this->extractByKeys($raw, [
            'dashboard_stats',
            'stats',
            'dashboard',
        ]);

        return [
            'success' => true,
            'status_code' => $response['status_code'] ?? 200,
            'message' => $response['message'] ?? null,
            'data' => $data,
            'raw' => $raw,
        ];
    }

    public function ambilInfoModel(): array
    {
        $response = $this->getRequest($this->infoModelEndpoint, 'Ambil info model Flask');

        if (!($response['success'] ?? false)) {
            return $response;
        }

        $raw = $response['raw'] ?? [];
        $data = $this->extractByKeys($raw, [
            'info_model',
            'model',
        ]);

        return [
            'success' => true,
            'status_code' => $response['status_code'] ?? 200,
            'message' => $response['message'] ?? null,
            'data' => $data,
            'raw' => $raw,
        ];
    }

    public function ambilFeatureImportance(): array
    {
        $response = $this->getRequest($this->featureImportanceEndpoint, 'Ambil feature importance Flask');

        if (!($response['success'] ?? false)) {
            return $response;
        }

        $raw = $response['raw'] ?? [];
        $data = $this->extractByKeys($raw, [
            'feature_importance',
            'importance',
            'features',
        ]);

        return [
            'success' => true,
            'status_code' => $response['status_code'] ?? 200,
            'message' => $response['message'] ?? null,
            'data' => $data,
            'raw' => $raw,
        ];
    }

    public function ambilEvaluation(): array
    {
        $response = $this->getRequest($this->evaluationEndpoint, 'Ambil evaluation Flask');

        if (!($response['success'] ?? false)) {
            return $response;
        }

        $raw = $response['raw'] ?? [];
        $data = $this->extractByKeys($raw, [
            'evaluation',
            'evaluasi',
            'metrics',
            'rf_final_metrics',
        ]);

        return [
            'success' => true,
            'status_code' => $response['status_code'] ?? 200,
            'message' => $response['message'] ?? null,
            'data' => $data,
            'raw' => $raw,
        ];
    }

    public function prediksiRekomendasi(array $input): array
    {
        $payload = $this->normalizePredictPayload($input);

        return $this->postRequest(
            $this->predictEndpoint,
            $payload,
            'Prediksi rekomendasi Flask'
        );
    }
}