<div class="sidebar">
    <h2 class="logo">Smkn 1 Glagah Banyuwangi</h2>

    <ul>
        <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}">
                <i class="fa-solid fa-chart-line"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('input.siswa') ? 'active' : '' }}">
            <a href="{{ route('input.siswa') }}">
                <i class="fa-solid fa-user-pen"></i>
                <span class="menu-text">Input Siswa</span>
            </a>
        </li>

        <li class="{{ request()->routeIs('hasil.prediksi') ? 'active' : '' }}">
            <a href="{{ route('hasil.prediksi') }}">
                <i class="fa-solid fa-chart-pie"></i>
                <span class="menu-text">Hasil Prediksi</span>
            </a>
        </li>

        {{-- <li class="{{ request()->routeIs('upload.alumni') ? 'active' : '' }}">
            <a href="{{ route('upload.alumni') }}">
                <i class="fa-solid fa-file-arrow-up"></i>
                <span class="menu-text">Upload Alumni</span>
            </a>
        </li> --}}

        <li class="{{ request()->routeIs('info.model') ? 'active' : '' }}">
            <a href="{{ route('info.model') }}">
                <i class="fa-solid fa-circle-info"></i>
                <span class="menu-text">Info Model</span>
            </a>
        </li>

        {{-- <li class="{{ request()->routeIs('preprocessing') ? 'active' : '' }}">
            <a href="{{ route('preprocessing') }}">
                <i class="fa-solid fa-gears"></i>
                <span class="menu-text">Preprocessing</span>
            </a>
        </li> --}}
    </ul>
</div>