@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/upload-alumni.css') }}">
@endpush

@section('content')

<div class="page-header">
    <h2>Upload Data Alumni</h2>
    <p>Upload file Excel data tracer study alumni untuk training model</p>
</div>

{{-- Alert sukses/error --}}
@if(session('success'))
<div style="margin-bottom:16px;padding:12px 16px;background:#d1fae5;border-left:4px solid #10b981;border-radius:6px;color:#065f46;font-size:0.875rem">
    ✅ {{ session('success') }}
    @if(session('total_alumni'))
        — Total alumni sekarang: <b>{{ session('total_alumni') }}</b>
    @endif
</div>
@endif

@if(session('error'))
<div style="margin-bottom:16px;padding:12px 16px;background:#fee2e2;border-left:4px solid #ef4444;border-radius:6px;color:#991b1b;font-size:0.875rem">
    ⚠️ {{ session('error') }}
</div>
@endif

@if($errors->any())
<div style="margin-bottom:16px;padding:12px 16px;background:#fee2e2;border-left:4px solid #ef4444;border-radius:6px;color:#991b1b;font-size:0.875rem">
    <b>⚠️ Terdapat kesalahan:</b>
    <ul style="margin:6px 0 0 16px;padding:0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

@if(!$flaskOnline)
<div style="margin-bottom:16px;padding:10px 16px;background:#fef3c7;border-left:4px solid #f59e0b;border-radius:6px;color:#92400e;font-size:0.875rem">
    ⚠️ Layanan ML sedang tidak tersedia. Upload tidak dapat diproses saat ini.
</div>
@endif

<div class="two-col">

    {{-- ===== KOLOM KIRI ===== --}}
    <div>

        {{-- Form Upload --}}
        <div class="card">
            <div class="card-title">Upload File Excel (.xlsx / .csv)</div>

            <form action="{{ route('upload.alumni.proses') }}"
                  method="POST"
                  enctype="multipart/form-data"
                  id="formUpload">
                @csrf

                <div class="upload-zone" id="dropArea" onclick="document.getElementById('fileInput').click()">
                    <div class="upload-icon">📂</div>
                    <div class="upload-text" id="dropText">Klik untuk memilih file</div>
                    <div class="upload-hint">Format: .xlsx / .xls / .csv | Maks. 10MB</div>
                    <input type="file"
                           name="file_alumni"
                           id="fileInput"
                           accept=".xlsx,.xls,.csv"
                           style="display:none"
                           onchange="onFileSelected(this)">
                </div>

                {{-- Progress bar (tampil saat submit) --}}
                <div id="upload-progress" style="display:none;margin-top:12px">
                    <div id="upload-status" style="font-size:0.85rem;color:#6b7280;margin-bottom:6px">
                        Mengupload file...
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" id="upload-bar" style="width:0%"></div>
                    </div>
                </div>

                <button type="submit"
                        class="btn btn-primary"
                        id="btnUpload"
                        style="width:100%;margin-top:16px"
                        {{ !$flaskOnline ? 'disabled' : '' }}>
                    {{ $flaskOnline ? '⬆️ Upload & Perbarui Data Alumni' : '⚠️ ML Tidak Tersedia' }}
                </button>
            </form>
        </div>

        {{-- Format Kolom --}}
        <div class="card">
            <div class="card-title">Format Kolom yang Diperlukan</div>

            <div class="table-wrap">
                <table>
                    <tr><th>Kolom</th><th>Tipe</th><th>Keterangan</th></tr>
                    <tr>
                        <td>NISN</td>
                        <td><span class="tag tag-blue">string</span></td>
                        <td>ID unik siswa</td>
                    </tr>
                    <tr>
                        <td>nama</td>
                        <td><span class="tag tag-blue">string</span></td>
                        <td>Nama lengkap</td>
                    </tr>
                    <tr>
                        <td>jurusan_smk</td>
                        <td><span class="tag tag-purple">category</span></td>
                        <td>Jurusan SMK</td>
                    </tr>
                    <tr>
                        <td>nilai_pai ... nilai_ukk</td>
                        <td><span class="tag tag-green">float</span></td>
                        <td>Nilai 0-100</td>
                    </tr>
                    <tr>
                        <td>status_lanjut</td>
                        <td><span class="tag tag-amber">binary</span></td>
                        <td>Kuliah / Tidak</td>
                    </tr>
                    <tr>
                        <td>nama_univ</td>
                        <td><span class="tag tag-blue">string</span></td>
                        <td>Jika kuliah</td>
                    </tr>
                    <tr>
                        <td>jurusan_kuliah</td>
                        <td><span class="tag tag-blue">string</span></td>
                        <td>Jika kuliah</td>
                    </tr>
                    <tr>
                        <td>fakultas</td>
                        <td><span class="tag tag-blue">string</span></td>
                        <td>Jika kuliah</td>
                    </tr>
                </table>
            </div>
        </div>

    </div>

    {{-- ===== KOLOM KANAN ===== --}}
    <div>

        {{-- Preview Data Alumni (statis sebagai contoh format) --}}
<div class="card">
            <div class="card-title">Preview Data Alumni</div>

            <div class="table-wrap">
                <table>
                    <tr><th>Nama</th><th>Jur. SMK</th><th>Status</th><th>Universitas</th></tr>
                    @if(isset($previewData) && count($previewData) > 0)
                        @foreach($previewData as $alumni)
                        <tr>
                            <td>{{ $alumni['nama'] ?? '-' }}</td>
                            <td><span class="badge-blue stat-badge">{{ $alumni['jurusan_smk'] ?? '-' }}</span></td>
                            <td>
                                @if($alumni['status_lanjut'] == 'Kuliah')
                                    <span class="badge-green stat-badge">Kuliah</span>
                                @else
                                    <span class="badge-amber stat-badge">Tidak</span>
                                @endif
                            </td>
                            <td>{{ $alumni['nama_univ'] ?? '—' }}</td>
                        </tr>
                        @endforeach
                    @else
                        {{-- Fallback data statis --}}
                        <tr>
                            <td>Rudi Hartono</td>
                            <td><span class="badge-blue stat-badge">TM</span></td>
                            <td><span class="badge-green stat-badge">Kuliah</span></td>
                            <td>ITS</td>
                        </tr>
                        <tr>
                            <td>Siti Aminah</td>
                            <td><span class="badge-blue stat-badge">TKJ</span></td>
                            <td><span class="badge-green stat-badge">Kuliah</span></td>
                            <td>UB</td>
                        </tr>
                        <tr>
                            <td>Budi Santoso</td>
                            <td><span class="badge-blue stat-badge">TM</span></td>
                            <td><span class="badge-amber stat-badge">Tidak</span></td>
                            <td>—</td>
                        </tr>
                        <tr>
                            <td>Dewi Lestari</td>
                            <td><span class="badge-blue stat-badge">RPL</span></td>
                            <td><span class="badge-green stat-badge">Kuliah</span></td>
                            <td>UNAIR</td>
                        </tr>
                        <tr>
                            <td>Ahmad Fauzi</td>
                            <td><span class="badge-blue stat-badge">AK</span></td>
                            <td><span class="badge-green stat-badge">Kuliah</span></td>
                            <td>UNM</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div class="table-note">Tampilan {{ $previewCount ?? 5 }} baris pertama dari {{ $totalRows ?? 847 }} data</div>
        </div>

        {{-- Status Data Saat Ini (dinamis) --}}
        <div class="card">
            <div class="card-title">Status Data Saat Ini</div>

            <div class="metric-row">
                <span class="label">Total alumni (CBF)</span>
                <span class="value"><b>{{ $totalAlumni ?? '-' }}</b></span>
            </div>
            <div class="metric-row">
                <span class="label">Status Flask API</span>
                <span class="value">
                    @if($flaskOnline)
                        <span class="stat-badge badge-green">🟢 Online</span>
                    @else
                        <span class="stat-badge badge-amber">🔴 Offline</span>
                    @endif
                </span>
            </div>
            <div class="metric-row">
                <span class="label">Status Model</span>
                <span class="value"><span class="stat-badge badge-green">Terlatih</span></span>
            </div>

            <hr class="section-divider">

            <div style="font-size:0.82rem;color:#6b7280;line-height:1.6">
                📌 Setelah upload, data alumni baru langsung ditambahkan ke pool CBF
                tanpa perlu retrain model Random Forest.
                Model RF hanya perlu diretrain jika ada data historis baru yang signifikan.
            </div>
        </div>

    </div>

</div>

{{-- Notifikasi global --}}
<div id="notif" class="notif" style="display:none"></div>

@push('scripts')
<script>
// Tampilkan nama file saat dipilih
function onFileSelected(input) {
    const dropText = document.getElementById('dropText');
    if (input.files && input.files[0]) {
        const file = input.files[0];
        dropText.textContent = '📄 ' + file.name;
        document.getElementById('dropArea').style.borderColor = '#10b981';
    }
}

// Drag and drop
const dropArea = document.getElementById('dropArea');

['dragenter','dragover'].forEach(evt => {
    dropArea.addEventListener(evt, e => {
        e.preventDefault();
        dropArea.style.borderColor = '#3b82f6';
    });
});

['dragleave'].forEach(evt => {
    dropArea.addEventListener(evt, e => {
        e.preventDefault();
        dropArea.style.borderColor = '';
    });
});

dropArea.addEventListener('drop', e => {
    e.preventDefault();
    dropArea.style.borderColor = '';
    const file = e.dataTransfer.files[0];
    if (file) {
        document.getElementById('fileInput').files = e.dataTransfer.files;
        onFileSelected(document.getElementById('fileInput'));
    }
});

// Tampilkan progress bar saat form disubmit
document.getElementById('formUpload').addEventListener('submit', function () {
    const fileInput = document.getElementById('fileInput');
    if (!fileInput.files.length) return;

    const prog   = document.getElementById('upload-progress');
    const bar    = document.getElementById('upload-bar');
    const status = document.getElementById('upload-status');
    const btn    = document.getElementById('btnUpload');

    prog.style.display = 'block';
    btn.disabled = true;
    btn.textContent = 'Mengupload...';

    let pct = 0;
    const interval = setInterval(() => {
        pct = Math.min(pct + Math.random() * 12, 90);
        bar.style.width = pct.toFixed(0) + '%';

        if (pct < 40)      status.textContent = 'Membaca file...';
        else if (pct < 70) status.textContent = 'Memvalidasi kolom data...';
        else               status.textContent = 'Mengirim ke Flask API...';

        if (pct >= 90) clearInterval(interval);
    }, 200);
});
</script>
@endpush

@endsection