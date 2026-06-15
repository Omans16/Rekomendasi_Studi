@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/input-siswa.css') }}">
@endpush

@section('content')

<div class="page-header">
    <h2>Input Data Siswa</h2>
    <p>Masukkan data akademik siswa untuk prediksi dan rekomendasi studi lanjut</p>
</div>

{{-- Alert error dari session --}}
@if(session('error'))
<div class="alert alert-danger">
    {{ session('error') }}
</div>
@endif

{{-- Alert validasi dari Laravel --}}
@if($errors->any())
<div class="alert alert-danger">
    <b>Terdapat kesalahan input:</b>
    <ul class="alert-list">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Alert Flask offline --}}
@if(!$flaskOnline)
<div class="alert alert-warning">
    Layanan ML sedang tidak tersedia. Prediksi tidak dapat dijalankan saat ini.
</div>
@endif

<form action="{{ route('input.siswa.proses') }}" method="POST" id="formPrediksi">
@csrf

<div class="two-col">

    {{-- KOLOM KIRI --}}
    <div>

        <div class="card">
            <div class="card-title">Identitas Siswa</div>

            <div class="form-group">
                <label class="form-label">Nama Lengkap <span class="required">*</span></label>
                <input class="form-input"
                       type="text"
                       name="nama_siswa"
                       placeholder="Nama siswa"
                       value="{{ old('nama_siswa') }}"
                       required>
                @error('nama_siswa')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Jurusan SMK <span class="required">*</span></label>

                <select class="form-select" name="jurusan_smk" required>
                    <option value="">Pilih jurusan...</option>

                    @forelse($jurusanList as $jurusan)
                        @php
                            if (is_array($jurusan)) {
                                $valueJurusan = $jurusan['nama_lengkap'] ?? $jurusan['singkatan'] ?? '';
                                $labelJurusan = $jurusan['nama_lengkap'] ?? $jurusan['singkatan'] ?? '';
                                $singkatanJurusan = $jurusan['singkatan'] ?? null;
                                $jumlahAlumni = $jurusan['jumlah_alumni'] ?? null;
                            } else {
                                $valueJurusan = $jurusan;
                                $labelJurusan = $jurusan;
                                $singkatanJurusan = null;
                                $jumlahAlumni = null;
                            }
                        @endphp

                        <option value="{{ $valueJurusan }}"
                            {{ old('jurusan_smk') === $valueJurusan ? 'selected' : '' }}>
                            @if($singkatanJurusan && $singkatanJurusan !== $labelJurusan)
                                {{ $singkatanJurusan }} — {{ $labelJurusan }}
                            @else
                                {{ $labelJurusan }}
                            @endif

                            @if(!is_null($jumlahAlumni))
                                ({{ $jumlahAlumni }} alumni)
                            @endif
                        </option>
                    @empty
                        @php
                            $fallback = [
                                'Desain Pemodelan dan Informasi Bangunan',
                                'Teknik Jaringan Komputer dan Telekomunikasi',
                                'Teknik Elektronika',
                                'Teknik Instalasi Tenaga Listrik',
                                'Teknik Mesin',
                                'Teknik Konstruksi dan Perumahan',
                                'Agribisnis Perikanan',
                                'Agriteknologi Pengolahan Hasil Pertanian',
                                'Teknik Otomotif',
                                'Teknik Fabrikasi Logam dan Manufaktur',
                                'Nautika Kapal Penangkap Ikan',
                                'Teknika Kapal Penangkap Ikan',
                            ];
                        @endphp

                        @foreach($fallback as $jurusanFallback)
                            <option value="{{ $jurusanFallback }}" {{ old('jurusan_smk') === $jurusanFallback ? 'selected' : '' }}>
                                {{ $jurusanFallback }}
                            </option>
                        @endforeach
                    @endforelse
                </select>

                @error('jurusan_smk')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="card">
            <div class="card-title">Nilai Mata Pelajaran</div>

            <div class="two-col">

                <div class="form-group">
                    <label class="form-label">PAI <span class="required">*</span></label>
                    <input class="form-input score-input"
                           id="f-pai" name="rata_pai" type="number"
                           min="0" max="100" step="0.01"
                           placeholder="0-100"
                           value="{{ old('rata_pai') }}"
                           oninput="clamp(this); updateStats()"
                           required>
                    @error('rata_pai')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">PPKn <span class="required">*</span></label>
                    <input class="form-input score-input"
                           id="f-ppkn" name="rata_ppkn" type="number"
                           min="0" max="100" step="0.01"
                           placeholder="0-100"
                           value="{{ old('rata_ppkn') }}"
                           oninput="clamp(this); updateStats()"
                           required>
                    @error('rata_ppkn')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">B. Indonesia <span class="required">*</span></label>
                    <input class="form-input score-input"
                           id="f-bind" name="rata_ind" type="number"
                           min="0" max="100" step="0.01"
                           placeholder="0-100"
                           value="{{ old('rata_ind') }}"
                           oninput="clamp(this); updateStats()"
                           required>
                    @error('rata_ind')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Matematika <span class="required">*</span></label>
                    <input class="form-input score-input"
                           id="f-mtk" name="rata_mtk" type="number"
                           min="0" max="100" step="0.01"
                           placeholder="0-100"
                           value="{{ old('rata_mtk') }}"
                           oninput="clamp(this); updateStats()"
                           required>
                    @error('rata_mtk')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">B. Inggris <span class="required">*</span></label>
                    <input class="form-input score-input"
                           id="f-bing" name="rata_ing" type="number"
                           min="0" max="100" step="0.01"
                           placeholder="0-100"
                           value="{{ old('rata_ing') }}"
                           oninput="clamp(this); updateStats()"
                           required>
                    @error('rata_ing')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Nilai UKK <span class="required">*</span></label>
                    <input class="form-input score-input"
                           id="f-ukk" name="ukk" type="number"
                           min="0" max="100" step="0.01"
                           placeholder="0-100"
                           value="{{ old('ukk') }}"
                           oninput="clamp(this); updateStats()"
                           required>
                    @error('ukk')<div class="form-error">{{ $message }}</div>@enderror
                </div>

            </div>
        </div>

    </div>

    {{-- KOLOM KANAN --}}
    <div>

        <div class="card">
            <div class="card-title">Statistik Nilai (Auto-hitung)</div>

            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>Rata-rata nilai</span>
                    <span id="stat-mean">—</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-blue" id="bar-mean" style="width:0%"></div>
                </div>
            </div>

            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>Nilai maksimum (nilai_max)</span>
                    <span id="stat-max">—</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-green" id="bar-max" style="width:0%"></div>
                </div>
            </div>

            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>Nilai minimum (nilai_min)</span>
                    <span id="stat-min">—</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-amber" id="bar-min" style="width:0%"></div>
                </div>
            </div>

            <div class="score-bar-wrap">
                <div class="score-bar-label">
                    <span>Standar deviasi (std_nilai)</span>
                    <span id="stat-std">—</span>
                </div>
                <div class="score-bar-bg">
                    <div class="score-bar-fill fill-purple" id="bar-std" style="width:0%"></div>
                </div>
            </div>

            <hr class="section-divider">

            <div class="card-sub">
                Fitur agregat ini digunakan sebagai input tambahan model Random Forest untuk menangkap konsistensi akademik siswa. Dihitung otomatis oleh Flask dari 6 nilai (rata_pai, rata_ppkn, rata_ind, rata_mtk, rata_ing, UKK).
            </div>
        </div>

        <div class="card">
            <div class="card-title">Fitur yang Digunakan Model</div>

            <div class="tag-list">
                <span class="tag tag-blue">rata_pai</span>
                <span class="tag tag-blue">rata_ppkn</span>
                <span class="tag tag-blue">rata_ind</span>
                <span class="tag tag-blue">rata_mtk</span>
                <span class="tag tag-blue">rata_ing</span>
                <span class="tag tag-blue">UKK</span>
                <span class="tag tag-purple">nilai_max</span>
                <span class="tag tag-purple">nilai_min</span>
                <span class="tag tag-purple">std_nilai</span>
                <span class="tag tag-green">Jurusan_Smk (OHE)</span>
            </div>

            <hr class="section-divider">

            <div class="submit-wrap">
                <button type="submit"
                        class="btn btn-primary btn-block"
                        {{ !$flaskOnline ? 'disabled' : '' }}>
                    {{ $flaskOnline ? 'Jalankan Prediksi dan Rekomendasi' : ' ML Tidak Tersedia' }}
                </button>
            </div>
        </div>

    </div>

</div>
</form>

<script>
function clamp(input) {
    let v = parseFloat(input.value);
    if (isNaN(v)) return;
    if (v > 100) { input.value = 100; v = 100; }
    if (v < 0)   { input.value = 0;   v = 0; }
}

function updateStats() {
    const ids  = ['f-pai','f-ppkn','f-bind','f-mtk','f-bing','f-ukk'];
    const vals = ids.map(id => parseFloat(document.getElementById(id).value)).filter(v => !isNaN(v));

    if (!vals.length) {
        document.getElementById('stat-mean').textContent = '—';
        document.getElementById('stat-max').textContent  = '—';
        document.getElementById('stat-min').textContent  = '—';
        document.getElementById('stat-std').textContent  = '—';
        ['bar-mean','bar-max','bar-min','bar-std'].forEach(id => document.getElementById(id).style.width = '0%');
        return;
    }

    const mean = vals.reduce((a,b)=>a+b,0)/vals.length;
    const max  = Math.max(...vals);
    const min  = Math.min(...vals);
    const std  = Math.sqrt(vals.reduce((s,v)=>s+Math.pow(v-mean,2),0)/vals.length);

    document.getElementById('stat-mean').textContent = mean.toFixed(1);
    document.getElementById('stat-max').textContent  = max.toFixed(0);
    document.getElementById('stat-min').textContent  = min.toFixed(0);
    document.getElementById('stat-std').textContent  = std.toFixed(2);
    document.getElementById('bar-mean').style.width  = mean + '%';
    document.getElementById('bar-max').style.width   = max  + '%';
    document.getElementById('bar-min').style.width   = min  + '%';
    document.getElementById('bar-std').style.width   = Math.min(std * 5, 100) + '%';
}

document.addEventListener('DOMContentLoaded', updateStats);
</script>

@endsection