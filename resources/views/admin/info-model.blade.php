{{-- PERTAHANKAN VIEWS INI JANGAN RUBAH APAPUN. AMBIL DATA DINAMIS SEPERTI INI UNTUK DITAMPILKAN DI VIEWS LAIN (INFO MODEL, PREPROCESSING) --}}
@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/info-model.css') }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Guard Data dari Controller
    |--------------------------------------------------------------------------
    | Blok ini hanya menyiapkan data agar view tidak error.
    | Tidak mengubah tampilan, struktur HTML, class, maupun design.
    */

    $infoModel        = $infoModel        ?? [];
    $dbStats          = $dbStats          ?? [];
    $flaskOnline      = $flaskOnline      ?? false;
    $jurusanMapping   = $jurusanMapping   ?? [];
    $alumniPerJurusan = $alumniPerJurusan ?? [];
    $dashboard        = $dashboard        ?? [];
    $evaluasi         = $evaluasi         ?? [];

    /*
    |--------------------------------------------------------------------------
    | Jika infoModel masih berbentuk response penuh dari Flask
    |--------------------------------------------------------------------------
    | Contoh:
    | [
    |   'success' => true,
    |   'data' => [...]
    | ]
    */
    if (isset($infoModel['data']) && is_array($infoModel['data'])) {
        $infoModel = $infoModel['data'];
    }

    /*
    |--------------------------------------------------------------------------
    | Evaluasi Random Forest
    |--------------------------------------------------------------------------
    | Mendukung beberapa kemungkinan key dari rf_final_metrics.json.
    */
    $evaluasi = !empty($evaluasi)
        ? $evaluasi
        : ($infoModel['rf_final_metrics'] ?? []);

    if (isset($evaluasi['data']) && is_array($evaluasi['data'])) {
        $evaluasi = $evaluasi['data'];
    }

    $precision = $evaluasi['precision']
        ?? $evaluasi['precision_class_1']
        ?? $evaluasi['kelas_1']['precision']
        ?? $infoModel['precision']
        ?? '—';

    $recall = $evaluasi['recall']
        ?? $evaluasi['recall_class_1']
        ?? $evaluasi['kelas_1']['recall']
        ?? $infoModel['recall']
        ?? '—';

    $f1 = $evaluasi['f1_score']
        ?? $evaluasi['f1']
        ?? $evaluasi['f1_class_1']
        ?? $evaluasi['kelas_1']['f1_score']
        ?? $infoModel['f1_score']
        ?? '—';

    $rocAuc = $evaluasi['roc_auc']
        ?? $evaluasi['roc_auc_score']
        ?? $infoModel['roc_auc']
        ?? '—';

    $prAuc = $evaluasi['pr_auc']
        ?? $evaluasi['pr_auc_score']
        ?? $infoModel['pr_auc']
        ?? '—';

    /*
    |--------------------------------------------------------------------------
    | Konfigurasi KNN dari /info-model Flask
    |--------------------------------------------------------------------------
    */
    $knn = [
        'metric'               => $infoModel['metric'] ?? 'euclidean',
        'n_neighbors'          => $infoModel['n_neighbors'] ?? '—',
        'total_alumni'         => $infoModel['jumlah_data_alumni_basis'] ?? '—',
        'top_n'                => $infoModel['top_rekomendasi'] ?? 5,
        'top_alumni'           => $infoModel['top_alumni'] ?? 3,
        'similarity_threshold' => $infoModel['threshold_similarity_knn'] ?? null,
        'w_similarity'         => $infoModel['bobot_similarity'] ?? 0.7,
        'w_frequency'          => $infoModel['bobot_frekuensi'] ?? 0.3,
    ];

    /*
    |--------------------------------------------------------------------------
    | Feature Importance
    |--------------------------------------------------------------------------
    | Mendukung format:
    | 1. Langsung list:
    |    [
    |      ['feature' => 'rata_mtk', 'importance' => 0.12]
    |    ]
    |
    | 2. Response penuh:
    |    [
    |      'success' => true,
    |      'data' => [...]
    |    ]
    */
    $featureImportanceLive = null;

    if (!empty($featureImportance) && is_array($featureImportance)) {
        if (isset($featureImportance['data']) && is_array($featureImportance['data'])) {
            $featureImportanceLive = $featureImportance['data'];
        } else {
            $featureImportanceLive = $featureImportance;
        }
    }

    $featureImportanceFallback = [
        ['rank'=>1,'feature'=>'rata_mtk', 'importance'=>0.18],
        ['rank'=>2,'feature'=>'rata_ind', 'importance'=>0.15],
        ['rank'=>3,'feature'=>'rata_ppkn','importance'=>0.14],
        ['rank'=>4,'feature'=>'rata_ing', 'importance'=>0.13],
        ['rank'=>5,'feature'=>'rata_pai', 'importance'=>0.12],
        ['rank'=>6,'feature'=>'UKK',      'importance'=>0.10],
        ['rank'=>7,'feature'=>'nilai_max','importance'=>0.09],
        ['rank'=>8,'feature'=>'std_nilai','importance'=>0.08],
    ];

    $featureImportance = $featureImportanceLive ?? $featureImportanceFallback;
    $isLiveFI = $featureImportanceLive !== null;

    $fiForChart = [];

    foreach ($featureImportance as $fi) {
        if (!is_array($fi)) {
            continue;
        }

        $featureName = $fi['feature']
            ?? $fi['fitur']
            ?? $fi['nama_fitur']
            ?? $fi['Feature']
            ?? $fi['Fitur']
            ?? null;

        $importanceValue = $fi['importance']
            ?? $fi['nilai_importance']
            ?? $fi['Importance']
            ?? $fi['nilai']
            ?? null;

        if ($featureName !== null && $importanceValue !== null && is_numeric($importanceValue)) {
            $fiForChart[$featureName] = (float) $importanceValue;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Fitur Numerik dan Fitur Turunan
    |--------------------------------------------------------------------------
    | Digunakan untuk tabel "Format Kolom yang Digunakan Model Random Forest".
    */
    $fiturNumerik = [
        [
            'nama' => 'rata_pai',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai PAI'
        ],
        [
            'nama' => 'rata_ppkn',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai PPKn'
        ],
        [
            'nama' => 'rata_ind',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai Bahasa Indonesia'
        ],
        [
            'nama' => 'rata_mtk',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai Matematika'
        ],
        [
            'nama' => 'rata_ing',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai Bahasa Inggris'
        ],
        [
            'nama' => 'UKK',
            'tipe' => 'Numerik',
            'keterangan' => 'Nilai Ujian Kompetensi Keahlian'
        ],
        [
            'nama' => 'nilai_max',
            'tipe' => 'Turunan',
            'keterangan' => 'Nilai tertinggi dari rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, dan UKK'
        ],
        [
            'nama' => 'nilai_min',
            'tipe' => 'Turunan',
            'keterangan' => 'Nilai terendah dari rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, dan UKK'
        ],
        [
            'nama' => 'std_nilai',
            'tipe' => 'Turunan',
            'keterangan' => 'Standar deviasi nilai dari rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, dan UKK'
        ],
        [
            'nama' => 'Jurusan_Smk_*',
            'tipe' => 'OHE',
            'keterangan' => 'One Hot Encoding jurusan SMK'
        ],
    ];
@endphp

<div class="page-header">
    <h2>Informasi Model</h2>
    <p>Performa dan konfigurasi Random Forest + KNN Similarity</p>
</div>

{{-- ── STAT CARDS PERFORMA ──────────────────────────────────────── --}}
<div class="three-col">
    <div class="stat-card model-metric">
        <div class="model-metric-val">{{ $precision }}</div>
        <div class="model-metric-lab">Precision</div>
    </div>
    <div class="stat-card model-metric">
        <div class="model-metric-val">{{ $recall }}</div>
        <div class="model-metric-lab">Recall</div>
    </div>
    <div class="stat-card model-metric">
        <div class="model-metric-val">{{ $f1 }}</div>
        <div class="model-metric-lab">F1-Score</div>
    </div>
    <div class="stat-card model-metric">
        <div class="model-metric-val">{{ $rocAuc }}</div>
        <div class="model-metric-lab">ROC-AUC</div>
    </div>
    <div class="stat-card model-metric">
        <div class="model-metric-val">{{ $prAuc }}</div>
        <div class="model-metric-lab">PR-AUC</div>
    </div>
    <div class="stat-card model-metric">
        <div class="model-metric-val">{{ $infoModel['threshold_random_forest'] ?? '—' }}</div>
        <div class="model-metric-lab">Threshold RF</div>
    </div>
</div>

{{-- ── KONFIGURASI RF + CBF ─────────────────────────────────────── --}}
<div class="two-col">

    {{-- RANDOM FOREST --}}
    <div class="card config-card">
        <div class="config-card-header">
            <div class="config-card-icon">RF</div>
            <div>
                <div class="card-title card-title-compact">Random Forest</div>
                <div class="config-card-subtitle">BalancedRandomForestClassifier</div>
            </div>
        </div>

        <div class="config-grid">
            <div class="config-item">
                <span class="config-key">Model</span>
                <span class="config-val">{{ $infoModel['nama_model'] ?? 'BalancedRandomForestClassifier' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Encoding</span>
                <span class="config-val">{{ $infoModel['encoding'] ?? 'One Hot Encoding (OHE)' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Imbalance</span>
                <span class="config-val">{{ $infoModel['class_imbalance'] ?? 'BalancedRandomForestClassifier' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Tuning</span>
                <span class="config-val">{{ $infoModel['tuning'] ?? 'GridSearchCV, scoring=f1' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Train/Test</span>
                <span class="config-val">80% / 20% (stratified)</span>
            </div>
            <div class="config-item">
                <span class="config-key">Cross-val</span>
                <span class="config-val">{{ $infoModel['cv_folds'] ?? '5-fold Stratified CV' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">random_state</span>
                <span class="config-val">42</span>
            </div>
            <div class="config-item">
                <span class="config-key">Total Fitur</span>
                <span class="config-val">{{ $infoModel['total_fitur_rf'] ?? '—' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Total Prediksi</span>
                <span class="config-val">{{ $dbStats['total_prediksi'] ?? 0 }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Teridentifikasi Kuliah</span>
                <span class="config-val">{{ $dbStats['total_kuliah'] ?? 0 }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Tidak Teridentifikasi</span>
                <span class="config-val">{{ $dbStats['total_tidak'] ?? 0 }}</span>
            </div>
        </div>
    </div>

    {{-- KNN --}}
    <div class="card config-card">
        <div class="config-card-header">
            <div class="config-card-icon config-icon-purple">KNN</div>
            <div>
                <div class="card-title card-title-compact">KNN Similarity</div>
                <div class="config-card-subtitle">NearestNeighbors (Euclidean)</div>
            </div>
        </div>

        <div class="config-grid">
            <div class="config-item">
                <span class="config-key">Algoritma</span>
                <span class="config-val">KNeighborsClassifier (NearestNeighbors)</span>
            </div>
            <div class="config-item">
                <span class="config-key">Metric</span>
                <span class="config-val">{{ $knn['metric'] }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">N Neighbors</span>
                <span class="config-val">{{ $knn['n_neighbors'] }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Total Alumni KNN</span>
                <span class="config-val">{{ $knn['total_alumni'] }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Top-N Output</span>
                <span class="config-val">{{ $knn['top_n'] }} rekomendasi</span>
            </div>
            <div class="config-item">
                <span class="config-key">Top Alumni</span>
                <span class="config-val">{{ $knn['top_alumni'] }} alumni terdekat</span>
            </div>
            <div class="config-item">
                <span class="config-key">Similarity Threshold</span>
                <span class="config-val">{{ $knn['similarity_threshold'] ?? 'Tidak aktif' }}</span>
            </div>
            <div class="config-item">
                <span class="config-key">Final Score</span>
                <span class="config-val">{{ $knn['w_similarity'] }} × similarity + {{ $knn['w_frequency'] }} × frequency</span>
            </div>
            <div class="config-item">
                <span class="config-key">Similarity Score</span>
                <span class="config-val">1 / (1 + euclidean_distance)</span>
            </div>
            <div class="config-item">
                <span class="config-key">Frequency Score</span>
                <span class="config-val">jumlah_alumni_prodi / n_neighbors</span>
            </div>
            <div class="config-item">
                <span class="config-key">Metode Hybrid</span>
                <span class="config-val">{{ $infoModel['aturan_integrasi'] ?? 'RF predict_proba + threshold → KNN NearestNeighbors' }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── FORMAT KOLOM (kiri) + STATUS MODEL (kanan) ──────────────── --}}
<div class="two-col">

    {{-- KIRI: Format Kolom --}}
    <div class="card">
        <div class="card-title">Format Kolom yang Digunakan Model Random Forest</div>
        <div class="section-desc">
            Seluruh fitur di bawah ini digunakan sebagai input model RF setelah preprocessing dan encoding.
        </div>
        <div class="table-responsive">
            <table class="fitur-table">
                <thead>
                    <tr>
                        <th>Nama Kolom</th>
                        <th>Tipe</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($fiturNumerik as $f)
                    <tr>
                        <td><code class="fitur-code">{{ $f['nama'] }}</code></td>
                        <td>
                            @if($f['tipe'] === 'Numerik')
                                <span class="stat-badge badge-blue">Numerik</span>
                            @elseif($f['tipe'] === 'Turunan')
                                <span class="stat-badge badge-green">Turunan</span>
                            @else
                                <span class="stat-badge badge-purple">OHE</span>
                            @endif
                        </td>
                        <td class="table-desc">{{ $f['keterangan'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="table-note">
            Tipe <b>Turunan</b>: dihitung otomatis oleh Flask dari nilai rata-rata mapel. Tidak perlu diinput manual.
            Tipe <b>OHE</b>: dihasilkan dari proses One Hot Encoding pada kolom Jurusan_Smk.
        </div>
    </div>

    {{-- KANAN: Status Model --}}
    <div class="card">
        <div class="card-title">Status Model Saat Ini</div>
        <div class="status-list">
            <div class="status-item {{ $flaskOnline ? 'status-item-online' : 'status-item-offline' }}">
                <div class="status-item-left">
                    <span class="status-dot {{ $flaskOnline ? 'dot-online' : 'dot-offline' }}"></span>
                    <span class="status-item-label">Flask ML</span>
                </div>
                <span class="status-item-val {{ $flaskOnline ? 'val-online' : 'val-offline' }}">
                    {{ $flaskOnline ? 'Online' : 'Offline' }}
                </span>
            </div>
            <div class="status-item {{ $flaskOnline ? 'status-item-online' : 'status-item-offline' }}">
                <div class="status-item-left">
                    <span class="status-dot {{ $flaskOnline ? 'dot-online' : 'dot-offline' }}"></span>
                    <span class="status-item-label">Model RF</span>
                </div>
                <span class="status-item-val {{ $flaskOnline ? 'val-online' : 'val-offline' }}">
                    {{ $flaskOnline ? 'Loaded' : 'Tidak tersedia' }}
                </span>
            </div>
            <div class="status-item {{ $flaskOnline ? 'status-item-online' : 'status-item-offline' }}">
                <div class="status-item-left">
                    <span class="status-dot {{ $flaskOnline ? 'dot-online' : 'dot-offline' }}"></span>
                    <span class="status-item-label">Dataset KNN Alumni</span>
                </div>
                <span class="status-item-val {{ $flaskOnline ? 'val-online' : 'val-offline' }}">
                    {{ $flaskOnline ? ($infoModel['jumlah_data_alumni_basis'] ?? '—') . ' alumni' : 'Tidak tersedia' }}
                </span>
            </div>
            <div class="status-item {{ $isLiveFI ? 'status-item-online' : 'status-item-offline' }}">
                <div class="status-item-left">
                    <span class="status-dot {{ $isLiveFI ? 'dot-online' : 'dot-offline' }}"></span>
                    <span class="status-item-label">Feature Importance</span>
                </div>
                <span class="status-item-val {{ $isLiveFI ? 'val-online' : 'val-offline' }}">
                    {{ $isLiveFI ? 'Live dari model' : 'Statis (fallback)' }}
                </span>
            </div>
        </div>
    </div>

</div>

{{-- ── TABEL MAPPING JURUSAN SMK ─────────────────────────────────── --}}
<div class="card">
    <div class="card-title">Daftar Jurusan SMK & Singkatan</div>
    <div class="table-responsive">
        <table class="fitur-table">
            <thead>
                <tr>
                    <th>Singkatan</th>
                    <th>Nama Lengkap</th>
                    @if($flaskOnline && isset($alumniPerJurusan) && count($alumniPerJurusan))
                    <th>Alumni KNN</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse($jurusanMapping as $singkatan => $namaLengkap)
                <tr>
                    <td><code class="fitur-code">{{ $singkatan }}</code></td>
                    <td class="table-desc table-desc-sm">{{ $namaLengkap }}</td>
                    @if($flaskOnline && isset($alumniPerJurusan) && count($alumniPerJurusan))
                    <td class="table-center">
                        @php $jumlah = $alumniPerJurusan[$singkatan]['jumlah_alumni'] ?? 0; @endphp
                        @if($jumlah > 0)
                            <span class="stat-badge badge-blue">{{ $jumlah }}</span>
                        @else
                            <span class="text-muted-light">—</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @empty
                <tr>
                    <td colspan="{{ ($flaskOnline && count($alumniPerJurusan)) ? 3 : 2 }}" class="table-center text-muted-light">
                        Data jurusan tidak tersedia.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ── FEATURE IMPORTANCE (ApexCharts, data dari Flask /feature-importance) ── --}}
<div class="card">
    <div class="card-title card-title-inline">
        Feature Importance (Top 15 Fitur)
        @if($isLiveFI)
            <span class="stat-badge badge-green badge-xs">Live dari model</span>
        @else
            <span class="stat-badge badge-amber badge-xs">Statis (Flask offline)</span>
        @endif
    </div>

    @php
        $fiLabels = array_keys($fiForChart);
        $fiValues = array_values($fiForChart);
        $fiColors = array_map(function($name) {
            if (str_starts_with($name, 'Jurusan_Smk_')) return '#7c3aed';
            if (in_array($name, ['nilai_max','nilai_min','std_nilai'])) return '#10b981';
            return '#3b82f6';
        }, $fiLabels);
    @endphp

    <div id="chart-fi" class="feature-chart"></div>

    <div class="chart-legend">
        <span><span class="legend-dot legend-blue"></span>Fitur Numerik Mapel</span>
        <span><span class="legend-dot legend-green"></span>Fitur Turunan Agregat</span>
        <span><span class="legend-dot legend-purple"></span>Fitur OHE Jurusan</span>
    </div>
    <div class="fi-note">Diambil dari <code>models/evaluation/rf_feature_importance.csv</code> via endpoint <code>/feature-importance</code>.</div>
</div>

<script>
const fiLabels = @json($fiLabels ?? []);
const fiValues = @json($fiValues ?? []);
const fiColors = @json($fiColors ?? []);

let chartFi = null;

function isDarkMode() {
    return document.body.classList.contains('dark');
}

function getChartTheme() {
    const dark = isDarkMode();

    return {
        mode: dark ? 'dark' : 'light',
        text: dark ? '#e5e7eb' : '#374151',
        muted: dark ? '#cbd5e1' : '#6b7280',
        grid: dark ? '#334155' : '#f3f4f6'
    };
}

function buildFeatureImportanceChart() {
    const el = document.querySelector('#chart-fi');
    if (!el) return;

    if (!fiLabels.length || !fiValues.length) {
        el.innerHTML = '<div style="padding:24px;text-align:center;color:#6b7280;font-size:0.9rem;">Data feature importance belum tersedia.</div>';
        return;
    }

    if (chartFi) {
        chartFi.destroy();
        chartFi = null;
    }

    const theme = getChartTheme();

    chartFi = new ApexCharts(el, {
        series: [
            {
                name: 'Importance',
                data: fiValues.map(v => parseFloat(parseFloat(v).toFixed(6)))
            }
        ],
        chart: {
            type: 'bar',
            height: 400,
            toolbar: { show: false },
            fontFamily: 'inherit',
            foreColor: theme.text,
            background: 'transparent'
        },
        theme: {
            mode: theme.mode
        },
        plotOptions: {
            bar: {
                horizontal: true,
                borderRadius: 4,
                distributed: true,
                barHeight: '75%',
                dataLabels: {
                    position: 'right'
                }
            }
        },
        colors: fiColors,
        dataLabels: {
            enabled: true,
            formatter: val => parseFloat(val).toFixed(4),
            style: {
                fontSize: '12px',
                colors: [theme.text]
            },
            offsetX: 4
        },
        xaxis: {
            categories: fiLabels,
            labels: {
                style: {
                    fontSize: '12px',
                    colors: theme.muted
                },
                formatter: val => parseFloat(val).toFixed(3)
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '12px',
                    colors: theme.text
                },
                maxWidth: 220
            }
        },
        grid: {
            borderColor: theme.grid,
            padding: {
                right: 24,
                bottom: 10
            }
        },
        legend: {
            show: false
        },
        tooltip: {
            theme: theme.mode,
            y: {
                formatter: val => 'Importance: ' + parseFloat(val).toFixed(6)
            }
        }
    });

    chartFi.render();
}

document.addEventListener('DOMContentLoaded', buildFeatureImportanceChart);

new MutationObserver(buildFeatureImportanceChart).observe(document.body, {
    attributes: true,
    attributeFilter: ['class']
});
</script>

@endsection