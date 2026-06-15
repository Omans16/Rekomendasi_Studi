@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/hasil-prediksi.css') }}">
@endpush

@section('content')

<div class="page-header">
    <h2>Hasil Prediksi & Rekomendasi</h2>
    <p>Output sistem hybrid Random Forest + KNN Similarity</p>
</div>

{{-- ================================================================
     STATE A: DETAIL SATU PREDIKSI  →  /hasil-prediksi/{id}
     ================================================================ --}}
@isset($detail)

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

@php
    $isKuliah = (int) ($detail->prediksi_rf ?? 0) === 1;

    $probRF = $detail->probabilitas_studi_lanjut !== null
        ? round((float) $detail->probabilitas_studi_lanjut * 100, 1)
        : 0;

    $profilSiswa = collect($detail->profil_siswa ?? []);
    $profilMap = [];

    foreach ($profilSiswa as $profil) {
        $atribut = $profil['atribut'] ?? null;
        if ($atribut) {
            $profilMap[$atribut] = $profil['nilai'] ?? null;
        }
    }

    $nilaiMax = $profilMap['Nilai Maksimum'] ?? null;
    $nilaiMin = $profilMap['Nilai Minimum'] ?? null;
    $stdNilai = $profilMap['Standar Deviasi Nilai'] ?? null;

    $rekUniv = collect($detail->rekomendasi_final ?? []);
    $alumniTerdekat = collect($detail->alumni_terdekat ?? []);
    $kualitas = $detail->kualitas_rekomendasi ?? null;

    $avgSim  = $rekUniv->isNotEmpty() ? round($rekUniv->avg('similarity_score') * 100, 1) : 0;
    $avgFinal = $rekUniv->isNotEmpty() ? round($rekUniv->avg('final_score') * 100, 1) : 0;
@endphp

{{-- ── HEADER STATUS ── --}}
<div class="result-header {{ $isKuliah ? 'result-kuliah' : 'result-tidak' }}">
    <div class="result-header-inner">
        <div class="result-status-badge {{ $isKuliah ? 'badge-kuliah' : 'badge-tidak' }}">
            {{ $isKuliah ? 'Teridentifikasi Studi Lanjut' : 'Tidak Teridentifikasi Studi Lanjut' }}
        </div>
        <div class="result-meta-row">
            <span class="result-meta-item">
                <span class="result-meta-label">Nama</span>
                <span class="result-meta-val">{{ $detail->nama_siswa ?? 'Siswa' }}</span>
            </span>
            <span class="result-meta-sep">·</span>
            <span class="result-meta-item">
                <span class="result-meta-label">Jurusan</span>
                <span class="result-meta-val">
                    <span class="stat-badge" title="{{ $detail->jurusan_smk_lengkap ?? $detail->jurusan_smk }}">{{ $detail->jurusan_smk }}</span>
                    @if(!empty($detail->jurusan_smk_lengkap) && $detail->jurusan_smk_lengkap !== $detail->jurusan_smk)
                        <span class="jurusan-fullname">{{ $detail->jurusan_smk_lengkap }}</span>
                    @endif
                </span>
            </span>
            <span class="result-meta-sep">·</span>
            <span class="result-meta-item">
                <span class="result-meta-label">Probabilitas RF</span>
                <span class="result-meta-val">
                    {{ $probRF }}%
                    @if(!empty($detail->kategori_probabilitas))
                        <span class="stat-badge badge-blue">{{ $detail->kategori_probabilitas }}</span>
                    @endif
                </span>
            </span>
            <span class="result-meta-sep">·</span>
            <span class="result-meta-item">
                <span class="result-meta-label">UKK</span>
                <span class="result-meta-val">{{ $detail->ukk }}</span>
            </span>
        </div>
    </div>
</div>

{{-- ── MAIN CONTENT ── --}}
<div class="two-col">

    {{-- KOLOM KIRI --}}
    <div>

        {{-- Nilai Akademik --}}
        <div class="card">
            <div class="card-title">Data Akademik</div>
            <div class="metric-row">
                <span class="label">Nilai UKK</span>
                <span class="value">{{ $detail->ukk }}</span>
            </div>
            <div class="metric-row">
                <span class="label">Nilai Maks / Min</span>
                <span class="value">{{ $nilaiMax ?? '—' }} / {{ $nilaiMin ?? '—' }}</span>
            </div>
            <div class="metric-row">
                <span class="label">Std. Deviasi</span>
                <span class="value">{{ $stdNilai ?? '—' }}</span>
            </div>
            @if($kualitas && isset($kualitas['rata_rata_similarity']))
            <div class="metric-row">
                <span class="label">Avg Neighbor Similarity</span>
                <span class="value">{{ number_format((float) $kualitas['rata_rata_similarity'], 3) }}</span>
            </div>
            @endif
        </div>

        {{-- Skor Probabilitas --}}
        <div class="card">
            <div class="card-title">Skor Probabilitas Model</div>

            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>P(Kuliah) — Random Forest</span>
                    <span>{{ $probRF }}%</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-blue" style="width:{{ $probRF }}%"></div>
                </div>
            </div>

            @if($rekUniv->isNotEmpty())
            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>Avg Similarity — KNN</span>
                    <span>{{ $avgSim }}%</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-green" style="width:{{ $avgSim }}%"></div>
                </div>
            </div>
            @endif

            <div class="formula-box">Final Score = (0.7 × similarity_score) + (0.3 × frequency_score)</div>

            @if($rekUniv->isNotEmpty())
            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>Avg Final Score</span>
                    <span>{{ $avgFinal }}%</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-purple" style="width:{{ $avgFinal }}%"></div>
                </div>
            </div>
            @endif
        </div>

        {{-- Interpretasi --}}
        <div class="card">
            <div class="card-title">Interpretasi Hasil</div>
            <div class="interpretasi-text">
                {{ $detail->narasi_rekomendasi ?? $detail->pesan ?? 'Tidak ada interpretasi.' }}
            </div>
        </div>

        @if($isKuliah && $alumniTerdekat->isNotEmpty())
        <div class="card">
            <div class="card-title">Alumni dengan Profil Paling Mirip</div>

            @foreach($alumniTerdekat as $alumni)
            <div class="rek-univ-block">
                <div class="rek-univ-header">
                    <div class="rek-rank {{ ($alumni['ranking'] ?? 0) === 1 ? 'rank-top' : '' }}">
                        {{ $alumni['ranking'] ?? '-' }}
                    </div>

                    <div class="rek-content">
                        <div class="rek-name">{{ $alumni['kode_alumni'] ?? 'Alumni' }}</div>
                        <div class="rek-jurusan">{{ $alumni['universitas'] ?? '-' }} — {{ $alumni['program_studi'] ?? '-' }}</div>
                        <div class="rek-sub">
                            Jurusan SMK: {{ $alumni['jurusan_smk_alumni'] ?? '-' }}
                            | Status Jurusan: {{ $alumni['status_jurusan'] ?? '-' }}
                        </div>
                    </div>

                    <div class="rek-score-stack">
                        <span class="rek-score-chip chip-cbf">
                            Sim {{ number_format((float) ($alumni['skor_kemiripan'] ?? 0), 3) }}
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="action-row">
            <a href="{{ route('hasil.prediksi') }}"
               class="btn btn-primary btn-sm action-link">
                Riwayat
            </a>
            <a href="{{ route('input.siswa') }}"
               class="btn btn-primary btn-sm action-link">
                Prediksi Baru
            </a>
        </div>

    </div>

    {{-- KOLOM KANAN --}}
    <div>
        <div class="card">
            <div class="card-title">Rekomendasi Universitas & Jurusan Kuliah</div>
            <div class="card-sub rekomendasi-sub">
                Berdasarkan data yang Anda masukkan, sistem menemukan kemiripan dengan data alumni jurusan <strong>{{ $detail->jurusan_smk }}</strong> yang melanjutkan kuliah
            </div>

            @if($isKuliah && $kualitas)
                <div class="formula-box">
                    Kualitas Rekomendasi: {{ $kualitas['status'] ?? '-' }}
                    @if(isset($kualitas['rata_rata_similarity']))
                        | Rata-rata Similarity: {{ number_format((float) $kualitas['rata_rata_similarity'], 3) }}
                    @endif
                    @if(isset($kualitas['threshold']))
                        | Threshold: {{ $kualitas['threshold'] ?? '-' }}
                    @endif
                </div>
            @endif

            @if($isKuliah && $rekUniv->isNotEmpty())
                @php
                    $top5 = $rekUniv->sortByDesc('final_score')->values()->take(5);
                @endphp

                @foreach($top5 as $i => $rek)
                <div class="rek-univ-block">
                    <div class="rek-univ-header">

                        <div class="rek-rank {{ $i === 0 ? 'rank-top' : '' }}">
                            {{ $i + 1 }}
                        </div>

                        <div class="rek-content">
                            <div class="rek-name">{{ $rek['universitas'] ?? '-' }}</div>
                            <div class="rek-jurusan">{{ $rek['program_studi'] ?? '-' }}</div>
                            <div class="rek-sub">
                                {{ $rek['jumlah_alumni_mirip'] ?? 0 }} alumni mirip mengambil program studi ini
                            </div>
                        </div>

                        <div class="rek-score-stack">
                            <span class="rek-score-chip chip-cbf">Sim {{ number_format((float) ($rek['similarity_score'] ?? 0), 3) }}</span>
                            <span class="rek-score-chip chip-hybrid">Final {{ number_format((float) ($rek['final_score'] ?? 0), 3) }}</span>
                        </div>

                    </div>
                </div>
                @endforeach

            @elseif($isKuliah)
                <div class="empty-recommendation muted">
                    Tidak ada rekomendasi universitas dan program studi yang dapat ditampilkan.
                </div>
            @else
                <div class="empty-recommendation">
                    <div class="empty-recommendation-title">Tidak Teridentifikasi Studi Lanjut</div>
                    <div class="empty-recommendation-desc">Rekomendasi hanya tersedia untuk siswa yang teridentifikasi studi lanjut.</div>
                </div>
            @endif
        </div>
    </div>

</div>

@endisset

{{-- ================================================================
     STATE B: DAFTAR RIWAYAT  →  /hasil-prediksi
     ================================================================ --}}
@isset($data)

<form method="GET" action="{{ route('hasil.prediksi') }}" class="filter-form">
    <select name="jurusan" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">Semua Jurusan</option>
        @foreach($jurusanList as $j)
            <option value="{{ $j }}" {{ request('jurusan') === $j ? 'selected' : '' }}>{{ $j }}</option>
        @endforeach
    </select>
    <select name="status" class="form-select filter-select" onchange="this.form.submit()">
        <option value="">Semua Status</option>
        <option value="1"
                {{ request('status') === '1' ? 'selected' : '' }}>
            Teridentifikasi Studi Lanjut
        </option>
        <option value="0"
                {{ request('status') === '0' ? 'selected' : '' }}>
            Tidak Teridentifikasi Studi Lanjut
        </option>
    </select>
    @if(request()->hasAny(['jurusan','status']))
        <a href="{{ route('hasil.prediksi') }}"
           class="reset-link">Reset</a>
    @endif
</form>

@if($data->count())

@php
    $allRowsData = $data->map(function($item) {
        return [
            'id'                  => $item->id,
            'nama_siswa'          => $item->nama_siswa ?? 'Siswa',
            'jurusan_smk'         => $item->jurusan_smk,
            'jurusan_smk_lengkap' => $item->jurusan_smk_lengkap ?? $item->jurusan_smk,
            'status_prediksi'     => (int) ($item->prediksi_rf ?? 0),
            'status_rf'           => $item->status_rf ?? '-',
            'probabilitas'        => $item->probabilitas_studi_lanjut !== null
                                        ? round((float) $item->probabilitas_studi_lanjut * 100, 1)
                                        : 0,
            'kategori'            => $item->kategori_probabilitas ?? '',
            'tanggal'             => $item->created_at->format('d/m/Y H:i'),
            'detail_url'          => route('hasil.prediksi.detail', $item->id),
        ];
    })->values();
@endphp

<div class="card">
    <div id="tabelWrap">
        <table class="prediksi-table">
            <thead>
                <tr class="table-head-row">
                    <th>#</th>
                    <th>Nama Siswa</th>
                    <th>Jurusan</th>
                    <th>Status</th>
                    <th>Probabilitas</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tabelBody"></tbody>
        </table>
    </div>

    <div class="pagination-row">
        <span id="paginfoText" class="pagination-info"></span>
        <div class="pagination-controls">
            <button onclick="changePage(-1)" id="btnPrev" class="pagination-btn">
                Prev
            </button>
            <span id="pageIndicator" class="page-indicator"></span>
            <button onclick="changePage(1)" id="btnNext" class="pagination-btn">
                Next
            </button>
        </div>
    </div>
</div>

<script>
const allRows    = @json($allRowsData);
const PER_PAGE   = 10;
let currentPage  = 1;
const totalPages = Math.max(Math.ceil(allRows.length / PER_PAGE), 1);

function renderTable() {
    const start = (currentPage - 1) * PER_PAGE;
    const rows  = allRows.slice(start, start + PER_PAGE);

    document.getElementById('tabelBody').innerHTML = rows.map((item, i) => `
        <tr class="table-body-row">
            <td class="table-index">${start + i + 1}</td>
            <td class="table-name">${item.nama_siswa}</td>
            <td>
                <span class="stat-badge" title="${item.jurusan_smk_lengkap}">${item.jurusan_smk}</span>
            </td>
            <td>
                ${item.status_prediksi === 1
                    ? '<span class="stat-badge badge-green">Teridentifikasi Studi Lanjut</span>'
                    : '<span class="stat-badge badge-amber">Tidak Teridentifikasi Studi Lanjut</span>'}
            </td>
            <td>
                ${item.probabilitas}%
                ${item.kategori ? `<span class="table-category">(${item.kategori})</span>` : ''}
            </td>
            <td class="table-date">${item.tanggal}</td>
            <td>
                <a href="${item.detail_url}" class="btn btn-primary btn-sm">Detail</a>
            </td>
        </tr>
    `).join('');

    document.getElementById('paginfoText').textContent =
        `Menampilkan ${start + 1}\u2013${Math.min(start + PER_PAGE, allRows.length)} dari ${allRows.length} data`;
    document.getElementById('pageIndicator').textContent =
        `Halaman ${currentPage} / ${totalPages}`;
    document.getElementById('btnPrev').style.opacity = currentPage === 1 ? '0.4' : '1';
    document.getElementById('btnNext').style.opacity = currentPage === totalPages ? '0.4' : '1';
    document.getElementById('btnPrev').disabled = currentPage === 1;
    document.getElementById('btnNext').disabled = currentPage === totalPages;
}

function changePage(dir) {
    const next = currentPage + dir;
    if (next < 1 || next > totalPages) return;
    currentPage = next;
    renderTable();
    document.getElementById('tabelWrap').scrollIntoView({ behavior:'smooth', block:'start' });
}

renderTable();
</script>

@else
<div id="hasil-placeholder" class="card card-placeholder">
    <div class="placeholder-icon">—</div>
    <div class="placeholder-title">Belum ada prediksi</div>
    <div class="placeholder-desc">Silakan input data siswa terlebih dahulu</div>
    <a href="{{ route('input.siswa') }}" class="btn btn-primary btn-sm">Input Data Siswa</a>
</div>
@endif

@endisset

@if(!isset($detail) && !isset($data))
<div id="hasil-placeholder" class="card card-placeholder">
    <div class="placeholder-icon">—</div>
    <div class="placeholder-title">Belum ada prediksi</div>
    <div class="placeholder-desc">Silakan input data siswa terlebih dahulu</div>
    <a href="{{ route('input.siswa') }}" class="btn btn-primary btn-sm">Input Data Siswa</a>
</div>
@endif

@endsection