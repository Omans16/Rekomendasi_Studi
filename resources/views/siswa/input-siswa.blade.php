@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/siswa/input-siswa.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/siswa/input-siswa.js') }}" defer></script>
@endpush

@section('content')

<div class="input-siswa-page">

    <div class="page-header">
        <h2>Isi Data Akademik untuk Rekomendasi Studi Lanjut</h2>
        <p>
            Masukkan nama, jurusan SMK, nilai rapor, dan nilai UKK.
            Setelah dikirim, sistem akan membantu menampilkan potensi studi lanjut serta rekomendasi kampus dan program studi yang sesuai.
        </p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

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

    @if(!$flaskOnline)
        <div class="alert alert-warning">
            Layanan ML sedang tidak tersedia. Prediksi tidak dapat dijalankan saat ini.
        </div>
    @endif

    <form action="{{ route('siswa.input.siswa.proses') }}" method="POST" id="formPrediksi">
        @csrf

        <div class="two-col">

            <div>
                <div class="card card-user">
                    <div class="card-title">Data Diri Siswa</div>

                    <div class="form-group">
                        <label class="form-label">
                            Nama Lengkap <span class="required">*</span>
                        </label>

                        <input
                            class="form-input"
                            type="text"
                            name="nama_siswa"
                            placeholder="Nama siswa"
                            value="{{ old('nama_siswa', auth()->user()->name ?? '') }}"
                            required
                        >

                        @error('nama_siswa')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            Jurusan SMK <span class="required">*</span>
                        </label>

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

                                <option value="{{ $valueJurusan }}" {{ old('jurusan_smk') === $valueJurusan ? 'selected' : '' }}>
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

                <div class="card card-score">
                    <div class="card-title">Nilai Rapor dan UKK</div>

                    <div class="two-col">
                        <div class="form-group score-field">
                            <label class="form-label">
                                PAI <span class="required">*</span>
                            </label>

                            <input
                                class="form-input score-input"
                                id="f-pai"
                                name="rata_pai"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0-100"
                                value="{{ old('rata_pai') }}"
                                required
                            >

                            @error('rata_pai')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group score-field">
                            <label class="form-label">
                                PPKn <span class="required">*</span>
                            </label>

                            <input
                                class="form-input score-input"
                                id="f-ppkn"
                                name="rata_ppkn"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0-100"
                                value="{{ old('rata_ppkn') }}"
                                required
                            >

                            @error('rata_ppkn')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group score-field">
                            <label class="form-label">
                                B. Indonesia <span class="required">*</span>
                            </label>

                            <input
                                class="form-input score-input"
                                id="f-bind"
                                name="rata_ind"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0-100"
                                value="{{ old('rata_ind') }}"
                                required
                            >

                            @error('rata_ind')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group score-field">
                            <label class="form-label">
                                Matematika <span class="required">*</span>
                            </label>

                            <input
                                class="form-input score-input"
                                id="f-mtk"
                                name="rata_mtk"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0-100"
                                value="{{ old('rata_mtk') }}"
                                required
                            >

                            @error('rata_mtk')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group score-field">
                            <label class="form-label">
                                B. Inggris <span class="required">*</span>
                            </label>

                            <input
                                class="form-input score-input"
                                id="f-bing"
                                name="rata_ing"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0-100"
                                value="{{ old('rata_ing') }}"
                                required
                            >

                            @error('rata_ing')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group score-field">
                            <label class="form-label">
                                Nilai UKK <span class="required">*</span>
                            </label>

                            <input
                                class="form-input score-input"
                                id="f-ukk"
                                name="ukk"
                                type="number"
                                min="0"
                                max="100"
                                step="0.01"
                                placeholder="0-100"
                                value="{{ old('ukk') }}"
                                required
                            >

                            @error('ukk')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <div class="card card-summary">
                    <div class="card-title">Ringkasan Nilai Otomatis</div>

                    <div class="score-bar-wrap">
                        <div class="score-bar-label">
                            <span>Rata-rata nilai</span>
                            <span id="stat-mean">—</span>
                        </div>

                        <div class="score-bar-bg">
                            <div class="score-bar-fill fill-blue" id="bar-mean"></div>
                        </div>
                    </div>

                    <div class="score-bar-wrap">
                        <div class="score-bar-label">
                            <span>Nilai maksimum (nilai_max)</span>
                            <span id="stat-max">—</span>
                        </div>

                        <div class="score-bar-bg">
                            <div class="score-bar-fill fill-green" id="bar-max"></div>
                        </div>
                    </div>

                    <div class="score-bar-wrap">
                        <div class="score-bar-label">
                            <span>Nilai minimum (nilai_min)</span>
                            <span id="stat-min">—</span>
                        </div>

                        <div class="score-bar-bg">
                            <div class="score-bar-fill fill-amber" id="bar-min"></div>
                        </div>
                    </div>

                    <div class="score-bar-wrap">
                        <div class="score-bar-label">
                            <span>Standar deviasi (std_nilai)</span>
                            <span id="stat-std">—</span>
                        </div>

                        <div class="score-bar-bg">
                            <div class="score-bar-fill fill-purple" id="bar-std"></div>
                        </div>
                    </div>

                    <hr class="section-divider">

                    <div class="card-sub">
                        Bagian ini dihitung otomatis dari nilai yang kamu masukkan. Rata-rata, nilai tertinggi, nilai terendah, dan konsistensi nilai akan membantu sistem membaca pola akademikmu.
                    </div>
                </div>

                <div class="card card-feature">
                    <div class="card-title">Data yang Dibaca Oleh Sistem</div>

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
                        <button
                            type="submit"
                            class="btn btn-primary btn-block"
                            {{ !$flaskOnline ? 'disabled' : '' }}
                        >
                            {{ $flaskOnline ? 'Jalankan Prediksi dan Rekomendasi' : 'ML Tidak Tersedia' }}
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>

@endsection