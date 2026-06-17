<?php

namespace App\Http\Controllers;

use App\Models\HasilPrediksi;
use App\Services\FlaskRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected FlaskRecommendationService $flaskService;

    public function __construct(FlaskRecommendationService $flaskService)
    {
        $this->flaskService = $flaskService;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: cek apakah user login adalah siswa
    |--------------------------------------------------------------------------
    */
    private function isSiswa(): bool
    {
        return Auth::check() && Auth::user()->role === 'siswa';
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: cek apakah request sedang berada di route siswa
    |--------------------------------------------------------------------------
    */
    private function isSiswaRoute(): bool
    {
        return request()->routeIs('siswa.*') || request()->is('siswa/*');
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: path view otomatis
    |--------------------------------------------------------------------------
    | /admin/...  => resources/views/admin/...
    | /siswa/...  => resources/views/siswa/...
    */
    private function viewPath(string $view): string
    {
        if ($this->isSiswaRoute()) {
            return 'siswa.' . $view;
        }

        return 'admin.' . $view;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: nama route otomatis
    |--------------------------------------------------------------------------
    | admin.hasil.prediksi.detail
    | siswa.hasil.prediksi.detail
    */
    private function routeName(string $name): string
    {
        if ($this->isSiswaRoute()) {
            return 'siswa.' . $name;
        }

        return 'admin.' . $name;
    }

    /*
    |--------------------------------------------------------------------------
    | Helper: query hasil prediksi sesuai hak akses
    |--------------------------------------------------------------------------
    | - siswa      : hanya data miliknya sendiri
    | - admin/BK   : semua data
    */
    private function hasilPrediksiQuery()
    {
        $query = HasilPrediksi::query();

        if ($this->isSiswa()) {
            $query->where('user_id', Auth::id());
        }

        return $query;
    }

    public function dashboard()
    {
        $health = $this->flaskService->healthCheck();
        $flaskOnline = $health['success'] ?? false;

        /*
            Ambil statistik dashboard dari Flask endpoint /dashboard-stats.
            Endpoint ini harus mengembalikan:
            - total_alumni
            - total_universitas
            - total_program_studi
            - alumni_per_jurusan
            - top_universitas
            - top_program_studi
        */
        $dashboardStatsResponse = method_exists($this->flaskService, 'ambilDashboardStats')
            ? $this->flaskService->ambilDashboardStats()
            : ['data' => []];

        $flaskStats = $dashboardStatsResponse['data'] ?? [];

        /*
            Fallback jika /dashboard-stats belum aktif,
            minimal total alumni tetap diambil dari /info-model.
        */
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

        /*
            Pastikan semua key tetap tersedia agar dashboard.blade.php tidak error.
        */
        $flaskStats = array_merge([
            'total_alumni' => null,
            'jumlah_data_alumni_basis' => null,
            'total_universitas' => null,
            'total_program_studi' => null,
            'alumni_per_jurusan' => [],
            'top_universitas' => [],
            'top_program_studi' => [],
        ], $flaskStats);

        /*
            Statistik database:
            - siswa hanya melihat statistik prediksi miliknya sendiri
            - admin/guru BK melihat semua statistik
        */
        $baseQuery = $this->hasilPrediksiQuery();

        $dbStats = [
            'total_prediksi' => (clone $baseQuery)->count(),
            'total_kuliah' => (clone $baseQuery)->where('prediksi_rf', 1)->count(),
            'total_tidak' => (clone $baseQuery)->where('prediksi_rf', 0)->count(),
            'total_knn_dijalankan' => (clone $baseQuery)->where('knn_dijalankan', true)->count(),
            'prediksi_terakhir' => (clone $baseQuery)->latest()->limit(5)->get(),
        ];

        return view($this->viewPath('dashboard'), compact(
            'flaskOnline',
            'flaskStats',
            'dbStats'
        ));
    }

    public function inputSiswa()
    {
        $health = $this->flaskService->healthCheck();
        $flaskOnline = $health['success'] ?? false;

        $jurusanList = $this->flaskService->ambilDaftarJurusan();

        return view($this->viewPath('input-siswa'), compact(
            'flaskOnline',
            'jurusanList'
        ));
    }

    public function prosesPrediksi(Request $request)
    {
        $validated = $request->validate([
            'nama_siswa' => ['nullable', 'string', 'max:255'],
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

        /*
            Jika role siswa, nama siswa otomatis diambil dari akun login.
            Ini mencegah siswa menginput hasil atas nama orang lain.
        */
        if ($user && $user->role === 'siswa') {
            $validated['nama_siswa'] = $user->name;
        }

        $hasilFlask = $this->flaskService->prediksiRekomendasi($validated);

        if (!($hasilFlask['success'] ?? false)) {
            Log::warning('Prediksi Flask gagal.', [
                'user_id' => $user?->id,
                'nisn' => $user?->nisn,
                'input' => $validated,
                'response' => $hasilFlask,
            ]);

            return back()
                ->withInput()
                ->with('error', $hasilFlask['message'] ?? 'Prediksi gagal diproses.');
        }

        $prediksiRf = $hasilFlask['prediksi_rf'] ?? [];

        $profilMap = collect($hasilFlask['profil_siswa'] ?? [])
            ->mapWithKeys(function ($item) {
                return [
                    $item['atribut'] ?? '' => $item['nilai'] ?? null
                ];
            });

        $hasilPrediksi = HasilPrediksi::create([
            /*
                Data pemilik hasil prediksi.
                Wajib ada kolom user_id dan nisn di tabel hasil_prediksis.
            */
            'user_id' => $user?->id,
            'nisn' => $user?->nisn,
            'nama_siswa' => $validated['nama_siswa'] ?? $user?->name,

            'jurusan_smk' => $validated['jurusan_smk'],
            'jurusan_smk_lengkap' => $validated['jurusan_smk'],

            'rata_pai' => $validated['rata_pai'],
            'rata_ppkn' => $validated['rata_ppkn'],
            'rata_ind' => $validated['rata_ind'],
            'rata_mtk' => $validated['rata_mtk'],
            'rata_ing' => $validated['rata_ing'],
            'ukk' => $validated['ukk'],

            'nilai_max' => $profilMap['Nilai Maksimum'] ?? null,
            'nilai_min' => $profilMap['Nilai Minimum'] ?? null,
            'nilai_std' => $profilMap['Standar Deviasi Nilai'] ?? null,

            'prediksi_rf' => $prediksiRf['prediksi'] ?? null,
            'status_rf' => $prediksiRf['status'] ?? null,
            'probabilitas_studi_lanjut' => $prediksiRf['probabilitas_studi_lanjut'] ?? null,
            'threshold_rf' => $prediksiRf['threshold'] ?? null,
            'knn_dijalankan' => $hasilFlask['knn_dijalankan'] ?? false,

            'profil_siswa' => $hasilFlask['profil_siswa'] ?? [],
            'alumni_terdekat' => $hasilFlask['alumni_terdekat'] ?? [],
            'narasi_rekomendasi' => $hasilFlask['narasi_rekomendasi'] ?? null,
            'kualitas_rekomendasi' => $hasilFlask['kualitas_rekomendasi'] ?? null,
            'rekomendasi_final' => $hasilFlask['rekomendasi_final'] ?? [],
            'pesan' => $hasilFlask['pesan'] ?? null,
            'response_flask' => $hasilFlask,
        ]);

        return redirect()
            ->route($this->routeName('hasil.prediksi.detail'), $hasilPrediksi->id)
            ->with('success', 'Prediksi berhasil diproses.');
    }

    public function hasilPrediksi(Request $request)
    {
        /*
            siswa hanya mengambil hasil miliknya sendiri.
            admin/guru BK mengambil semua hasil.
        */
        $query = $this->hasilPrediksiQuery();

        if ($request->filled('jurusan')) {
            $query->where('jurusan_smk', $request->jurusan);
        }

        if ($request->filled('status')) {
            $query->where('prediksi_rf', $request->status);
        }

        $data = $query->latest()->get();

        /*
            Filter jurusan juga mengikuti hak akses.
            Jadi siswa hanya melihat daftar jurusan dari riwayat miliknya.
        */
        $jurusanList = $this->hasilPrediksiQuery()
            ->select('jurusan_smk')
            ->whereNotNull('jurusan_smk')
            ->distinct()
            ->orderBy('jurusan_smk')
            ->pluck('jurusan_smk');

        return view($this->viewPath('hasil-prediksi'), compact(
            'data',
            'jurusanList'
        ));
    }

    public function hasilDetail($id)
    {
        /*
            findOrFail tetap aman karena query sudah dibatasi.
            Jika siswa mencoba membuka id milik siswa lain, hasilnya 404.
        */
        $detail = $this->hasilPrediksiQuery()->findOrFail($id);

        return view($this->viewPath('hasil-prediksi'), compact('detail'));
    }

    public function infoModel()
    {
        /*
            Route info-model hanya untuk admin/guru BK.
        */

        $health = $this->flaskService->healthCheck();
        $flaskOnline = $health['success'] ?? false;

        $infoModelResponse = $this->flaskService->ambilInfoModel();
        $featureImportanceResponse = $this->flaskService->ambilFeatureImportance();
        $evaluationResponse = $this->flaskService->ambilEvaluation();

        $infoModel = $infoModelResponse['data'] ?? [];
        $featureImportance = $featureImportanceResponse['data'] ?? [];

        $evaluasi = $evaluationResponse['data'] ?? ($infoModel['rf_final_metrics'] ?? []);

        $dbStats = [
            'total_prediksi' => HasilPrediksi::count(),
            'total_kuliah' => HasilPrediksi::where('prediksi_rf', 1)->count(),
            'total_tidak' => HasilPrediksi::where('prediksi_rf', 0)->count(),
        ];

        $jurusanMapping = [];

        foreach ($this->flaskService->ambilDaftarJurusan() as $jurusan) {
            if (is_array($jurusan)) {
                $nama = $jurusan['nama_lengkap'] ?? $jurusan['singkatan'] ?? null;

                if ($nama) {
                    $jurusanMapping[$nama] = $nama;
                }
            } else {
                $jurusanMapping[$jurusan] = $jurusan;
            }
        }

        $dashboardStatsResponse = method_exists($this->flaskService, 'ambilDashboardStats')
            ? $this->flaskService->ambilDashboardStats()
            : ['data' => []];

        $dashboardStats = $dashboardStatsResponse['data'] ?? [];

        $alumniPerJurusan = $dashboardStats['alumni_per_jurusan'] ?? [];
        $dashboard = $dashboardStats;

        return view('admin.info-model', compact(
            'infoModel',
            'featureImportance',
            'evaluasi',
            'dbStats',
            'flaskOnline',
            'jurusanMapping',
            'alumniPerJurusan',
            'dashboard'
        ));
    }

    public function prosesUploadAlumni(Request $request)
    {
        /*
            Route upload alumni hanya untuk admin/guru BK.
        */

        return back()->with(
            'info',
            'Fitur upload alumni belum dihubungkan karena API Flask saat ini belum menyediakan endpoint upload alumni.'
        );
    }
}