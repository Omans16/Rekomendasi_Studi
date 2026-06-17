<?php

namespace App\Http\Controllers;

use App\Models\HasilPrediksi;
use App\Services\FlaskRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SiswaController extends Controller
{
    protected FlaskRecommendationService $flaskService;

    public function __construct(FlaskRecommendationService $flaskService)
    {
        $this->flaskService = $flaskService;
    }

    /*
    |--------------------------------------------------------------------------
    | Query Hasil Prediksi Milik Siswa Login
    |--------------------------------------------------------------------------
    */
    private function hasilPrediksiQuery()
    {
        $user = Auth::user();

        return HasilPrediksi::query()
            ->where(function ($query) use ($user) {
                $query->where('user_id', $user->id);

                if (!empty($user->nisn)) {
                    $query->orWhere('nisn', $user->nisn);
                }
            });
    }

    /*
    |--------------------------------------------------------------------------
    | Dashboard Siswa
    |--------------------------------------------------------------------------
    */
    public function dashboard()
    {
        $health = $this->flaskService->healthCheck();
        $flaskOnline = $health['success'] ?? false;

        $dashboardStatsResponse = method_exists($this->flaskService, 'ambilDashboardStats')
            ? $this->flaskService->ambilDashboardStats()
            : ['data' => []];

        $flaskStats = $dashboardStatsResponse['data'] ?? [];

        if (empty($flaskStats)) {
            $infoModelResponse = $this->flaskService->ambilInfoModel();
            $infoModel = $infoModelResponse['data'] ?? [];

            $flaskStats = [
                'total_alumni' => $infoModel['jumlah_data_alumni_basis'] ?? null,
                'jumlah_data_alumni_basis' => $infoModel['jumlah_data_alumni_basis'] ?? null,
                'total_universitas' => null,
                'total_program_studi' => null,
                'alumni_per_jurusan' => [],
                'top_universitas' => [],
                'top_program_studi' => [],
            ];
        }

        $flaskStats = array_merge([
            'total_alumni' => null,
            'jumlah_data_alumni_basis' => null,
            'total_universitas' => null,
            'total_program_studi' => null,
            'alumni_per_jurusan' => [],
            'top_universitas' => [],
            'top_program_studi' => [],
        ], $flaskStats);

        $baseQuery = $this->hasilPrediksiQuery();

        $dbStats = [
            'total_prediksi' => (clone $baseQuery)->count(),
            'total_kuliah' => (clone $baseQuery)->where('prediksi_rf', 1)->count(),
            'total_tidak' => (clone $baseQuery)->where('prediksi_rf', 0)->count(),
            'total_knn_dijalankan' => (clone $baseQuery)->where('knn_dijalankan', true)->count(),
            'prediksi_terakhir' => (clone $baseQuery)->latest()->limit(5)->get(),
        ];

        return view('siswa.dashboard', compact(
            'flaskOnline',
            'flaskStats',
            'dbStats'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Form Input Prediksi Siswa
    |--------------------------------------------------------------------------
    */
    public function inputSiswa()
    {
        $health = $this->flaskService->healthCheck();
        $flaskOnline = $health['success'] ?? false;

        $jurusanList = $this->flaskService->ambilDaftarJurusan();

        return view('siswa.input-siswa', compact(
            'flaskOnline',
            'jurusanList'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Proses Prediksi Siswa
    |--------------------------------------------------------------------------
    */
    public function prosesPrediksi(Request $request)
    {
        $validated = $request->validate([
            'jurusan_smk' => ['required', 'string', 'max:255'],
            'rata_pai' => ['required', 'numeric', 'min:0', 'max:100'],
            'rata_ppkn' => ['required', 'numeric', 'min:0', 'max:100'],
            'rata_ind' => ['required', 'numeric', 'min:0', 'max:100'],
            'rata_mtk' => ['required', 'numeric', 'min:0', 'max:100'],
            'rata_ing' => ['required', 'numeric', 'min:0', 'max:100'],
            'ukk' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [
            'jurusan_smk.required' => 'Jurusan SMK wajib dipilih.',
            'rata_pai.required' => 'Nilai rata-rata PAI wajib diisi.',
            'rata_ppkn.required' => 'Nilai rata-rata PPKn wajib diisi.',
            'rata_ind.required' => 'Nilai rata-rata Bahasa Indonesia wajib diisi.',
            'rata_mtk.required' => 'Nilai rata-rata Matematika wajib diisi.',
            'rata_ing.required' => 'Nilai rata-rata Bahasa Inggris wajib diisi.',
            'ukk.required' => 'Nilai UKK wajib diisi.',
            '*.numeric' => 'Nilai harus berupa angka.',
            '*.min' => 'Nilai minimal adalah 0.',
            '*.max' => 'Nilai maksimal adalah 100.',
        ]);

        $user = Auth::user();

        $validated['nisn'] = $user->nisn;
        $validated['nama_siswa'] = $user->name;

        $hasilFlask = $this->flaskService->prediksiRekomendasi($validated);

        if (!($hasilFlask['success'] ?? false)) {
            Log::warning('Prediksi Flask siswa gagal.', [
                'user_id' => $user->id,
                'nisn' => $user->nisn,
                'input' => $validated,
                'response' => $hasilFlask,
            ]);

            return back()
                ->withInput()
                ->with('error', $hasilFlask['message'] ?? 'Prediksi gagal diproses.');
        }

        $prediksiRf = is_array($hasilFlask['prediksi_rf'] ?? null)
            ? $hasilFlask['prediksi_rf']
            : [];

        $inputModel = is_array($hasilFlask['input_model'] ?? null)
            ? $hasilFlask['input_model']
            : [];

        $profilMap = collect($hasilFlask['profil_siswa'] ?? [])
            ->mapWithKeys(function ($item) {
                return [
                    $item['atribut'] ?? '' => $item['nilai'] ?? null,
                ];
            });

        $prediksi = $hasilFlask['prediksi']
            ?? $prediksiRf['prediksi']
            ?? null;

        $statusRf = $hasilFlask['status_rf']
            ?? $prediksiRf['status']
            ?? null;

        $probabilitas = $hasilFlask['probabilitas_studi_lanjut']
            ?? $prediksiRf['probabilitas_studi_lanjut']
            ?? null;

        $threshold = $hasilFlask['threshold']
            ?? $prediksiRf['threshold']
            ?? null;

        $knnDijalankan = $hasilFlask['knn_dijalankan']
            ?? $hasilFlask['lanjut_ke_knn']
            ?? $prediksiRf['lanjut_ke_knn']
            ?? false;

        $hasilPrediksi = HasilPrediksi::create([
            'user_id' => $user->id,
            'upload_batch_id' => null,

            'nisn' => $validated['nisn'],
            'nama_siswa' => $validated['nama_siswa'],

            'jurusan_smk' => $validated['jurusan_smk'],
            'jurusan_smk_lengkap' => $validated['jurusan_smk'],

            'rata_pai' => $validated['rata_pai'],
            'rata_ppkn' => $validated['rata_ppkn'],
            'rata_ind' => $validated['rata_ind'],
            'rata_mtk' => $validated['rata_mtk'],
            'rata_ing' => $validated['rata_ing'],
            'ukk' => $validated['ukk'],

            'nilai_max' => $inputModel['nilai_max'] ?? $profilMap['Nilai Maksimum'] ?? null,
            'nilai_min' => $inputModel['nilai_min'] ?? $profilMap['Nilai Minimum'] ?? null,
            'nilai_std' => $inputModel['std_nilai'] ?? $profilMap['Standar Deviasi Nilai'] ?? null,

            'prediksi_rf' => $prediksi,
            'status_rf' => $statusRf,
            'probabilitas_studi_lanjut' => $probabilitas,
            'kategori_probabilitas' => $hasilFlask['kategori_probabilitas'] ?? null,
            'threshold_rf' => $threshold,
            'knn_dijalankan' => (bool) $knnDijalankan,

            'profil_siswa' => $hasilFlask['profil_siswa'] ?? [],
            'alumni_terdekat' => $hasilFlask['alumni_terdekat'] ?? [],
            'narasi_rekomendasi' => $hasilFlask['narasi_rekomendasi'] ?? null,
            'kualitas_rekomendasi' => $hasilFlask['kualitas_rekomendasi'] ?? null,
            'rekomendasi_final' => $hasilFlask['rekomendasi_final'] ?? [],
            'pesan' => $hasilFlask['pesan'] ?? null,
            'response_flask' => $hasilFlask,

            'sumber' => 'manual',
            'error_message' => null,
        ]);

        return redirect()
            ->route('siswa.hasil.prediksi.detail', $hasilPrediksi->id)
            ->with('success', 'Prediksi berhasil diproses.');
    }

    /*
    |--------------------------------------------------------------------------
    | Riwayat Prediksi Siswa
    |--------------------------------------------------------------------------
    */
    public function hasilPrediksi(Request $request)
    {
        $query = $this->hasilPrediksiQuery();

        if ($request->filled('jurusan')) {
            $query->where('jurusan_smk', $request->jurusan);
        }

        if ($request->filled('status')) {
            $query->where('prediksi_rf', $request->status);
        }

        $data = $query->latest()->get();

        $jurusanList = $this->hasilPrediksiQuery()
            ->select('jurusan_smk')
            ->whereNotNull('jurusan_smk')
            ->distinct()
            ->orderBy('jurusan_smk')
            ->pluck('jurusan_smk');

        return view('siswa.hasil-prediksi', compact(
            'data',
            'jurusanList'
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Detail Prediksi Siswa
    |--------------------------------------------------------------------------
    */
    public function hasilDetail($id)
    {
        $detail = $this->hasilPrediksiQuery()->findOrFail($id);

        return view('siswa.hasil-prediksi', compact('detail'));
    }
}