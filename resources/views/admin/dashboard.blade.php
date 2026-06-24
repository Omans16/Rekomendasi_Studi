@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>
    <script src="{{ asset('js/admin/dashboard.js') }}" defer></script>
@endpush

@section('content')

@php
    $flaskStats = $flaskStats ?? [];
    $dbStats = $dbStats ?? [];
    $flaskOnline = $flaskOnline ?? false;

    $alumniPerJurusan = $flaskStats['alumni_per_jurusan'] ?? [];
    $topUniv = $flaskStats['top_universitas'] ?? [];
    $topJurKuliah = $flaskStats['top_program_studi'] ?? [];

    $totalAlumniKnn = $flaskStats['total_alumni']
        ?? $flaskStats['jumlah_data_alumni_basis']
        ?? '—';

    $totalUniversitas = $flaskStats['total_universitas'] ?? '—';
    $totalProgramStudi = $flaskStats['total_program_studi'] ?? '—';

    $prediksiTerakhir = $dbStats['prediksi_terakhir'] ?? collect();

    if (!($prediksiTerakhir instanceof \Illuminate\Support\Collection)) {
        $prediksiTerakhir = collect($prediksiTerakhir);
    }

    $dashboardChartPayload = [
        'topUniv' => $topUniv,
        'topJurKuliah' => $topJurKuliah,
        'alumniSmk' => $alumniPerJurusan,
    ];
@endphp

<div class="dashboard-page">

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h2>Dashboard</h2>
                <p>
                    Pantau ringkasan data alumni, hasil prediksi terbaru, serta sebaran universitas,
                    program studi, dan jurusan SMK berdasarkan data tracer study.
                </p>
            </div>

            <a href="{{ route('admin.input.siswa') }}" class="btn-dashboard-primary">
                Minta Rekomendasi
            </a>
        </div>
    </div>

    @if(!$flaskOnline)
        <div class="alert-ml-offline">
            Layanan ML Flask sedang tidak aktif. Beberapa data model dan grafik mungkin tidak tersedia.
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Alumni KNN</div>
            <div class="stat-value">{{ $totalAlumniKnn }}</div>
            <div class="stat-sub">Data tracer study alumni</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Universitas Tujuan</div>
            <div class="stat-value">{{ $totalUniversitas }}</div>
            <div class="stat-sub">
                <span class="stat-badge badge-green">
                    {{ $totalProgramStudi }} program studi
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Prediksi</div>
            <div class="stat-value">{{ $dbStats['total_prediksi'] ?? 0 }}</div>
            <div class="stat-sub">
                <span class="stat-badge badge-blue">
                    Teridentifikasi: {{ $dbStats['total_kuliah'] ?? 0 }}
                    |
                    Tidak Teridentifikasi: {{ $dbStats['total_tidak'] ?? 0 }}
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tahun Alumni</div>
            <div class="stat-value stat-value-small">2023 – 2025</div>
            <div class="stat-sub">Rentang tahun lulus alumni</div>
        </div>
    </div>

    @if($prediksiTerakhir->count())
        <div class="card dashboard-card">
            <div class="card-title">Prediksi Terakhir</div>

            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Nama Siswa</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th>Probabilitas</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($prediksiTerakhir as $item)
                            @php
                                $prediksiRf = (int) ($item->prediksi_rf ?? 0);

                                $probabilitas = $item->probabilitas_studi_lanjut !== null
                                    ? round((float) $item->probabilitas_studi_lanjut * 100, 1)
                                    : 0;
                            @endphp

                            <tr>
                                <td class="td-strong" data-label="Nama Siswa">
                                    {{ $item->nama_siswa ?? 'Siswa' }}
                                </td>

                                <td data-label="Jurusan">
                                    <span class="stat-badge badge-blue" title="{{ $item->jurusan_smk_lengkap ?? $item->jurusan_smk }}">
                                        {{ $item->jurusan_smk }}
                                    </span>
                                </td>

                                <td data-label="Status">
                                    @if($prediksiRf === 1)
                                        <span class="stat-badge badge-green">
                                            Teridentifikasi Studi Lanjut
                                        </span>
                                    @else
                                        <span class="stat-badge badge-amber">
                                            Tidak Teridentifikasi Studi Lanjut
                                        </span>
                                    @endif
                                </td>

                                <td data-label="Probabilitas">
                                    {{ $probabilitas }}%

                                    @if(!empty($item->kategori_probabilitas))
                                        <span class="text-muted-small">
                                            ({{ $item->kategori_probabilitas }})
                                        </span>
                                    @endif
                                </td>

                                <td class="text-muted-small" data-label="Waktu">
                                    {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                </td>

                                <td data-label="Aksi">
                                    <a href="{{ route('admin.hasil.prediksi.detail', $item->id) }}" class="btn btn-primary btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('admin.hasil.prediksi') }}" class="card-link">
                Lihat Semua Prediksi →
            </a>
        </div>
    @endif

    <div class="card dashboard-card">
        <div class="card-title">Sebaran Alumni Berdasarkan Jurusan SMK</div>

        <div class="card-sub" id="subtitleSmk">
            Menampilkan jumlah alumni tracer study pada setiap jurusan SMK. Data diurutkan dari jumlah alumni terbanyak.
        </div>

        <div class="chart-scroll">
            <div id="chartSmk" class="chart chart-smk"></div>
        </div>
    </div>

    <div class="card dashboard-card">
        <div class="card-title">Sebaran Alumni Berdasarkan Universitas Tujuan</div>

        <div class="card-sub" id="subtitleUniv">
            Menampilkan universitas tujuan alumni terbanyak berdasarkan data tracer study.
        </div>

        <div class="chart-scroll">
            <div id="chartUniv" class="chart chart-large"></div>
        </div>
    </div>

    <div class="card dashboard-card">
        <div class="card-title">Sebaran Alumni Berdasarkan Program Studi</div>

        <div class="card-sub">
            Menampilkan 10 program studi yang paling banyak dipilih alumni.
        </div>

        <div class="chart-scroll">
            <div id="chartJurKuliah" class="chart chart-large"></div>
        </div>
    </div>

    <script type="application/json" id="dashboardChartPayload">
        @json($dashboardChartPayload)
    </script>

</div>

@endsection