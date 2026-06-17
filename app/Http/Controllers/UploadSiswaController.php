<?php

namespace App\Http\Controllers;

use App\Models\HasilPrediksi;
use App\Models\UploadSiswaBatch;
use App\Models\UploadSiswaRow;
use App\Models\User;
use App\Services\FlaskRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class UploadSiswaController extends Controller
{
    public function index(FlaskRecommendationService $flask)
    {
        $flaskOnline = $flask->isOnline();

        $lastBatch = UploadSiswaBatch::with('uploader')
            ->latest()
            ->first();

        $recentBatches = UploadSiswaBatch::with('uploader')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.upload-siswa', compact(
            'flaskOnline',
            'lastBatch',
            'recentBatches'
        ));
    }

    public function store(Request $request, FlaskRecommendationService $flask)
    {
        set_time_limit(600);

        $request->validate([
            'file_siswa' => ['required', 'file', 'mimes:xlsx,xls,csv,txt', 'max:10240'],
        ], [
            'file_siswa.required' => 'File data siswa wajib diupload.',
            'file_siswa.mimes' => 'Format file harus .xlsx, .xls, .csv, atau .txt.',
            'file_siswa.max' => 'Ukuran file maksimal 10MB.',
        ]);

        if (!$flask->isOnline()) {
            return back()->with('error', 'Layanan Flask API sedang tidak aktif. Upload belum dapat diproses.');
        }

        $file = $request->file('file_siswa');
        $extension = strtolower($file->getClientOriginalExtension());

        $storedPath = $file->storeAs(
            'upload-siswa',
            now()->format('Ymd_His') . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName())
        );

        $batch = UploadSiswaBatch::create([
            'uploaded_by' => Auth::id(),
            'original_filename' => $file->getClientOriginalName(),
            'stored_filename' => $storedPath,
            'status' => 'processing',
        ]);

        try {
            $rows = $this->readSpreadsheetRows($file->getRealPath(), $extension);

            if (count($rows) === 0) {
                $batch->update([
                    'status' => 'failed',
                    'summary' => [
                        'message' => 'File kosong atau tidak memiliki data.',
                    ],
                ]);

                return back()->with('error', 'File kosong atau tidak memiliki data.');
            }

            $summary = [
                'total_rows' => 0,
                'valid_rows' => 0,
                'failed_rows' => 0,
                'linked_user_count' => 0,
                'unlinked_user_count' => 0,
                'prediksi_success_count' => 0,
                'rekomendasi_success_count' => 0,
            ];

            foreach ($rows as $index => $rawRow) {
                $rowNumber = $index + 2;
                $summary['total_rows']++;

                $payload = $this->normalizePayload($rawRow);

                $uploadRow = UploadSiswaRow::create([
                    'upload_siswa_batch_id' => $batch->id,
                    'row_number' => $rowNumber,
                    'nisn' => $payload['nisn'] ?? null,
                    'nama_siswa' => $payload['nama_siswa'] ?? null,
                    'jurusan_smk' => $payload['jurusan_smk'] ?? null,
                    'payload' => $payload,
                    'status' => 'pending',
                ]);

                $validation = $this->validatePayload($payload);

                if (!$validation['valid']) {
                    $summary['failed_rows']++;

                    $uploadRow->update([
                        'status' => 'failed',
                        'message' => $validation['message'],
                    ]);

                    continue;
                }

                $user = User::where('nisn', $payload['nisn'])->first();

                if ($user) {
                    $summary['linked_user_count']++;
                } else {
                    $summary['unlinked_user_count']++;
                }

                try {
                    $response = $flask->predict($payload);

                    $stats = $this->calculateNilaiStats($payload);
                    $parsed = $this->parseFlaskResponse($response);

                    $hasil = HasilPrediksi::create([
                        'user_id' => $user?->id,
                        'upload_batch_id' => $batch->id,

                        'nisn' => $payload['nisn'],
                        'nama_siswa' => $payload['nama_siswa'],
                        'jurusan_smk' => $payload['jurusan_smk'],
                        'jurusan_smk_lengkap' => $payload['jurusan_smk'],

                        'rata_pai' => $payload['rata_pai'],
                        'rata_ppkn' => $payload['rata_ppkn'],
                        'rata_ind' => $payload['rata_ind'],
                        'rata_mtk' => $payload['rata_mtk'],
                        'rata_ing' => $payload['rata_ing'],
                        'ukk' => $payload['ukk'],

                        'nilai_max' => $parsed['input_model']['nilai_max'] ?? $stats['nilai_max'],
                        'nilai_min' => $parsed['input_model']['nilai_min'] ?? $stats['nilai_min'],
                        'nilai_std' => $parsed['input_model']['std_nilai'] ?? $stats['nilai_std'],

                        'prediksi_rf' => $parsed['prediksi_rf'],
                        'status_rf' => $parsed['status_rf'],
                        'probabilitas_studi_lanjut' => $parsed['probabilitas_studi_lanjut'],
                        'kategori_probabilitas' => $parsed['kategori_probabilitas'],
                        'threshold_rf' => $parsed['threshold_rf'],
                        'knn_dijalankan' => $parsed['knn_dijalankan'],

                        'pesan' => $response['pesan'] ?? null,
                        'narasi_rekomendasi' => $response['narasi_rekomendasi'] ?? null,

                        'profil_siswa' => $response['profil_siswa'] ?? $this->buildProfilFallback($payload, $stats),
                        'alumni_terdekat' => $response['alumni_terdekat'] ?? [],
                        'rekomendasi_final' => $response['rekomendasi_final'] ?? [],
                        'kualitas_rekomendasi' => $response['kualitas_rekomendasi'] ?? null,
                        'response_flask' => $response,

                        'sumber' => 'upload_siswa',
                        'error_message' => null,
                    ]);

                    $summary['valid_rows']++;
                    $summary['prediksi_success_count']++;

                    if ($parsed['prediksi_rf'] === 1 && !empty($response['rekomendasi_final'] ?? [])) {
                        $summary['rekomendasi_success_count']++;
                    }

                    $uploadRow->update([
                        'user_id' => $user?->id,
                        'hasil_prediksi_id' => $hasil->id,
                        'status' => 'success',
                        'message' => $user
                            ? 'Berhasil diproses dan terhubung ke akun siswa.'
                            : 'Berhasil diproses. Akun siswa belum tersedia, hasil disimpan berdasarkan NISN.',
                        'response' => $response,
                    ]);
                } catch (\Throwable $e) {
                    $summary['failed_rows']++;

                    $uploadRow->update([
                        'status' => 'failed',
                        'message' => $e->getMessage(),
                    ]);
                }
            }

            $batch->update([
                'total_rows' => $summary['total_rows'],
                'valid_rows' => $summary['valid_rows'],
                'failed_rows' => $summary['failed_rows'],
                'linked_user_count' => $summary['linked_user_count'],
                'unlinked_user_count' => $summary['unlinked_user_count'],
                'prediksi_success_count' => $summary['prediksi_success_count'],
                'rekomendasi_success_count' => $summary['rekomendasi_success_count'],
                'status' => $summary['failed_rows'] > 0 ? 'completed_with_errors' : 'completed',
                'summary' => $summary,
            ]);

            return redirect()
                ->route('admin.upload.siswa')
                ->with('success', 'Upload data siswa selesai diproses.')
                ->with('upload_summary', $summary);
        } catch (\Throwable $e) {
            $batch->update([
                'status' => 'failed',
                'summary' => [
                    'message' => $e->getMessage(),
                ],
            ]);

            return back()->with('error', 'Upload gagal diproses: ' . $e->getMessage());
        }
    }

    private function readSpreadsheetRows(string $path, string $extension): array
    {
        if (in_array($extension, ['csv', 'txt'], true)) {
            $reader = new Csv();
            $reader->setDelimiter($this->detectCsvDelimiter($path));
            $reader->setEnclosure('"');

            $spreadsheet = $reader->load($path);
        } else {
            $spreadsheet = IOFactory::load($path);
        }

        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestDataRow();
        $highestColumn = $sheet->getHighestDataColumn();
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

        if ($highestRow < 2) {
            return [];
        }

        $headers = [];

        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $address = Coordinate::stringFromColumnIndex($col) . '1';
            $headers[$col] = $this->normalizeHeader($sheet->getCell($address)->getFormattedValue());
        }

        $rows = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $assoc = [];
            $isEmpty = true;

            for ($col = 1; $col <= $highestColumnIndex; $col++) {
                $header = $headers[$col] ?? null;

                if (!$header) {
                    continue;
                }

                $address = Coordinate::stringFromColumnIndex($col) . $row;
                $value = trim((string) $sheet->getCell($address)->getFormattedValue());

                if ($value !== '') {
                    $isEmpty = false;
                }

                $assoc[$header] = $value;
            }

            if (!$isEmpty) {
                $rows[] = $assoc;
            }
        }

        return $rows;
    }

    private function detectCsvDelimiter(string $path): string
    {
        $line = '';

        $handle = fopen($path, 'r');

        if ($handle) {
            $line = fgets($handle) ?: '';
            fclose($handle);
        }

        $comma = substr_count($line, ',');
        $semicolon = substr_count($line, ';');

        return $semicolon > $comma ? ';' : ',';
    }

    private function normalizeHeader(?string $header): string
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);

        return trim($header, '_');
    }

    private function normalizePayload(array $row): array
    {
        $alias = [
            'nisn' => ['nisn', 'no_nisn', 'nis'],
            'nama_siswa' => ['nama_siswa', 'nama', 'nama_lengkap', 'nama_lengkap_siswa'],
            'jurusan_smk' => ['jurusan_smk', 'jurusan', 'kompetensi_keahlian', 'program_keahlian'],

            'rata_pai' => ['rata_pai', 'pai', 'nilai_pai', 'pendidikan_agama_islam'],
            'rata_ppkn' => ['rata_ppkn', 'ppkn', 'pkn', 'nilai_ppkn', 'nilai_pkn'],
            'rata_ind' => ['rata_ind', 'bahasa_indonesia', 'nilai_bahasa_indonesia', 'nilai_indonesia', 'bind'],
            'rata_mtk' => ['rata_mtk', 'matematika', 'mtk', 'nilai_matematika', 'nilai_mtk'],
            'rata_ing' => ['rata_ing', 'bahasa_inggris', 'nilai_bahasa_inggris', 'nilai_inggris', 'bing'],
            'ukk' => ['ukk', 'nilai_ukk', 'uji_kompetensi_keahlian'],
        ];

        $payload = [];

        foreach ($alias as $target => $candidates) {
            $payload[$target] = null;

            foreach ($candidates as $candidate) {
                if (array_key_exists($candidate, $row)) {
                    $payload[$target] = $row[$candidate];
                    break;
                }
            }
        }

        $payload['nisn'] = $this->cleanNisn($payload['nisn']);
        $payload['nama_siswa'] = $this->cleanText($payload['nama_siswa']);
        $payload['jurusan_smk'] = $this->cleanText($payload['jurusan_smk']);

        foreach (['rata_pai', 'rata_ppkn', 'rata_ind', 'rata_mtk', 'rata_ing', 'ukk'] as $field) {
            $payload[$field] = $this->parseScore($payload[$field]);
        }

        return $payload;
    }

    private function validatePayload(array $payload): array
    {
        foreach (['nisn', 'nama_siswa', 'jurusan_smk'] as $field) {
            if (empty($payload[$field])) {
                return [
                    'valid' => false,
                    'message' => 'Kolom ' . $field . ' wajib diisi.',
                ];
            }
        }

        foreach (['rata_pai', 'rata_ppkn', 'rata_ind', 'rata_mtk', 'rata_ing', 'ukk'] as $field) {
            if ($payload[$field] === null) {
                return [
                    'valid' => false,
                    'message' => 'Kolom ' . $field . ' wajib berupa angka.',
                ];
            }

            if ($payload[$field] < 0 || $payload[$field] > 100) {
                return [
                    'valid' => false,
                    'message' => 'Kolom ' . $field . ' harus berada pada rentang 0 sampai 100.',
                ];
            }
        }

        return [
            'valid' => true,
            'message' => 'Valid.',
        ];
    }

    private function cleanText($value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function cleanNisn($value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = preg_replace('/\.0$/', '', $value);
        $value = preg_replace('/\s+/', '', $value);

        return $value;
    }

    private function parseScore($value): ?float
    {
        $value = trim((string) $value);
        $value = str_replace(',', '.', $value);

        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return round((float) $value, 2);
    }

    private function calculateNilaiStats(array $payload): array
    {
        $values = [
            (float) $payload['rata_pai'],
            (float) $payload['rata_ppkn'],
            (float) $payload['rata_ind'],
            (float) $payload['rata_mtk'],
            (float) $payload['rata_ing'],
            (float) $payload['ukk'],
        ];

        $mean = array_sum($values) / count($values);

        $variance = array_sum(array_map(function ($value) use ($mean) {
            return pow($value - $mean, 2);
        }, $values)) / count($values);

        return [
            'nilai_max' => round(max($values), 2),
            'nilai_min' => round(min($values), 2),
            'nilai_std' => round(sqrt($variance), 4),
        ];
    }

    private function parseFlaskResponse(array $response): array
    {
        $hasilRf = is_array($response['prediksi_rf'] ?? null)
            ? $response['prediksi_rf']
            : [];

        $prediksi = (int) (
            $response['prediksi']
            ?? $hasilRf['prediksi']
            ?? 0
        );

        $status = $response['status_rf']
            ?? $hasilRf['status']
            ?? ($prediksi === 1 ? 'Teridentifikasi Studi Lanjut' : 'Tidak Teridentifikasi Studi Lanjut');

        $probabilitas = (float) (
            $response['probabilitas_studi_lanjut']
            ?? $hasilRf['probabilitas_studi_lanjut']
            ?? 0
        );

        $threshold = (float) (
            $response['threshold']
            ?? $hasilRf['threshold']
            ?? 0
        );

        $knnDijalankan = (bool) (
            $response['knn_dijalankan']
            ?? $response['lanjut_ke_knn']
            ?? $hasilRf['lanjut_ke_knn']
            ?? false
        );

        return [
            'prediksi_rf' => $prediksi,
            'status_rf' => $status,
            'probabilitas_studi_lanjut' => $probabilitas,
            'kategori_probabilitas' => $response['kategori_probabilitas'] ?? null,
            'threshold_rf' => $threshold,
            'knn_dijalankan' => $knnDijalankan,
            'input_model' => is_array($response['input_model'] ?? null)
                ? $response['input_model']
                : [],
        ];
    }

    private function buildProfilFallback(array $payload, array $stats): array
    {
        return [
            ['atribut' => 'NISN', 'nilai' => $payload['nisn']],
            ['atribut' => 'Nama Siswa', 'nilai' => $payload['nama_siswa']],
            ['atribut' => 'Jurusan SMK', 'nilai' => $payload['jurusan_smk']],
            ['atribut' => 'Rata-rata PAI', 'nilai' => $payload['rata_pai']],
            ['atribut' => 'Rata-rata PPKn', 'nilai' => $payload['rata_ppkn']],
            ['atribut' => 'Rata-rata Bahasa Indonesia', 'nilai' => $payload['rata_ind']],
            ['atribut' => 'Rata-rata Matematika', 'nilai' => $payload['rata_mtk']],
            ['atribut' => 'Rata-rata Bahasa Inggris', 'nilai' => $payload['rata_ing']],
            ['atribut' => 'UKK', 'nilai' => $payload['ukk']],
            ['atribut' => 'Nilai Maksimum', 'nilai' => $stats['nilai_max']],
            ['atribut' => 'Nilai Minimum', 'nilai' => $stats['nilai_min']],
            ['atribut' => 'Standar Deviasi Nilai', 'nilai' => $stats['nilai_std']],
        ];
    }
}