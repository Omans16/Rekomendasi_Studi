@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/siswa/dashboard.css') }}">
@endpush

@push('scripts')
<script src="{{ asset('js/siswa/password-suggestion.js') }}" defer></script>
@endpush

@section('content')
@php
    $dbStats = $dbStats ?? [];
    $flaskOnline = $flaskOnline ?? false;
    $prediksiTerakhir = $dbStats['prediksi_terakhir'] ?? collect();

    $totalPrediksi = $dbStats['total_prediksi'] ?? 0;
    $totalKuliah = $dbStats['total_kuliah'] ?? 0;
    $totalTidak = $dbStats['total_tidak'] ?? 0;

    $showPasswordSuggestion = $showPasswordSuggestion ?? false;
    $hasPasswordErrors = $errors->has('password');
    $openPasswordModal = $showPasswordSuggestion || session('show_password_modal') || $hasPasswordErrors;
@endphp

<div class="siswa-dashboard-page">

    <div class="siswa-hero">
        <div class="siswa-hero-content">
            <div class="siswa-eyebrow">Dashboard Siswa</div>

            <h2>Halo, {{ auth()->user()->name ?? 'Siswa' }}</h2>

            <p>
                Gunakan halaman ini untuk meminta rekomendasi studi lanjut berdasarkan data akademikmu.
                Sistem akan membantu menampilkan status hasil dan rekomendasi universitas atau program studi jika hasil memenuhi batas rekomendasi.
            </p>

            <div class="siswa-hero-actions">
                <a href="{{ route('siswa.input.siswa') }}" class="btn-dashboard-primary">
                    Minta Rekomendasi Studi Lanjut
                </a>

                @if($totalPrediksi > 0)
                    <a href="{{ route('siswa.hasil.prediksi') }}" class="btn-dashboard-secondary">
                        Lihat Riwayat Saya
                    </a>
                @endif
            </div>
        </div>

        <div class="siswa-hero-card">
            <div class="hero-card-label">Status Sistem</div>

            @if($flaskOnline)
                <div class="hero-card-value hero-online">Aktif</div>
                <div class="hero-card-desc">Rekomendasi dapat dijalankan saat ini.</div>
            @else
                <div class="hero-card-value hero-offline">Tidak Aktif</div>
                <div class="hero-card-desc">Layanan rekomendasi sedang tidak tersedia.</div>
            @endif
        </div>
    </div>

    @if(!$flaskOnline)
        <div class="alert-ml-offline">
            Layanan rekomendasi sedang tidak aktif. Kamu masih dapat melihat riwayat hasil sebelumnya, tetapi belum dapat menjalankan prediksi baru.
        </div>
    @endif

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Pengajuan Saya</div>
            <div class="stat-value">{{ $totalPrediksi }}</div>
            <div class="stat-sub">Jumlah data rekomendasi yang pernah kamu ajukan.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Memenuhi Batas Rekomendasi</div>
            <div class="stat-value">{{ $totalKuliah }}</div>
            <div class="stat-sub">Hasil yang dapat menampilkan rekomendasi kampus dan program studi.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Belum Memenuhi Batas</div>
            <div class="stat-value">{{ $totalTidak }}</div>
            <div class="stat-sub">Hasil yang belum menampilkan rekomendasi dan perlu didiskusikan kembali.</div>
        </div>
    </div>

    @if(!empty($prediksiTerakhir) && $prediksiTerakhir->count())
        <div class="card dashboard-card">
            <div class="section-header">
                <div>
                    <div class="card-title">Hasil Rekomendasi Terakhir</div>
                    <div class="card-sub">Berikut riwayat hasil terbaru milikmu.</div>
                </div>

                <a href="{{ route('siswa.hasil.prediksi') }}" class="card-link">
                    Lihat Semua
                </a>
            </div>

            <div class="table-responsive">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Jurusan SMK</th>
                            <th>Status</th>
                            <th>Waktu</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($prediksiTerakhir as $item)
                            @php
                                $prediksiRf = (int) ($item->prediksi_rf ?? 0);

                                $statusLabel = $prediksiRf === 1
                                    ? 'Memenuhi Batas Rekomendasi'
                                    : 'Belum Memenuhi Batas Rekomendasi';
                            @endphp

                            <tr>
                                <td class="td-strong" data-label="Nama">
                                    {{ $item->nama_siswa ?? auth()->user()->name ?? 'Siswa' }}
                                </td>

                                <td data-label="Jurusan SMK">
                                    <span class="stat-badge badge-blue" title="{{ $item->jurusan_smk_lengkap ?? $item->jurusan_smk }}">
                                        {{ $item->jurusan_smk }}
                                    </span>
                                </td>

                                <td data-label="Status">
                                    <span class="stat-badge {{ $prediksiRf === 1 ? 'badge-green' : 'badge-amber' }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="text-muted-small" data-label="Waktu">
                                    {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                </td>

                                <td data-label="Aksi">
                                    <a href="{{ route('siswa.hasil.prediksi.detail', $item->id) }}" class="btn btn-primary btn-sm">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="empty-dashboard-card">
            <div class="empty-title">Belum Ada Hasil Rekomendasi</div>

            <div class="empty-desc">
                Kamu belum pernah mengisi data akademik. Mulai dengan memasukkan nilai dan jurusan SMK untuk mendapatkan rekomendasi studi lanjut.
            </div>

            <a href="{{ route('siswa.input.siswa') }}" class="btn-dashboard-primary">
                Minta Rekomendasi Sekarang
            </a>
        </div>
    @endif

    <div class="info-grid">
        <div class="card dashboard-card">
            <div class="card-title">Cara Menggunakan Sistem</div>

            <div class="step-list">
                <div class="step-item">
                    <div class="step-number">1</div>
                    <div>
                        <div class="step-title">Isi data akademik</div>
                        <div class="step-desc">Masukkan jurusan SMK dan nilai mata pelajaran yang diminta.</div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">2</div>
                    <div>
                        <div class="step-title">Sistem memproses data</div>
                        <div class="step-desc">Model akan membaca pola nilai dan membandingkan dengan data alumni jika hasil memenuhi batas rekomendasi.</div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div>
                        <div class="step-title">Lihat hasil rekomendasi</div>
                        <div class="step-desc">Kamu dapat melihat status hasil dan rekomendasi kampus jika hasil memenuhi batas rekomendasi.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dashboard-card">
            <div class="card-title">Catatan untuk Siswa</div>

            <div class="student-note">
                Hasil sistem digunakan sebagai bahan pertimbangan awal. Keputusan akhir tetap perlu disesuaikan dengan minat, kemampuan, biaya, lokasi kampus, dan arahan dari guru BK atau orang tua.
            </div>

            <a href="{{ route('siswa.input.siswa') }}" class="note-link">
                Mulai minta rekomendasi
            </a>
        </div>
    </div>

</div>

@if ($openPasswordModal)
    <div
        class="password-suggestion-modal"
        id="passwordSuggestionModal"
        data-open="1"
    >
        <div class="password-suggestion-backdrop"></div>

        <div class="password-suggestion-panel" role="dialog" aria-modal="true" aria-labelledby="passwordSuggestionTitle">
            <div class="password-suggestion-header">
                <div class="password-suggestion-icon">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>

                <div>
                    <h2 id="passwordSuggestionTitle">Amankan Akun Kamu</h2>
                    <p>
                        Akun kamu masih menggunakan password awal. Untuk keamanan, sebaiknya ganti password agar tidak mudah digunakan orang lain.
                    </p>
                </div>
            </div>

            @if ($errors->has('password'))
                <div class="password-suggestion-alert">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Password belum valid.</strong>
                        <ul>
                            @foreach ($errors->get('password') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('siswa.password.update') }}" class="password-suggestion-form">
                @csrf

                <div class="form-group">
                    <label for="password">Password Baru</label>
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Masukkan password baru"
                        autocomplete="new-password"
                    >
                    <p class="form-hint">
                        Minimal 8 karakter dan jangan sama dengan NISN.
                    </p>
                </div>

                <div class="form-group">
                    <label for="password_confirmation">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="form-control"
                        placeholder="Ulangi password baru"
                        autocomplete="new-password"
                    >
                </div>

                <div class="password-suggestion-actions">
                    <button type="submit" class="btn-dashboard-primary">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan Password Baru
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('siswa.password.keep') }}" class="password-suggestion-keep-form">
                @csrf

                <button type="submit" class="password-suggestion-skip">
                    Gunakan password saat ini
                </button>
            </form>

            <p class="password-suggestion-note">
                Jika kamu memilih tetap menggunakan password saat ini, kamu tetap bisa masuk seperti biasa. Namun, akun lebih aman jika password diganti.
            </p>
        </div>
    </div>
@endif
@endsection