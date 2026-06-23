@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/info-model.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="{{ asset('js/admin/info-model.js') }}" defer></script>
@endpush

@section('content')

@php
    $infoModel        = $infoModel        ?? [];
    $dbStats          = $dbStats          ?? [];
    $flaskOnline      = $flaskOnline      ?? false;
    $jurusanMapping   = $jurusanMapping   ?? [];
    $alumniPerJurusan = $alumniPerJurusan ?? [];
    $dashboard        = $dashboard        ?? [];
    $evaluasi         = $evaluasi         ?? [];

    if (isset($infoModel['data']) && is_array($infoModel['data'])) {
        $infoModel = $infoModel['data'];
    }

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

    $featureImportanceLive = null;

    if (!empty($featureImportance) && is_array($featureImportance)) {
        $featureImportanceLive = isset($featureImportance['data']) && is_array($featureImportance['data'])
            ? $featureImportance['data']
            : $featureImportance;
    }

    $featureImportanceFallback = [
        ['rank' => 1, 'feature' => 'rata_mtk',  'importance' => 0.18],
        ['rank' => 2, 'feature' => 'rata_ind',  'importance' => 0.15],
        ['rank' => 3, 'feature' => 'rata_ppkn', 'importance' => 0.14],
        ['rank' => 4, 'feature' => 'rata_ing',  'importance' => 0.13],
        ['rank' => 5, 'feature' => 'rata_pai',  'importance' => 0.12],
        ['rank' => 6, 'feature' => 'UKK',       'importance' => 0.10],
        ['rank' => 7, 'feature' => 'nilai_max', 'importance' => 0.09],
        ['rank' => 8, 'feature' => 'std_nilai', 'importance' => 0.08],
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

    $featureChartRows = [];

    foreach ($fiForChart as $label => $value) {
        $type = 'numeric';

        if (str_starts_with($label, 'Jurusan_Smk_')) {
            $type = 'ohe';
        } elseif (in_array($label, ['nilai_max', 'nilai_min', 'std_nilai'], true)) {
            $type = 'derived';
        }

        $featureChartRows[] = [
            'label' => $label,
            'value' => (float) $value,
            'type'  => $type,
        ];
    }

    $fiturNumerik = [
        [
            'nama' => 'rata_pai',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai PAI',
        ],
        [
            'nama' => 'rata_ppkn',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai PPKn',
        ],
        [
            'nama' => 'rata_ind',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai Bahasa Indonesia',
        ],
        [
            'nama' => 'rata_mtk',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai Matematika',
        ],
        [
            'nama' => 'rata_ing',
            'tipe' => 'Numerik',
            'keterangan' => 'Rata-rata nilai Bahasa Inggris',
        ],
        [
            'nama' => 'UKK',
            'tipe' => 'Numerik',
            'keterangan' => 'Nilai Ujian Kompetensi Keahlian',
        ],
        [
            'nama' => 'nilai_max',
            'tipe' => 'Turunan',
            'keterangan' => 'Nilai tertinggi dari rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, dan UKK',
        ],
        [
            'nama' => 'nilai_min',
            'tipe' => 'Turunan',
            'keterangan' => 'Nilai terendah dari rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, dan UKK',
        ],
        [
            'nama' => 'std_nilai',
            'tipe' => 'Turunan',
            'keterangan' => 'Standar deviasi nilai dari rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, dan UKK',
        ],
        [
            'nama' => 'Jurusan_Smk_*',
            'tipe' => 'OHE',
            'keterangan' => 'One Hot Encoding jurusan SMK',
        ],
    ];
@endphp

<div class="admin-info-page">

    <div class="page-header">
        <h2>Informasi Model</h2>
        <p>Performa dan konfigurasi Random Forest + KNN Similarity.</p>
    </div>

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

    <div class="two-col">
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

        <div class="card config-card">
            <div class="config-card-header">
                <div class="config-card-icon config-icon-knn">KNN</div>

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

    <div class="two-col">
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
                                <td data-label="Nama Kolom">
                                    <code class="fitur-code">{{ $f['nama'] }}</code>
                                </td>

                                <td data-label="Tipe">
                                    @if($f['tipe'] === 'Numerik')
                                        <span class="stat-badge badge-blue">Numerik</span>
                                    @elseif($f['tipe'] === 'Turunan')
                                        <span class="stat-badge badge-green">Turunan</span>
                                    @else
                                        <span class="stat-badge badge-purple">OHE</span>
                                    @endif
                                </td>

                                <td class="table-desc" data-label="Keterangan">
                                    {{ $f['keterangan'] }}
                                </td>
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

    <div class="card feature-card">
        <div class="card-title card-title-inline">
            Feature Importance (Top 15 Fitur)

            @if($isLiveFI)
                <span class="stat-badge badge-green badge-xs">Live dari model</span>
            @else
                <span class="stat-badge badge-amber badge-xs">Statis (Flask offline)</span>
            @endif
        </div>

        <script type="application/json" id="featureImportancePayload">
            @json($featureChartRows)
        </script>

        <div id="chart-fi" class="feature-chart"></div>

        <div class="chart-legend">
            <span><span class="legend-dot legend-numeric"></span>Fitur Numerik Mapel</span>
            <span><span class="legend-dot legend-derived"></span>Fitur Turunan Agregat</span>
            <span><span class="legend-dot legend-ohe"></span>Fitur OHE Jurusan</span>
        </div>

        <div class="fi-note">
            Diambil dari <code>models/evaluation/rf_feature_importance.csv</code> via endpoint <code>/feature-importance</code>.
        </div>
    </div>

</div>

@endsection