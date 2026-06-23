@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/upload-siswa.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/admin/upload-siswa.js') }}" defer></script>
@endpush

@section('content')

<div class="upload-siswa-page">

    <div class="page-header">
        <h2>Upload Data Siswa</h2>
        <p>
            Upload file Excel/CSV berisi data akademik siswa untuk menjalankan prediksi dan rekomendasi studi lanjut secara massal.
            Hasil disimpan berdasarkan NISN, sehingga tetap dapat terhubung ketika siswa memiliki akun.
        </p>

        <div class="required-columns">
            <div class="required-columns-title">Kolom yang diperlukan</div>

            <div class="required-columns-list">
                <span>nisn</span>
                <i>|</i>
                <span>nama_siswa</span>
                <i>|</i>
                <span>jurusan_smk</span>
                <i>|</i>
                <span>rata_pai</span>
                <i>|</i>
                <span>rata_ppkn</span>
                <i>|</i>
                <span>rata_ind</span>
                <i>|</i>
                <span>rata_mtk</span>
                <i>|</i>
                <span>rata_ing</span>
                <i>|</i>
                <span>ukk</span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            <strong>Berhasil.</strong>
            <span>{{ session('success') }}</span>

            @if(session('upload_summary'))
                @php($s = session('upload_summary'))

                <div class="alert-summary">
                    Total: {{ $s['total_rows'] ?? 0 }},
                    Berhasil: {{ $s['valid_rows'] ?? 0 }},
                    Gagal: {{ $s['failed_rows'] ?? 0 }},
                    Terhubung akun: {{ $s['linked_user_count'] ?? 0 }},
                    Belum punya akun: {{ $s['unlinked_user_count'] ?? 0 }}.
                </div>
            @endif
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <strong>Gagal.</strong>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Terdapat kesalahan input:</strong>

            <ul class="alert-list">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if(!$flaskOnline)
        <div class="alert alert-warning">
            <strong>Layanan ML tidak tersedia.</strong>
            <span>Upload belum dapat diproses karena Flask API sedang offline.</span>
        </div>
    @endif

    <div class="two-col upload-main-grid">

        <div>
            <div class="card">
                <div class="card-title">Upload File Excel/CSV</div>

                <div class="card-sub">
                    File akan divalidasi berdasarkan NISN, jurusan SMK, nilai rapor, dan nilai UKK.
                    Sistem tidak membuat akun siswa otomatis.
                </div>

                <form
                    action="{{ route('admin.upload.siswa.proses') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="formUploadSiswa"
                >
                    @csrf

                    <div class="upload-zone" id="dropArea">
                        <div class="upload-icon">
                            <i class="fa-solid fa-file-arrow-up"></i>
                        </div>

                        <div class="upload-text" id="dropText">
                            Klik atau tarik file ke sini
                        </div>

                        <div class="upload-hint" id="dropHint">
                            Format: .xlsx, .xls, .csv | Maksimal 10MB
                        </div>

                        <div class="selected-file-inline" id="selectedFileBox" hidden>
                            <div class="selected-file-inline-icon">
                                <i class="fa-solid fa-file-excel"></i>
                            </div>

                            <div class="selected-file-inline-info">
                                <strong id="selectedFileName">-</strong>
                                <span id="selectedFileSize">-</span>
                            </div>

                            <button
                                type="button"
                                class="selected-file-inline-remove"
                                id="removeFileBtn"
                                aria-label="Hapus file terpilih"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <input
                            type="file"
                            name="file_siswa"
                            id="fileInput"
                            accept=".xlsx,.xls,.csv,.txt"
                            hidden
                        >
                    </div>

                    <div id="upload-progress" class="upload-progress" hidden>
                        <div id="upload-status" class="upload-status">
                            Membaca file...
                        </div>

                        <div class="progress-bar">
                            <div class="progress-fill" id="upload-bar"></div>
                        </div>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary btn-block"
                        id="btnUpload"
                        {{ !$flaskOnline ? 'disabled' : '' }}
                    >
                        {{ $flaskOnline ? 'Proses Prediksi Massal' : 'ML Tidak Tersedia' }}
                    </button>
                </form>
            </div>
        </div>

        <div>
            <div class="card">
                <div class="card-title">Alur Pemrosesan</div>

                <div class="process-list">
                    <div class="process-item">
                        <span class="process-num">1</span>
                        <div>
                            <strong>Validasi File</strong>
                            <p>Sistem membaca file Excel/CSV dan memeriksa kelengkapan kolom.</p>
                        </div>
                    </div>

                    <div class="process-item">
                        <span class="process-num">2</span>
                        <div>
                            <strong>Pencocokan NISN</strong>
                            <p>Jika akun siswa sudah ada, hasil dikaitkan ke akun. Jika belum ada, hasil tetap disimpan berdasarkan NISN.</p>
                        </div>
                    </div>

                    <div class="process-item">
                        <span class="process-num">3</span>
                        <div>
                            <strong>Prediksi Random Forest</strong>
                            <p>Sistem menghitung potensi studi lanjut berdasarkan data akademik siswa.</p>
                        </div>
                    </div>

                    <div class="process-item">
                        <span class="process-num">4</span>
                        <div>
                            <strong>Rekomendasi KNN</strong>
                            <p>Jika siswa teridentifikasi studi lanjut, sistem menampilkan rekomendasi universitas dan program studi.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="upload-bottom-grid">

        <div class="card">
            <div class="card-title">Riwayat Upload</div>

            @if($recentBatches->count())
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>File</th>
                                <th>Berhasil</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($recentBatches as $batch)
                                <tr>
                                    <td data-label="File">
                                        {{ $batch->original_filename }}
                                    </td>

                                    <td data-label="Berhasil">
                                        {{ $batch->valid_rows }}/{{ $batch->total_rows }}
                                    </td>

                                    <td data-label="Status">
                                        @if($batch->status === 'completed')
                                            <span class="stat-badge badge-green">Selesai</span>
                                        @elseif($batch->status === 'completed_with_errors')
                                            <span class="stat-badge badge-amber">Catatan</span>
                                        @elseif($batch->status === 'failed')
                                            <span class="stat-badge badge-red">Gagal</span>
                                        @else
                                            <span class="stat-badge badge-blue">Proses</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="empty-box small">
                    <div class="empty-desc">Belum ada riwayat upload.</div>
                </div>
            @endif
        </div>

        <div class="card">
            <div class="card-title">Status Upload Terakhir</div>

            @if($lastBatch)
                <div class="metric-row">
                    <span class="label">File</span>
                    <span class="value">{{ $lastBatch->original_filename ?? '-' }}</span>
                </div>

                <div class="metric-row">
                    <span class="label">Total Baris</span>
                    <span class="value">{{ $lastBatch->total_rows }}</span>
                </div>

                <div class="metric-row">
                    <span class="label">Berhasil</span>
                    <span class="value">{{ $lastBatch->valid_rows }}</span>
                </div>

                <div class="metric-row">
                    <span class="label">Gagal</span>
                    <span class="value">{{ $lastBatch->failed_rows }}</span>
                </div>

                <div class="metric-row">
                    <span class="label">Terhubung Akun</span>
                    <span class="value">{{ $lastBatch->linked_user_count }}</span>
                </div>

                <div class="metric-row">
                    <span class="label">Belum Punya Akun</span>
                    <span class="value">{{ $lastBatch->unlinked_user_count }}</span>
                </div>

                <div class="metric-row">
                    <span class="label">Status</span>
                    <span class="value">
                        @if($lastBatch->status === 'completed')
                            <span class="stat-badge badge-green">Selesai</span>
                        @elseif($lastBatch->status === 'completed_with_errors')
                            <span class="stat-badge badge-amber">Selesai dengan Catatan</span>
                        @elseif($lastBatch->status === 'failed')
                            <span class="stat-badge badge-red">Gagal</span>
                        @else
                            <span class="stat-badge badge-blue">Diproses</span>
                        @endif
                    </span>
                </div>

                <a href="{{ route('admin.hasil.prediksi') }}" class="btn btn-primary btn-block mt-16">
                    Lihat Hasil Prediksi
                </a>
            @else
                <div class="empty-box">
                    <div class="empty-title">Belum ada upload</div>
                    <div class="empty-desc">
                        Upload file data siswa untuk menjalankan prediksi massal.
                    </div>
                </div>
            @endif
        </div>

    </div>

</div>

@endsection