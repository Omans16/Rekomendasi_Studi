@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/siswa/dashboard.css') }}">
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.0/dist/apexcharts.min.js"></script>
@endpush

@section('content')
@php
    $dbStats = $dbStats ?? [];
    $flaskOnline = $flaskOnline ?? false;
    $prediksiTerakhir = $dbStats['prediksi_terakhir'] ?? collect();

    $totalPrediksi = $dbStats['total_prediksi'] ?? 0;
    $totalKuliah = $dbStats['total_kuliah'] ?? 0;
    $totalTidak = $dbStats['total_tidak'] ?? 0;
@endphp

<div class="siswa-dashboard-page">

    <div class="siswa-hero">
        <div class="siswa-hero-content">
            <div class="siswa-eyebrow">Dashboard Siswa</div>
            <h2>Halo, {{ auth()->user()->name ?? 'Siswa' }}</h2>
            <p>
                Gunakan halaman ini untuk meminta rekomendasi studi lanjut berdasarkan data akademikmu.
                Sistem akan membantu menampilkan potensi studi lanjut dan pilihan universitas atau program studi yang relevan.
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
            <div class="stat-sub">Jumlah data yang pernah kamu input.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Teridentifikasi Studi Lanjut</div>
            <div class="stat-value">{{ $totalKuliah }}</div>
            <div class="stat-sub">Hasil yang menunjukkan potensi lanjut kuliah.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Belum Teridentifikasi</div>
            <div class="stat-value">{{ $totalTidak }}</div>
            <div class="stat-sub">Hasil yang perlu dipertimbangkan kembali.</div>
        </div>
    </div>

    @if(!empty($prediksiTerakhir) && $prediksiTerakhir->count())
        <div class="card dashboard-card">
            <div class="section-header">
                <div>
                    <div class="card-title">Hasil Rekomendasi Terakhir</div>
                    <div class="card-sub">
                        Berikut riwayat hasil terbaru milikmu.
                    </div>
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
                                <td class="td-strong">{{ $item->nama_siswa ?? auth()->user()->name ?? 'Siswa' }}</td>
                                <td>
                                    <span class="stat-badge badge-blue" title="{{ $item->jurusan_smk_lengkap ?? $item->jurusan_smk }}">
                                        {{ $item->jurusan_smk }}
                                    </span>
                                </td>
                                <td>
                                    @if($prediksiRf === 1)
                                        <span class="stat-badge badge-green">Teridentifikasi Studi Lanjut</span>
                                    @else
                                        <span class="stat-badge badge-amber">Belum Teridentifikasi Studi Lanjut</span>
                                    @endif
                                </td>
                                <td>{{ $probabilitas }}%</td>
                                <td class="text-muted-small">
                                    {{ $item->created_at ? $item->created_at->diffForHumans() : '-' }}
                                </td>
                                <td>
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
                        <div class="step-desc">Model akan membaca pola nilai dan membandingkan dengan data alumni.</div>
                    </div>
                </div>

                <div class="step-item">
                    <div class="step-number">3</div>
                    <div>
                        <div class="step-title">Lihat hasil rekomendasi</div>
                        <div class="step-desc">Kamu dapat melihat status potensi studi lanjut dan rekomendasi yang sesuai.</div>
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
@endsection