<?php

namespace App\Http\Controllers;

use App\Models\HasilPrediksi;
use App\Models\User;
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

        $baseQuery = HasilPrediksi::query();

        $dbStats = [
            'total_prediksi' => (clone $baseQuery)->count(),
            'total_kuliah' => (clone $baseQuery)->where('prediksi_rf', 1)->count(),
            'total_tidak' => (clone $baseQuery)->where('prediksi_rf', 0)->count(),
            'total_knn_dijalankan' => (clone $baseQuery)->where('knn_dijalankan', true)->count(),
            'prediksi_terakhir' => (clone $baseQuery)->latest()->limit(5)->get(),
        ];

        return view('admin.dashboard', compact(
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

        return view('admin.input-siswa', compact(
            'flaskOnline',
            'jurusanList'
        ));
    }

    public function prosesPrediksi(Request $request)
    {
        $validated = $request->validate([
            'nisn' => ['nullable', 'string', 'max:30'],
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

        $hasilFlask = $this->flaskService->prediksiRekomendasi($validated);

        if (!($hasilFlask['success'] ?? false)) {
            Log::warning('Prediksi Flask admin gagal.', [
                'user_id' => $user?->id,
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
            'user_id' => $user?->id,
            'upload_batch_id' => null,

            'nisn' => $validated['nisn'] ?? null,
            'nama_siswa' => $validated['nama_siswa'] ?? null,

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
            ->route('admin.hasil.prediksi.detail', $hasilPrediksi->id)
            ->with('success', 'Prediksi berhasil diproses.');
    }

    public function hasilPrediksi(Request $request)
    {
        $query = HasilPrediksi::query();

        if ($request->filled('jurusan')) {
            $query->where('jurusan_smk', $request->jurusan);
        }

        if ($request->filled('status')) {
            $query->where('prediksi_rf', $request->status);
        }

        $data = $query->latest()->get();

        $jurusanList = HasilPrediksi::query()
            ->select('jurusan_smk')
            ->whereNotNull('jurusan_smk')
            ->distinct()
            ->orderBy('jurusan_smk')
            ->pluck('jurusan_smk');

        return view('admin.hasil-prediksi', compact(
            'data',
            'jurusanList'
        ));
    }

    public function hasilDetail($id)
    {
        $detail = HasilPrediksi::findOrFail($id);

        return view('admin.hasil-prediksi', compact('detail'));
    }

    public function infoModel()
    {
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

    /*Manajemen Akun*/
    public function akun(Request $request)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Akses hanya untuk Administrator.');
        }

        $search = trim((string) $request->get('search', ''));
        $role = $request->get('role');

        $query = User::query()
            ->whereIn('role', ['admin', 'guru_bk', 'siswa']);

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('nisn', 'like', '%' . $search . '%');
            });
        }

        if (in_array($role, ['admin', 'guru_bk', 'siswa'], true)) {
            $query->where('role', $role);
        }

        $users = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'total_akun' => User::count(),
            'total_admin' => User::where('role', 'admin')->count(),
            'total_guru_bk' => User::where('role', 'guru_bk')->count(),
            'total_siswa' => User::where('role', 'siswa')->count(),
        ];

        return view('admin.akun', compact(
            'users',
            'stats',
            'search',
            'role'
        ));
    }

    public function simpanAkun(Request $request)
    {
        if (Auth::user()?->role !== 'admin') {
            abort(403, 'Akses hanya untuk Administrator.');
        }

        $request->merge([
            'nisn' => preg_replace('/\s+/', '', trim((string) $request->input('nisn'))),
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:guru_bk,siswa'],
            'name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'string', 'max:30', 'unique:users,nisn'],
            'kelas' => ['nullable', 'integer', 'min:10', 'max:13'],
            'password' => ['nullable', 'required_if:role,guru_bk', 'string', 'min:6', 'confirmed'],
        ], [
            'role.required' => 'Jenis akun wajib dipilih.',
            'role.in' => 'Jenis akun tidak valid.',

            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 255 karakter.',

            'nisn.required' => 'NISN / Username wajib diisi.',
            'nisn.unique' => 'NISN / Username sudah terdaftar.',
            'nisn.max' => 'NISN / Username maksimal 30 karakter.',

            'kelas.integer' => 'Kelas harus berupa angka.',
            'kelas.min' => 'Kelas minimal 10.',
            'kelas.max' => 'Kelas maksimal 13.',

            'password.required_if' => 'Password wajib diisi untuk akun Guru BK.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
        ]);

        $role = $validated['role'];

        $password = $validated['password'] ?? null;

        if ($role === 'siswa' && empty($password)) {
            $password = $validated['nisn'];
        }

        User::create([
            'nisn' => $validated['nisn'],
            'name' => $validated['name'],
            'password' => $password,
            'role' => $role,
            'kelas' => $role === 'siswa' ? ($validated['kelas'] ?? 12) : null,
        ]);

        $roleLabel = $role === 'guru_bk' ? 'Guru BK' : 'Siswa';

        return redirect()
            ->route('admin.akun')
            ->with('success', 'Akun ' . $roleLabel . ' berhasil dibuat.');
    }

    public function prosesUploadAlumni(Request $request)
    {
        return back()->with(
            'info',
            'Fitur upload alumni belum dihubungkan karena API Flask saat ini belum menyediakan endpoint upload alumni.'
        );
    }
}