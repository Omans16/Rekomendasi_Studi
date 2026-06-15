@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/preprocessing.css') }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endpush

@section('content')

@php
    // Data distribusi alumni per jurusan dari Flask /dashboard
    $alumniPerJurusan = [];
    $topUniversitas   = [];
    $topJurusanKuliah = [];
    $totalAlumni      = 0;
    $totalUniversitas = 0;
    $totalJurusanKuliah = 0;
    $tahunInfo        = [];

    if ($flaskOnline && isset($dashboard) && is_array($dashboard)) {
        $alumniPerJurusan  = $dashboard['alumni_per_jurusan']   ?? [];
        $topUniversitas    = $dashboard['top_universitas']       ?? [];
        $topJurusanKuliah  = $dashboard['top_jurusan_kuliah']    ?? [];
        $totalAlumni       = $dashboard['total_alumni']          ?? 0;
        $totalUniversitas  = $dashboard['total_universitas']     ?? 0;
        $totalJurusanKuliah= $dashboard['total_jurusan_kuliah']  ?? 0;
        if (isset($dashboard['tahun_lulus_min'])) {
            $tahunInfo = [
                'min'  => $dashboard['tahun_lulus_min'],
                'max'  => $dashboard['tahun_lulus_max'],
                'list' => $dashboard['tahun_list'] ?? [],
            ];
        }
    }
@endphp

<div class="page-header">
    <h2>Preprocessing Data</h2>
    <p>Tahapan pembersihan dan transformasi data sebelum pelatihan model</p>
</div>

{{-- ── STATUS FLASK ──────────────────────────────────────────────── --}}
<div class="status-bar {{ $flaskOnline ? 'status-online' : 'status-offline' }}" style="border-radius:10px;padding:12px 18px;margin-bottom:20px;display:flex;align-items:center;gap:10px;font-size:0.83rem">
    <span class="status-dot {{ $flaskOnline ? 'dot-online' : 'dot-offline' }}" style="width:8px;height:8px;border-radius:50%;display:inline-block;{{ $flaskOnline ? 'background:#10b981' : 'background:#ef4444' }}"></span>
    <span style="color:#6b7280">Statistik Dataset CBF:</span>
    <span style="font-weight:600;{{ $flaskOnline ? 'color:#065f46' : 'color:#991b1b' }}">
        {{ $flaskOnline ? 'Live dari Flask /dashboard' : 'Flask Offline — statistik tidak tersedia' }}
    </span>
    @if($flaskOnline)
        <span style="margin-left:auto;color:#9ca3af">{{ $totalAlumni }} alumni · {{ $totalUniversitas }} universitas · {{ $totalJurusanKuliah }} jurusan kuliah</span>
    @endif
</div>

{{-- ── STATS MINI (live dari Flask) ─────────────────────────────── --}}
@if($flaskOnline)
<div class="three-col" style="margin-bottom:20px">
    <div class="stat-card">
        <div class="stat-num">{{ $totalAlumni }}</div>
        <div class="stat-lab">Total Alumni CBF</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">{{ $totalUniversitas }}</div>
        <div class="stat-lab">Universitas Terdaftar</div>
    </div>
    <div class="stat-card">
        <div class="stat-num">{{ $totalJurusanKuliah }}</div>
        <div class="stat-lab">Jurusan Kuliah</div>
    </div>
</div>
@endif

{{-- ── TAHAPAN PREPROCESSING ────────────────────────────────────── --}}
<div class="card">
    <div class="card-title">Tahapan Preprocessing</div>

    <div class="pipeline-step">
        <div class="step-num">1</div>
        <div class="step-content">
            <div class="step-title">Pembersihan Data (Data Cleaning)</div>
            <div class="step-desc">
                Hapus baris dengan No_Siswa duplikat (ambil data terbaru).
                Standarisasi nama jurusan SMK (huruf kapital, konsisten).
                Hapus baris dengan Status_Studi_Lanjut kosong.
            </div>
        </div>
    </div>

    <div class="pipeline-step">
        <div class="step-num">2</div>
        <div class="step-content">
            <div class="step-title">Handling Missing Value</div>
            <div class="step-desc">
                Nilai mapel yang kosong: imputasi dengan median nilai per jurusan SMK (bukan mean global, untuk menjaga representasi per jurusan).
                Baris dengan Status_Studi_Lanjut kosong dihapus.
            </div>
        </div>
    </div>

    <div class="pipeline-step">
        <div class="step-num">3</div>
        <div class="step-content">
            <div class="step-title">Feature Engineering</div>
            <div class="step-desc">
                Dari nilai per semester dihitung rata-rata per mapel:
                <code>rata_pai = mean(pai_1..5)</code>, <code>rata_ppkn</code>, <code>rata_ind</code>, <code>rata_mtk</code>, <code>rata_ing</code>.
                Kemudian dihitung: <code>nilai_max</code> = nilai tertinggi dari 5 rata-rata,
                <code>nilai_min</code> = terendah, <code>std_nilai</code> = standar deviasi.
                Ketiga fitur ini dihitung otomatis oleh Flask saat inference — tidak perlu diinput manual.
            </div>
        </div>
    </div>

    <div class="pipeline-step">
        <div class="step-num">4</div>
        <div class="step-content">
            <div class="step-title">Encoding Variabel Kategorikal</div>
            <div class="step-desc">
                Jurusan SMK di-encode menggunakan <b>One Hot Encoding (OHE)</b> — menghasilkan kolom biner per jurusan.
                Daftar kolom OHE disimpan di <code>ohe_feature_columns.pkl</code> untuk digunakan saat inference.
                Jurusan yang tidak ada di training diisi 0 otomatis (<code>reindex fill_value=0</code>).
                Status lanjut: Teridentifikasi Lanjut Kuliah = 1, Tidak = 0 (target variabel).
            </div>
        </div>
    </div>

    <div class="pipeline-step">
        <div class="step-num">5</div>
        <div class="step-content">
            <div class="step-title">Penanganan Imbalance Data</div>
            <div class="step-desc">
                Menggunakan <b>BalancedRandomForestClassifier</b> yang menangani imbalance secara internal per tree.
                Recall kelas minoritas (kuliah) diprioritaskan karena false negative lebih merugikan.
                GridSearchCV menggunakan <code>scoring='f1'</code> — bukan accuracy — agar tuning sensitif terhadap kelas minoritas.
            </div>
        </div>
    </div>

    <div class="pipeline-step">
        <div class="step-num">6</div>
        <div class="step-content">
            <div class="step-title">Pemisahan Data Train-Test</div>
            <div class="step-desc">
                Split 80:20 dengan <code>stratify=y</code> — memastikan proporsi kelas seimbang di kedua set.
                <code>random_state=42</code> untuk reproduksibilitas.
                Cross-validation 5-fold Stratified untuk evaluasi yang lebih robust.
            </div>
        </div>
    </div>

    <div class="pipeline-step">
        <div class="step-num">7</div>
        <div class="step-content">
            <div class="step-title">CBF: Pembentukan Profil Alumni (TF-IDF)</div>
            <div class="step-desc">
                Setiap alumni direpresentasikan sebagai teks gabungan:
                <code>Jurusan_Smk + Jurusan_Smk + Jurusan_Kuliah + Nama_Universitas</code>
                (Jurusan_Smk diulang 2× untuk bobot lebih besar).
                TF-IDF menggunakan <code>ngram_range=(1,2)</code> agar frasa seperti
                "teknik mesin" atau "sistem informasi" ditangkap sebagai satu unit.
            </div>
        </div>
    </div>
</div>

{{-- ── DISTRIBUSI ALUMNI PER JURUSAN (live dari Flask) ─────────── --}}
@if($flaskOnline && count($alumniPerJurusan))
<div class="card">
    <div class="card-title" style="display:flex;align-items:center;gap:8px">
        Distribusi Alumni per Jurusan SMK
        <span class="stat-badge badge-green" style="font-size:0.7rem">Live dari Flask</span>
    </div>
    <div id="chart-alumni-jurusan" style="min-height:300px"></div>
</div>
@endif

{{-- ── TOP UNIVERSITAS + TOP JURUSAN KULIAH ─────────────────────── --}}
@if($flaskOnline && (count($topUniversitas) || count($topJurusanKuliah)))
<div class="two-col">

    @if(count($topUniversitas))
    <div class="card">
        <div class="card-title" style="display:flex;align-items:center;gap:8px">
            Top Universitas Tujuan Alumni
            <span class="stat-badge badge-green" style="font-size:0.7rem">Live dari Flask</span>
        </div>
        <div id="chart-top-univ" style="min-height:280px"></div>
    </div>
    @endif

    @if(count($topJurusanKuliah))
    <div class="card">
        <div class="card-title" style="display:flex;align-items:center;gap:8px">
            Top Jurusan Kuliah Pilihan Alumni
            <span class="stat-badge badge-green" style="font-size:0.7rem">Live dari Flask</span>
        </div>
        <div id="chart-top-jurusan" style="min-height:280px"></div>
    </div>
    @endif

</div>
@endif

{{-- ── TABEL MAPPING JURUSAN SMK ─────────────────────────────────── --}}
<div class="card">
    <div class="card-title">Daftar Jurusan SMK & Singkatan</div>
    <div style="overflow-x:auto">
        <table class="fitur-table">
            <thead>
                <tr>
                    <th>Singkatan</th>
                    <th>Nama Lengkap</th>
                    @if($flaskOnline && count($alumniPerJurusan))
                    <th>Alumni CBF</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach($jurusanMapping as $singkatan => $namaLengkap)
                <tr>
                    <td><code class="fitur-code">{{ $singkatan }}</code></td>
                    <td style="font-size:0.83rem;color:#374151">{{ $namaLengkap }}</td>
                    @if($flaskOnline && count($alumniPerJurusan))
                    <td style="text-align:center;font-size:0.82rem">
                        @php $jumlah = $alumniPerJurusan[$singkatan]['jumlah_alumni'] ?? 0; @endphp
                        @if($jumlah > 0)
                            <span class="stat-badge badge-blue">{{ $jumlah }}</span>
                        @else
                            <span style="color:#d1d5db">—</span>
                        @endif
                    </td>
                    @endif
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ── FITUR INPUT MODEL ────────────────────────────────────────── --}}
<div class="card">
    <div class="card-title">Fitur Input Model RF</div>
    <div class="two-col" style="gap:16px">
        <div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:8px">Fitur Input Langsung</div>
            @foreach($fiturInfo['fitur_input'] as $nama => $ket)
            <div style="padding:7px 0;border-bottom:1px solid #f3f4f6;display:flex;gap:10px;font-size:0.82rem">
                <code class="fitur-code">{{ $nama }}</code>
                <span style="color:#6b7280;flex:1">{{ $ket }}</span>
            </div>
            @endforeach
        </div>
        <div>
            <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;margin-bottom:8px">Fitur Turunan (dihitung Flask)</div>
            @foreach($fiturInfo['fitur_turunan'] as $nama => $ket)
            <div style="padding:7px 0;border-bottom:1px solid #f3f4f6;display:flex;gap:10px;font-size:0.82rem">
                <code class="fitur-code">{{ $nama }}</code>
                <span style="color:#6b7280;flex:1">{{ $ket }}</span>
            </div>
            @endforeach
            <div style="margin-top:10px;padding:10px 12px;background:#fffbeb;border-radius:6px;font-size:0.79rem;color:#92400e;border:1px solid #fde68a">
                {{ $fiturInfo['catatan'] }}
            </div>
        </div>
    </div>
    <div style="margin-top:12px;padding:10px 12px;background:#f0f9ff;border-radius:6px;font-size:0.79rem;color:#0369a1;border:1px solid #bae6fd">
        <b>Encoding:</b> {{ $fiturInfo['encoding'] }}
    </div>
</div>

<style>
.fitur-code { background:#f3f4f6; color:#374151; padding:2px 6px; border-radius:4px; font-size:0.79rem; font-family:monospace; }
.fitur-table { width:100%; border-collapse:collapse; font-size:0.83rem; }
.fitur-table th { text-align:left; padding:9px 12px; background:#f9fafb; color:#6b7280; font-weight:500; border-bottom:1px solid #e5e7eb; }
.fitur-table td { padding:8px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.fitur-table tr:last-child td { border-bottom:none; }
.stat-num { font-size:1.6rem; font-weight:700; color:#111827; }
.stat-lab { font-size:0.78rem; color:#9ca3af; margin-top:2px; }
</style>

@if($flaskOnline)
<script>
document.addEventListener('DOMContentLoaded', function() {

    @if(count($alumniPerJurusan))
    // ── Distribusi Alumni per Jurusan ──
    const jurusanData = @json($alumniPerJurusan);
    const jLabels = Object.keys(jurusanData);
    const jValues = jLabels.map(k => jurusanData[k].jumlah_alumni);

    new ApexCharts(document.querySelector('#chart-alumni-jurusan'), {
        series: [{ data: jValues }],
        chart: { type: 'bar', height: Math.max(260, jLabels.length * 28 + 60), toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '58%' } },
        colors: ['#3b82f6'],
        dataLabels: {
            enabled: true,
            formatter: val => val,
            style: { fontSize: '11px', colors: ['#374151'] },
            offsetX: 4,
        },
        xaxis: { categories: jLabels, labels: { style: { fontSize: '12px', colors: '#6b7280' } } },
        yaxis: { labels: { style: { fontSize: '12px', colors: '#374151' } } },
        grid: { borderColor: '#f3f4f6' },
        tooltip: { y: { formatter: val => val + ' alumni' } },
    }).render();
    @endif

    @if(count($topUniversitas))
    // ── Top Universitas ──
    const univData = @json($topUniversitas);
    new ApexCharts(document.querySelector('#chart-top-univ'), {
        series: [{ data: univData.map(d => d.jumlah) }],
        chart: { type: 'bar', height: Math.max(240, univData.length * 28 + 60), toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
        colors: ['#10b981'],
        dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#065f46'] }, offsetX: 4 },
        xaxis: { categories: univData.map(d => d.universitas), labels: { style: { fontSize: '11px', colors: '#6b7280' } } },
        yaxis: { labels: { style: { fontSize: '11px', colors: '#374151' }, maxWidth: 180 } },
        grid: { borderColor: '#f3f4f6' },
        tooltip: { y: { formatter: val => val + ' alumni' } },
    }).render();
    @endif

    @if(count($topJurusanKuliah))
    // ── Top Jurusan Kuliah ──
    const jurData = @json($topJurusanKuliah);
    new ApexCharts(document.querySelector('#chart-top-jurusan'), {
        series: [{ data: jurData.map(d => d.jumlah) }],
        chart: { type: 'bar', height: Math.max(240, jurData.length * 28 + 60), toolbar: { show: false } },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '55%' } },
        colors: ['#7c3aed'],
        dataLabels: { enabled: true, style: { fontSize: '11px', colors: ['#4c1d95'] }, offsetX: 4 },
        xaxis: { categories: jurData.map(d => d.jurusan_kuliah), labels: { style: { fontSize: '11px', colors: '#6b7280' } } },
        yaxis: { labels: { style: { fontSize: '11px', colors: '#374151' }, maxWidth: 180 } },
        grid: { borderColor: '#f3f4f6' },
        tooltip: { y: { formatter: val => val + ' alumni' } },
    }).render();
    @endif

});
</script>
@endif

@endsection
