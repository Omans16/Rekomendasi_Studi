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


        <a href="{{ route('admin.input.siswa') }}" class="btn-dashboard-primary">
            Minta Rekomendasi
        </a>
    </div>

    @if(!$flaskOnline)
        <div class="alert-ml-offline">
            ⚠️ Layanan ML (Flask) sedang tidak aktif. Beberapa data tidak tersedia.
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
                    Teridentifikasi: {{ $dbStats['total_kuliah'] ?? 0 }} | Tidak Teridentifikasi: {{ $dbStats['total_tidak'] ?? 0 }}
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
        <div id="chartSmk" class="chart chart-smk"></div>
    </div>

    <div class="card dashboard-card">
        <div class="card-title">Sebaran Alumni Berdasarkan Universitas Tujuan</div>
        <div class="card-sub" id="subtitleUniv">
            Menampilkan universitas tujuan alumni terbanyak berdasarkan data tracer study.
        </div>
        <div id="chartUniv" class="chart chart-large"></div>
    </div>

    <div class="card dashboard-card">
    <div class="card-title">Sebaran Alumni Berdasarkan Program Studi</div>
    <div class="card-sub">
        Menampilkan 10 program studi yang paling banyak dipilih alumni.
    </div>
        <div id="chartJurKuliah" class="chart chart-large"></div>
    </div>
</div>

<script>
const rawTopUniv = @json($topUniv);
const rawTopJurKuliah = @json($topJurKuliah);
const rawAlumniSmk = @json($alumniPerJurusan);

let chartUniv = null;
let chartJur = null;
let chartSmk = null;

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

    const rows = [...data].map(item => {
        return {
            label: item.universitas || item.nama_universitas || '-',
            jumlah: item.jumlah || item.total || 0
        };
    })
    .sort((a, b) => b.jumlah - a.jumlah)
    .slice(0, 10);

    const labels = rows.map(item => item.label);
    const values = rows.map(item => item.jumlah);
    const dynamicHeight = Math.max(360, rows.length * 42);

    chartUniv = new ApexCharts(document.getElementById('chartUniv'), {
        chart: {
            type: 'bar',
            height: dynamicHeight,
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
                borderRadius: 6,
                barHeight: '68%',
                dataLabels: {
                    position: 'right'
                }
            }
        },
        series: [
            {
                name: 'Jumlah Alumni',
                data: values
            }
        ],
        colors: ['#2563eb'],
        dataLabels: {
            enabled: true,
            formatter: val => `${val} alumni`,
            offsetX: 6,
            style: {
                fontSize: '12px',
                fontWeight: 700,
                colors: [theme.text]
            }
        },
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    fontSize: '12px',
                    colors: theme.muted
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '12px',
                    fontWeight: 700,
                    colors: theme.text
                },
                maxWidth: 260
            }
        },
        grid: {
            borderColor: theme.grid,
            strokeDashArray: 4,
            padding: {
                right: 36,
                left: 8
            }
        },
        tooltip: {
            theme: theme.mode,
            custom: function({ dataPointIndex }) {
                const item = rows[dataPointIndex];

                return `
                    <div style="padding:10px 12px;">
                        <div style="font-weight:800;margin-bottom:4px;">
                            ${item.label}
                        </div>
                        <div style="font-size:12px;font-weight:700;">
                            ${item.jumlah} alumni
                        </div>
                    </div>
                `;
            }
        },
        legend: {
            show: false
        }
    });

    chartUniv.render();
}

function buildChartJur(data) {
    if (chartJur) chartJur.destroy();
    if (!Array.isArray(data) || !data.length) return;

    const theme = getChartTheme();

    const rows = [...data].map(item => {
        return {
            label: item.program_studi || item.jurusan_kuliah || item.Jurusan_Kuliah || '-',
            jumlah: item.jumlah || item.total || 0
        };
    })
    .sort((a, b) => b.jumlah - a.jumlah)
    .slice(0, 10);

    const labels = rows.map(item => item.label);
    const values = rows.map(item => item.jumlah);
    const dynamicHeight = Math.max(390, rows.length * 44);

    chartJur = new ApexCharts(document.getElementById('chartJurKuliah'), {
        chart: {
            type: 'bar',
            height: dynamicHeight,
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
                borderRadius: 6,
                barHeight: '68%',
                dataLabels: {
                    position: 'right'
                }
            }
        },
        series: [
            {
                name: 'Jumlah Alumni',
                data: values
            }
        ],
        colors: ['#7c3aed'],
        dataLabels: {
            enabled: true,
            formatter: val => `${val} alumni`,
            offsetX: 6,
            style: {
                fontSize: '12px',
                fontWeight: 700,
                colors: [theme.text]
            }
        },
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    fontSize: '12px',
                    colors: theme.muted
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '12px',
                    fontWeight: 700,
                    colors: theme.text
                },
                maxWidth: 280
            }
        },
        grid: {
            borderColor: theme.grid,
            strokeDashArray: 4,
            padding: {
                right: 36,
                left: 8
            }
        },
        tooltip: {
            theme: theme.mode,
            custom: function({ dataPointIndex }) {
                const item = rows[dataPointIndex];

                return `
                    <div style="padding:10px 12px;">
                        <div style="font-weight:800;margin-bottom:4px;">
                            ${item.label}
                        </div>
                        <div style="font-size:12px;font-weight:700;">
                            ${item.jumlah} alumni
                        </div>
                    </div>
                `;
            }
        },
        legend: {
            show: false
        }
    });

    chartJur.render();
}

function buildChartSmk(data) {
    if (chartSmk) chartSmk.destroy();
    if (!data || !Object.keys(data).length) return;

    const theme = getChartTheme();

    const rows = Object.keys(data).map(key => {
        return {
            key: key,
            label: data[key].nama_lengkap || key,
            shortLabel: key,
            jumlah: data[key].jumlah_alumni || data[key].jumlah || 0
        };
    }).sort((a, b) => b.jumlah - a.jumlah);

    const labels = rows.map(item => item.shortLabel);
    const fullLabels = rows.map(item => item.label);
    const values = rows.map(item => item.jumlah);

    const dynamicHeight = Math.max(360, rows.length * 34);

    chartSmk = new ApexCharts(document.getElementById('chartSmk'), {
        chart: {
            type: 'bar',
            height: dynamicHeight,
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
                borderRadius: 6,
                barHeight: '68%',
                distributed: false,
                dataLabels: {
                    position: 'right'
                }
            }
        },
        series: [
            {
                name: 'Jumlah Alumni',
                data: values
            }
        ],
        colors: ['#7c3aed'],
        dataLabels: {
            enabled: true,
            formatter: val => `${val} alumni`,
            offsetX: 6,
            style: {
                fontSize: '12px',
                fontWeight: 700,
                colors: [theme.text]
            }
        },
        xaxis: {
            categories: labels,
            labels: {
                style: {
                    fontSize: '12px',
                    colors: theme.muted
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: '12px',
                    fontWeight: 700,
                    colors: theme.text
                },
                maxWidth: 120
            }
        },
        grid: {
            borderColor: theme.grid,
            strokeDashArray: 4,
            padding: {
                right: 36,
                left: 8
            }
        },
        tooltip: {
            theme: theme.mode,
            custom: function({ dataPointIndex }) {
                const item = rows[dataPointIndex];

                return `
                    <div style="padding:10px 12px;">
                        <div style="font-weight:800;margin-bottom:4px;">
                            ${item.shortLabel}
                        </div>
                        <div style="font-size:12px;margin-bottom:4px;">
                            ${item.label}
                        </div>
                        <div style="font-size:12px;font-weight:700;">
                            ${item.jumlah} alumni
                        </div>
                    </div>
                `;
            }
        },
        legend: {
            show: false
        }
    });

    chartSmk.render();
}

function renderCharts() {
    buildChartUniv(rawTopUniv);
    buildChartJur(rawTopJurKuliah);
    buildChartSmk(rawAlumniSmk);
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