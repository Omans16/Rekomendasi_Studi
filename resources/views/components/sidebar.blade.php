@php
    $user = auth()->user();
    $role = $user?->role;

    $routePrefix = $role === 'siswa' ? 'siswa.' : 'admin.';
    $isAdminArea = in_array($role, ['admin', 'guru_bk']);

    $menus = [
        [
            'label' => 'Dashboard',
            'icon' => 'fa-solid fa-chart-line',
            'route' => $routePrefix . 'dashboard',
            'active' => [$routePrefix . 'dashboard'],
            'show' => true,
        ],
        [
            'label' => 'Input Siswa',
            'icon' => 'fa-solid fa-user-pen',
            'route' => $routePrefix . 'input.siswa',
            'active' => [$routePrefix . 'input.siswa'],
            'show' => true,
        ],
        [
            'label' => 'Hasil Prediksi',
            'icon' => 'fa-solid fa-chart-pie',
            'route' => $routePrefix . 'hasil.prediksi',
            'active' => [
                $routePrefix . 'hasil.prediksi',
                $routePrefix . 'hasil.prediksi.detail',
            ],
            'show' => true,
        ],
        [
            'label' => 'Info Model',
            'icon' => 'fa-solid fa-circle-info',
            'route' => 'admin.info.model',
            'active' => ['admin.info.model'],
            'show' => $isAdminArea,
        ],
        [
            'label' => 'Upload Data Siswa',
            'icon' => 'fa-solid fa-file-arrow-up',
            'route' => 'admin.upload.siswa',
            'active' => ['admin.upload.siswa'],
            'show' => $isAdminArea,
        ],
    ];
@endphp

<aside class="sidebar" id="app-sidebar" aria-label="Sidebar navigasi">
    <div class="sidebar-brand">
        <div class="brand-icon">
            <img src="{{ asset('images/logos.png') }}" alt="Logo SMKN 1 Glagah">
        </div>

        <div class="brand-text">
            <h2 class="logo">SMKN 1 GLAGAH</h2>
            <span>Sistem Rekomendasi</span>
        </div>
    </div>

    <nav class="sidebar-nav" aria-label="Menu utama">
        <ul class="sidebar-menu">
            @foreach ($menus as $menu)
                @continue(! $menu['show'])

                <li @class([
                    'active' => request()->routeIs(...$menu['active'])
                ])>
                    <a href="{{ route($menu['route']) }}">
                        <i class="{{ $menu['icon'] }}"></i>
                        <span class="menu-text">{{ $menu['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</aside>