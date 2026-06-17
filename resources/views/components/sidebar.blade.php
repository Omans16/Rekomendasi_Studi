@php
    $role = auth()->check() ? auth()->user()->role : null;
    $routePrefix = $role === 'siswa' ? 'siswa.' : 'admin.';
    $isAdminArea = in_array($role, ['admin', 'guru_bk']);
@endphp

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('images/logos.png') }}" alt="Logo SMKN 1 Glagah">
        </div>

        <div class="brand-text">
            <h2 class="logo">SMKN 1 GLAGAH</h2>
            <span>Sistem Rekomendasi</span>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="{{ request()->routeIs($routePrefix . 'dashboard') ? 'active' : '' }}">
            <a href="{{ route($routePrefix . 'dashboard') }}">
                <i class="fa-solid fa-chart-line"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="{{ request()->routeIs($routePrefix . 'input.siswa') ? 'active' : '' }}">
            <a href="{{ route($routePrefix . 'input.siswa') }}">
                <i class="fa-solid fa-user-pen"></i>
                <span class="menu-text">Input Siswa</span>
            </a>
        </li>

        <li class="{{ request()->routeIs($routePrefix . 'hasil.prediksi') || request()->routeIs($routePrefix . 'hasil.prediksi.detail') ? 'active' : '' }}">
            <a href="{{ route($routePrefix . 'hasil.prediksi') }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="menu-text">Hasil Prediksi</span>
            </a>
        </li>

        @if($isAdminArea)
            <li class="{{ request()->routeIs('admin.info.model') ? 'active' : '' }}">
                <a href="{{ route('admin.info.model') }}">
                    <i class="fa-solid fa-circle-info"></i>
                    <span class="menu-text">Info Model</span>
                </a>
            </li>
        @endif

        @if(in_array(auth()->user()->role, ['admin', 'guru_bk']))
            <li class="{{ request()->routeIs('admin.upload.siswa') ? 'active' : '' }}">
                <a href="{{ route('admin.upload.siswa') }}">
                    <i class="fa-solid fa-file-arrow-up"></i>
                    <span class="menu-text">Upload Data Siswa</span>
                </a>
            </li>
        @endif
    </ul>
</div>