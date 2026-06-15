@extends('layouts.app')

<link rel="stylesheet" href="{{ asset('css/admin/dashboard.css') }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>

@section('content')
@php
    $flaskStats = $flaskStats ?? [];
    $dbStats = $dbStats ?? [];
    $flaskOnline = $flaskOnline ?? false;

    $alumniPerJurusan = $flaskStats['alumni_per_jurusan'] ?? [];
    $topUniv          = $flaskStats['top_universitas'] ?? [];
    $topJurKuliah     = $flaskStats['top_program_studi'] ?? [];

    $totalAlumniKnn = $flaskStats['total_alumni']
        ?? $flaskStats['jumlah_data_alumni_basis']
        ?? '—';

    $totalUniversitas = $flaskStats['total_universitas'] ?? '—';
    $totalProgramStudi = $flaskStats['total_program_studi'] ?? '—';

    $prediksiTerakhir = $dbStats['prediksi_terakhir'] ?? collect();
@endphp

<div class="dashboard-page">
    <div class="page-header page-header-row">
        <div>
            <h2>Dashboard</h2>
            <p>Ringkasan data alumni dan performa sistem rekomendasi</p>
        </div>

        <a href="{{ route('input.siswa') }}" class="btn-dashboard-primary">
            + Prediksi Siswa Baru
        </a>
    </div>

    @if(!$flaskOnline)
        <div class="alert-ml-offline">
            ⚠️ Layanan ML (Flask) sedang tidak aktif. Beberapa data tidak tersedia.
        </div>
    @endif

    <div class="filter-box">
        <span class="filter-label">Filter Visualisasi:</span>

        <select id="filterJurusan" class="filter-select">
            <option value="">Semua Jurusan SMK</option>
            @foreach($alumniPerJurusan as $singkatan => $info)
                <option value="{{ $singkatan }}">
                    {{ $singkatan }} — {{ $info['nama_lengkap'] ?? $singkatan }}
                </option>
            @endforeach
        </select>

        <button type="button" onclick="applyFilter()" class="filter-btn filter-btn-primary">
            Terapkan
        </button>
        <button type="button" onclick="resetFilter()" class="filter-btn filter-btn-secondary">
            Reset
        </button>

        <span id="filterInfo" class="filter-info"></span>
    </div>

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
                    Berpotensi: {{ $dbStats['total_kuliah'] ?? 0 }} | Tidak Berpotensi: {{ $dbStats['total_tidak'] ?? 0 }}
                </span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tahun Alumni</div>
            <div class="stat-value stat-value-small">2023 – 2025</div>
            <div class="stat-sub">Rentang tahun lulus alumni</div>
        </div>
    </div>

    @if(!empty($prediksiTerakhir) && $prediksiTerakhir->count())
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
                            <th></th>
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
                                <td class="td-strong">{{ $item->nama_siswa ?? 'Siswa' }}</td>
                                <td>
                                    <span class="stat-badge badge-blue" title="{{ $item->jurusan_smk_lengkap ?? $item->jurusan_smk }}">
                                        {{ $item->jurusan_smk }}
                                    </span>
                                </td>
                                <td>
                                    @if($prediksiRf === 1)
                                        <span class="stat-badge badge-green">Teridentifikasi Studi Lanjut</span>
                                    @else
                                        <span class="stat-badge badge-amber">Tidak Teridentifikasi Studi Lanjut</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $probabilitas }}%
                                    @if(!empty($item->kategori_probabilitas))
                                        <span class="text-muted-small">({{ $item->kategori_probabilitas }})</span>
                                    @endif
                                </td>
                                <td class="text-muted-small">
                                    {{ $item->created_at->diffForHumans() }}
                                </td>
                                <td>
                                    <a href="{{ route('hasil.prediksi.detail', $item->id) }}" class="btn btn-primary btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <a href="{{ route('hasil.prediksi') }}" class="card-link">
                Lihat Semua Prediksi →
            </a>
        </div>
    @endif

    <div class="card dashboard-card">
        <div class="card-title">Distribusi Alumni per Jurusan SMK</div>
        <div class="card-sub" id="subtitleSmk">
            Semua jurusan — {{ count($alumniPerJurusan) }} jurusan SMK
        </div>
        <div id="chartSmk" class="chart chart-smk"></div>
    </div>

    <div class="card dashboard-card">
        <div class="card-title">Top Universitas Tujuan Alumni</div>
        <div class="card-sub" id="subtitleUniv">
            Seluruh data — {{ is_numeric($totalAlumniKnn) ? $totalAlumniKnn : 0 }} alumni
        </div>
        <div id="chartUniv" class="chart chart-large"></div>
    </div>

    <div class="card dashboard-card">
        <div class="card-title">Top 10 Program Studi Terpilih</div>
        <div class="card-sub">
            Dari {{ is_numeric($totalProgramStudi) ? $totalProgramStudi : 0 }} program studi unik
        </div>
        <div id="chartJurKuliah" class="chart chart-large"></div>
    </div>
</div>

<script>
const rawTopUniv = @json($topUniv);
const rawTopJurKuliah = @json($topJurKuliah);
const rawAlumniSmk = @json($alumniPerJurusan);
const totalAlumni = @json(is_numeric($totalAlumniKnn) ? $totalAlumniKnn : 0);

let chartUniv = null;
let chartJur = null;
let chartSmk = null;
let activeJurusan = null;

function isDarkMode() {
    return document.body.classList.contains('dark');
}

function getChartTheme() {
    const dark = isDarkMode();

    return {
        mode: dark ? 'dark' : 'light',
        text: dark ? '#e5e7eb' : '#374151',
        muted: dark ? '#cbd5e1' : '#6b7280',
        grid: dark ? '#334155' : '#f3f4f6',
        tooltipBg: dark ? '#111827' : '#ffffff',
        tooltipText: dark ? '#f9fafb' : '#111827'
    };
}

function buildChartUniv(data) {
    if (chartUniv) chartUniv.destroy();
    if (!Array.isArray(data) || !data.length) return;

    const theme = getChartTheme();
    const sorted = [...data].sort((a, b) => (a.jumlah || 0) - (b.jumlah || 0));

    chartUniv = new ApexCharts(document.getElementById('chartUniv'), {
        chart: { type: 'bar', height: 400, toolbar: { show: false }, fontFamily: 'inherit', foreColor: theme.text },
        theme: { mode: theme.mode },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '85%', dataLabels: { position: 'right' } } },
        dataLabels: { enabled: true, formatter: v => `${v} alumni`, style: { fontSize: '12px', colors: [theme.text] }, offsetX: 4 },
        series: [{ name: 'Jumlah Alumni', data: sorted.map(d => d.jumlah || 0) }],
        xaxis: { categories: sorted.map(d => d.universitas || d.nama_universitas || '-'), labels: { style: { fontSize: '12px', colors: theme.muted } } },
        yaxis: { labels: { style: { fontSize: '12px', colors: theme.text } } },
        colors: ['#3b82f6'],
        grid: { borderColor: theme.grid, padding: { right: 24 } },
        tooltip: { theme: theme.mode, y: { formatter: v => `${v} alumni` } }
    });

    chartUniv.render();
}

function buildChartJur(data) {
    if (chartJur) chartJur.destroy();
    if (!Array.isArray(data) || !data.length) return;

    const theme = getChartTheme();
    const sorted = [...data].sort((a, b) => (a.jumlah || 0) - (b.jumlah || 0));
    const labels = sorted.map(d => d.program_studi || d.jurusan_kuliah || d.Jurusan_Kuliah || '-');

    chartJur = new ApexCharts(document.getElementById('chartJurKuliah'), {
        chart: { type: 'bar', height: 400, toolbar: { show: false }, fontFamily: 'inherit', foreColor: theme.text },
        theme: { mode: theme.mode },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '75%', dataLabels: { position: 'right' } } },
        dataLabels: { enabled: true, formatter: v => `${v} alumni`, style: { fontSize: '12px', colors: [theme.text] }, offsetX: 4 },
        series: [{ name: 'Jumlah Alumni', data: sorted.map(d => d.jumlah || 0) }],
        xaxis: { categories: labels, labels: { style: { fontSize: '12px', colors: theme.muted } } },
        yaxis: { labels: { style: { fontSize: '12px', colors: theme.text }, maxWidth: 220 } },
        colors: ['#7c3aed'],
        grid: { borderColor: theme.grid, padding: { right: 24 } },
        tooltip: { theme: theme.mode, y: { formatter: v => `${v} alumni` } }
    });

    chartJur.render();
}

function buildChartSmk(data, highlightKey = null) {
    if (chartSmk) chartSmk.destroy();
    if (!data || !Object.keys(data).length) return;

    const theme = getChartTheme();
    const keys = Object.keys(data);
    const values = keys.map(k => data[k].jumlah_alumni || data[k].jumlah || 0);
    const colors = keys.map(k => k === highlightKey ? '#ef4444' : '#7c3aed');

    chartSmk = new ApexCharts(document.getElementById('chartSmk'), {
        chart: { type: 'bar', height: 260, toolbar: { show: false }, fontFamily: 'inherit', foreColor: theme.text },
        theme: { mode: theme.mode },
        plotOptions: { bar: { horizontal: false, borderRadius: 4, columnWidth: '65%', distributed: !!highlightKey } },
        dataLabels: { enabled: true, style: { fontSize: '12px', colors: [theme.text] } },
        series: [{ name: 'Jumlah Alumni', data: values }],
        xaxis: { categories: keys, labels: { style: { fontSize: '12px', colors: theme.muted } } },
        yaxis: { labels: { style: { colors: theme.muted } } },
        colors: highlightKey ? colors : ['#7c3aed'],
        grid: { borderColor: theme.grid },
        tooltip: { theme: theme.mode, y: { formatter: v => `${v} alumni` } },
        legend: { show: false }
    });

    chartSmk.render();
}

function renderCharts() {
    buildChartUniv(rawTopUniv);
    buildChartJur(rawTopJurKuliah);
    buildChartSmk(rawAlumniSmk, activeJurusan);
}

function applyFilter() {
    const jurusan = document.getElementById('filterJurusan')?.value || '';
    const subtitleUniv = document.getElementById('subtitleUniv');
    const filterInfo = document.getElementById('filterInfo');

    activeJurusan = jurusan || null;
    buildChartSmk(rawAlumniSmk, activeJurusan);

    if (jurusan && rawAlumniSmk[jurusan]) {
        const info = rawAlumniSmk[jurusan];
        const namaLengkap = info.nama_lengkap || jurusan;
        const jumlahAlumni = info.jumlah_alumni || info.jumlah || 0;

        subtitleUniv.textContent = `Filter: ${jurusan} — ${namaLengkap} (${jumlahAlumni} alumni)`;
        filterInfo.textContent = `Filter aktif: Jurusan ${jurusan}`;
        return;
    }

    subtitleUniv.textContent = `Seluruh data — ${totalAlumni} alumni`;
    filterInfo.textContent = '';
}

function resetFilter() {
    const jurusanSelect = document.getElementById('filterJurusan');
    if (jurusanSelect) jurusanSelect.value = '';

    activeJurusan = null;
    buildChartSmk(rawAlumniSmk, null);

    document.getElementById('subtitleUniv').textContent = `Seluruh data — ${totalAlumni} alumni`;
    document.getElementById('filterInfo').textContent = '';
}

document.addEventListener('DOMContentLoaded', renderCharts);

new MutationObserver(renderCharts).observe(document.body, {
    attributes: true,
    attributeFilter: ['class']
});
</script>
@endsection