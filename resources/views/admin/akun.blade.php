@extends('layouts.app')

@section('title', 'Manajemen Akun - Sistem Rekomendasi')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin/akun.css') }}">
@endpush

@section('content')
@php
    $hasValidationError = $errors->any();
    $initialRole = old('role', 'siswa');
@endphp

<div
    class="account-page js-account-page"
    data-has-errors="{{ $hasValidationError ? '1' : '0' }}"
    data-initial-role="{{ $initialRole }}"
>
    <div class="page-header">
        <div class="page-title">
            <h1>Manajemen Akun</h1>
            <p>Kelola akun Guru BK dan siswa tanpa membuka registrasi publik.</p>
        </div>

        <button type="button" class="btn-primary" data-account-modal-open>
            <i class="fa-solid fa-user-plus"></i>
            Tambah Akun
        </button>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="account-stats">
        <div class="card account-stat-card">
            <span>Total Akun</span>
            <strong>{{ $stats['total_akun'] }}</strong>
        </div>

        <div class="card account-stat-card">
            <span>Administrator</span>
            <strong>{{ $stats['total_admin'] }}</strong>
        </div>

        <div class="card account-stat-card">
            <span>Guru BK</span>
            <strong>{{ $stats['total_guru_bk'] }}</strong>
        </div>

        <div class="card account-stat-card">
            <span>Siswa</span>
            <strong>{{ $stats['total_siswa'] }}</strong>
        </div>
    </div>

    <div class="card account-card">
        <div class="card-header account-card-header">
            <div>
                <h2 class="card-title">Daftar Akun</h2>
                <p class="card-subtitle">
                    Akun siswa memakai NISN sebagai username. Akun Guru BK dapat memakai NIP atau username khusus.
                </p>
            </div>

            <form method="GET" action="{{ route('admin.akun') }}" class="account-filter">
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    class="form-control"
                    placeholder="Cari nama / NISN / username"
                >

                <select name="role" class="form-select">
                    <option value="">Semua Role</option>
                    <option value="admin" @selected($role === 'admin')>Admin</option>
                    <option value="guru_bk" @selected($role === 'guru_bk')>Guru BK</option>
                    <option value="siswa" @selected($role === 'siswa')>Siswa</option>
                </select>

                <button type="submit" class="btn-secondary">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    Filter
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table account-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>NISN / Username</th>
                        <th>Role</th>
                        <th>Kelas</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $item)
                        @php
                            $roleLabel = [
                                'admin' => 'Administrator',
                                'guru_bk' => 'Guru BK',
                                'siswa' => 'Siswa',
                            ][$item->role] ?? ucfirst($item->role);

                            $badgeClass = [
                                'admin' => 'badge-primary',
                                'guru_bk' => 'badge-success',
                                'siswa' => 'badge-primary',
                            ][$item->role] ?? 'badge-primary';
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong>
                            </td>
                            <td>{{ $item->nisn }}</td>
                            <td>
                                <span class="badge {{ $badgeClass }}">
                                    {{ $roleLabel }}
                                </span>
                            </td>
                            <td>{{ $item->kelas ?? '-' }}</td>
                            <td>{{ $item->created_at?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="account-empty">
                                Belum ada data akun yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="account-pagination">
                <div class="account-pagination-info">
                    Menampilkan
                    <strong>{{ $users->firstItem() }}</strong>
                    sampai
                    <strong>{{ $users->lastItem() }}</strong>
                    dari
                    <strong>{{ $users->total() }}</strong>
                    akun
                </div>

                <div class="account-pagination-actions">
                    @if ($users->onFirstPage())
                        <span class="account-page-btn disabled">
                            <i class="fa-solid fa-chevron-left"></i>
                            Sebelumnya
                        </span>
                    @else
                        <a href="{{ $users->previousPageUrl() }}" class="account-page-btn">
                            <i class="fa-solid fa-chevron-left"></i>
                            Sebelumnya
                        </a>
                    @endif

                    @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                        @if ($page === $users->currentPage())
                            <span class="account-page-number active">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="account-page-number">{{ $page }}</a>
                        @endif
                    @endforeach

                    @if ($users->hasMorePages())
                        <a href="{{ $users->nextPageUrl() }}" class="account-page-btn">
                            Berikutnya
                            <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    @else
                        <span class="account-page-btn disabled">
                            Berikutnya
                            <i class="fa-solid fa-chevron-right"></i>
                        </span>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

<div class="account-modal" id="accountModal" hidden>
    <div class="account-modal-backdrop" data-account-modal-close></div>

    <div class="account-modal-panel" role="dialog" aria-modal="true" aria-labelledby="accountModalTitle">
        <div class="account-modal-header">
            <div>
                <h2 id="accountModalTitle">Tambah Akun Baru</h2>
                <p>Pilih jenis akun, lalu isi data akun yang akan dibuat.</p>
            </div>

            <button type="button" class="account-modal-close" data-account-modal-close aria-label="Tutup modal">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        @if ($errors->any())
            <div class="account-modal-alert">
                <div class="account-modal-alert-icon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>

                <div>
                    <strong>Data akun belum valid.</strong>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.akun.simpan') }}" class="account-form">
            @csrf

            <input type="hidden" name="role" id="roleInput" value="{{ old('role', 'siswa') }}">

            <div class="form-group">
                <label>Jenis Akun</label>

                <div class="account-role-grid">
                    <button
                        type="button"
                        class="account-role-card"
                        id="roleSiswa"
                        data-role-option="siswa"
                    >
                        <i class="fa-solid fa-user-graduate"></i>
                        <span>Siswa</span>
                        <small>Akun siswa satu per satu</small>
                    </button>

                    <button
                        type="button"
                        class="account-role-card"
                        id="roleGuru"
                        data-role-option="guru_bk"
                    >
                        <i class="fa-solid fa-chalkboard-user"></i>
                        <span>Guru BK</span>
                        <small>Akun pengelola rekomendasi</small>
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    value="{{ old('name') }}"
                    class="form-control"
                    placeholder="Masukkan nama lengkap"
                    required
                >
            </div>

            <div class="form-group">
                <label for="nisn" id="usernameLabel">NISN</label>
                <input
                    type="text"
                    name="nisn"
                    id="nisn"
                    value="{{ old('nisn') }}"
                    class="form-control"
                    placeholder="Masukkan NISN / username"
                    required
                >
                <p class="form-hint" id="usernameHint">
                    Untuk siswa, gunakan NISN. Password awal otomatis memakai NISN jika password dikosongkan.
                </p>
            </div>

            <div class="form-group" id="kelasGroup">
                <label for="kelas">Kelas</label>
                <input
                    type="number"
                    name="kelas"
                    id="kelas"
                    value="{{ old('kelas', 12) }}"
                    class="form-control"
                    min="10"
                    max="13"
                    placeholder="Contoh: 12"
                >
            </div>

            <div class="form-group">
                <label for="password" id="passwordLabel">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Kosongkan untuk siswa agar otomatis memakai NISN"
                >
                <p class="form-hint" id="passwordHint">
                    Untuk siswa, jika password dikosongkan maka password awal sama dengan NISN.
                </p>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Konfirmasi Password</label>
                <input
                    type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="form-control"
                    placeholder="Ulangi password jika diisi"
                >
            </div>

            <div class="account-modal-actions">
                <button type="button" class="btn-outline" data-account-modal-close>
                    Batal
                </button>

                <button type="submit" class="btn-primary">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin/akun.js') }}" defer></script>
@endpush