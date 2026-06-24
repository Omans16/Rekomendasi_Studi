@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/siswa/hasil-prediksi.css') }}">
@endpush

@push('scripts')
    <script src="{{ asset('js/siswa/hasil-prediksi.js') }}" defer></script>
@endpush

@section('content')

<div class="hasil-prediksi-page">

    <div class="page-header">
        <h2>Hasil Rekomendasi Studi Lanjut</h2>
        <p>
            Halaman ini menampilkan hasil analisis data akademikmu, status potensi studi lanjut,
            serta rekomendasi universitas dan program studi yang paling sesuai berdasarkan kemiripan dengan data alumni.
        </p>
    </div>

    @isset($detail)

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @php
            $isKuliah = (int) ($detail->prediksi_rf ?? 0) === 1;

            $thresholdRF = $detail->threshold_rf !== null
                ? (float) $detail->threshold_rf
                : 0.32;

            $statusLabel = $isKuliah
                ? 'Memenuhi Batas Rekomendasi'
                : 'Belum Memenuhi Batas Rekomendasi';

            $statusDescHasil = $isKuliah
                ? 'Hasil analisis sudah melewati batas keputusan sistem, sehingga rekomendasi kampus dan program studi dapat ditampilkan.'
                : 'Hasil analisis belum melewati batas keputusan sistem, sehingga rekomendasi kampus dan program studi belum ditampilkan. Hasil ini bukan keputusan akhir, melainkan bahan pertimbangan awal.';

            $profilSiswa = collect($detail->profil_siswa ?? []);
            $profilMap = [];

            foreach ($profilSiswa as $profil) {
                $atribut = $profil['atribut'] ?? null;

                if ($atribut) {
                    $profilMap[$atribut] = $profil['nilai'] ?? null;
                }
            }

            $nilaiMax = $profilMap['Nilai Maksimum'] ?? null;
            $nilaiMin = $profilMap['Nilai Minimum'] ?? null;
            $stdNilai = $profilMap['Standar Deviasi Nilai'] ?? null;

            $rekUniv = collect($detail->rekomendasi_final ?? []);
            $alumniTerdekat = collect($detail->alumni_terdekat ?? []);
            $kualitas = $detail->kualitas_rekomendasi ?? null;

            $avgSim = $rekUniv->isNotEmpty()
                ? round($rekUniv->avg('similarity_score') * 100, 1)
                : 0;

            $avgFinal = $rekUniv->isNotEmpty()
                ? round($rekUniv->avg('final_score') * 100, 1)
                : 0;

            $statusTitle = $isKuliah
                ? 'Kamu Teridentifikasi Memiliki Potensi Studi Lanjut'
                : 'Kamu Belum Teridentifikasi untuk Studi Lanjut';

            $statusDesc = $isKuliah
                ? 'Berdasarkan data alumni, sistem menilai bahwa profil akademikmu memiliki pola yang mendukung peluang untuk melanjutkan ke perguruan tinggi.'
                : 'Berdasarkan data yang dimasukkan, sistem belum menemukan pola yang cukup kuat untuk merekomendasikan studi lanjut. Hasil ini bukan keputusan akhir, melainkan bahan pertimbangan awal.';

            $nextStepText = $isKuliah
                ? 'Gunakan rekomendasi di bawah ini sebagai bahan diskusi dengan guru BK dan orang tua. Pertimbangkan juga minat pribadi, biaya pendidikan, lokasi kampus, prospek jurusan, serta kesiapan untuk melanjutkan studi.'
                : 'Hasil ini bukan berarti kamu tidak bisa melanjutkan kuliah. Namun, sistem belum menemukan indikasi yang cukup kuat berdasarkan data yang tersedia. Kamu tetap disarankan berdiskusi dengan guru BK untuk menentukan pilihan terbaik setelah lulus, seperti bekerja, mengikuti magang, mengambil pelatihan atau sertifikasi, berwirausaha, maupun mempertimbangkan studi lanjut melalui jalur lain yang sesuai dengan minat dan kondisimu.';

            $similarityText = $isKuliah
                ? (
                    $avgSim > 0
                        ? 'Semakin tinggi nilai kemiripan, semakin mirip profil akademikmu dengan alumni yang pernah melanjutkan kuliah.'
                        : 'Rekomendasi berbasis kemiripan alumni belum tersedia karena sistem belum menemukan data alumni yang cukup relevan untuk dibandingkan.'
                )
                : 'Rekomendasi berbasis kemiripan alumni belum ditampilkan karena hasil prediksi belum memenuhi syarat untuk masuk ke tahap rekomendasi program studi.';
        @endphp

        <div class="result-header {{ $isKuliah ? 'result-kuliah' : 'result-tidak' }}">
            <div class="result-header-inner">
                <div class="result-status-badge {{ $isKuliah ? 'badge-kuliah' : 'badge-tidak' }}">
                    {{ $isKuliah ? 'Potensi Studi Lanjut Terdeteksi' : 'Belum Terdeteksi Studi Lanjut' }}
                </div>

                <h3>{{ $statusTitle }}</h3>
                <p>{{ $statusDesc }}</p>

                <div class="result-meta-row">
                    <span class="result-meta-item">
                        <span class="result-meta-label">Nama</span>
                        <span class="result-meta-val">{{ $detail->nama_siswa ?? 'Siswa' }}</span>
                    </span>

                    <span class="result-meta-item">
                        <span class="result-meta-label">Jurusan SMK</span>
                        <span class="result-meta-val">
                            <span class="stat-badge badge-blue" title="{{ $detail->jurusan_smk_lengkap ?? $detail->jurusan_smk }}">
                                {{ $detail->jurusan_smk }}
                            </span>
                        </span>
                    </span>

                    <span class="result-meta-item">
                        <span class="result-meta-label">Status</span>
                        <span class="result-meta-val">
                            <span class="stat-badge {{ $isKuliah ? 'badge-green' : 'badge-amber' }}">
                                {{ $statusLabel }}
                            </span>
                        </span>
                    </span>

                    <span class="result-meta-item">
                        <span class="result-meta-label">Nilai UKK</span>
                        <span class="result-meta-val">{{ $detail->ukk }}</span>
                    </span>
                </div>
            </div>
        </div>

        <div class="two-col">
            <div>
                <div class="card card-academic">
                    <div class="card-title">Data Akademik</div>

                    <div class="metric-row">
                        <span class="label">Nilai UKK</span>
                        <span class="value">{{ $detail->ukk }}</span>
                    </div>

                    <div class="metric-row">
                        <span class="label">Nilai Maks / Min</span>
                        <span class="value">{{ $nilaiMax ?? '—' }} / {{ $nilaiMin ?? '—' }}</span>
                    </div>

                    <div class="metric-row">
                        <span class="label">Std. Deviasi</span>
                        <span class="value">{{ $stdNilai ?? '—' }}</span>
                    </div>

                    @if($kualitas && isset($kualitas['rata_rata_similarity']))
                        <div class="metric-row">
                            <span class="label">Avg Neighbor Similarity</span>
                            <span class="value">{{ number_format((float) $kualitas['rata_rata_similarity'], 3) }}</span>
                        </div>
                    @endif
                </div>

                <div class="card card-summary">
                    <div class="card-title">Ringkasan Hasil</div>

                    <div class="decision-summary-box {{ $isKuliah ? 'decision-pass' : 'decision-hold' }}">
                        <div class="decision-summary-label">
                            Status Hasil
                        </div>

                        <div class="decision-summary-value">
                            {{ $statusLabel }}
                        </div>

                        <div class="decision-summary-desc">
                            {{ $statusDescHasil }}
                        </div>
                    </div>

                    @if($rekUniv->isNotEmpty())
                        <div class="score-bar-wrap">
                            <div class="score-bar-label">
                                <span>Kemiripan dengan Alumni</span>
                                <span>{{ $avgSim }}%</span>
                            </div>

                            <div class="score-bar-bg">
                                <div class="score-bar-fill fill-green" data-score="{{ $avgSim }}"></div>
                            </div>

                            <div class="score-note">
                                {{ $similarityText }}
                            </div>
                        </div>

                        <div class="score-bar-wrap">
                            <div class="score-bar-label">
                                <span>Kekuatan Rekomendasi</span>
                                <span>{{ $avgFinal }}%</span>
                            </div>

                            <div class="score-bar-bg">
                                <div class="score-bar-fill fill-purple" data-score="{{ $avgFinal }}"></div>
                            </div>

                            <div class="score-note">
                                Skor ini membantu mengurutkan rekomendasi kampus dan program studi dari yang paling relevan.
                            </div>
                        </div>
                    @endif
                </div>

                <div class="card card-interpretation">
                    <div class="card-title">Apa Arti Hasil Ini?</div>

                    <div class="interpretasi-text">
                        {{ $detail->narasi_rekomendasi ?? $detail->pesan ?? $statusDesc }}
                    </div>

                    <div class="student-advice-box">
                        <strong>Langkah berikutnya:</strong>
                        <span>{{ $nextStepText }}</span>
                    </div>
                </div>

                @if($isKuliah && $alumniTerdekat->isNotEmpty())
                    <div class="card card-alumni">
                        <div class="card-title">Alumni dengan Profil Paling Mirip</div>

                        @foreach($alumniTerdekat as $alumni)
                            <div class="rek-univ-block">
                                <div class="rek-univ-header">
                                    <div class="rek-rank {{ ($alumni['ranking'] ?? 0) === 1 ? 'rank-top' : '' }}">
                                        {{ $alumni['ranking'] ?? '-' }}
                                    </div>

                                    <div class="rek-content">
                                        <div class="rek-name">{{ $alumni['kode_alumni'] ?? 'Alumni' }}</div>
                                        <div class="rek-jurusan">
                                            {{ $alumni['universitas'] ?? '-' }} — {{ $alumni['program_studi'] ?? '-' }}
                                        </div>
                                        <div class="rek-sub">
                                            Jurusan SMK: {{ $alumni['jurusan_smk_alumni'] ?? '-' }}
                                            | Status Jurusan: {{ $alumni['status_jurusan'] ?? '-' }}
                                        </div>
                                    </div>

                                    <div class="rek-score-stack">
                                        <span class="rek-score-chip chip-cbf">
                                            Sim {{ number_format((float) ($alumni['skor_kemiripan'] ?? 0), 3) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <div class="card card-recommendation">
                    <div class="card-title">Rekomendasi Universitas & Jurusan Kuliah</div>

                    <div class="card-sub rekomendasi-sub">
                        @if($isKuliah)
                            Rekomendasi ini disusun dari data alumni yang memiliki profil akademik mirip denganmu.
                            Urutan pertama berarti pilihan tersebut paling sesuai berdasarkan pola data yang tersedia.
                        @else
                            Rekomendasi universitas dan program studi hanya ditampilkan jika hasil analisis sudah memenuhi batas rekomendasi sistem.
                        @endif
                    </div>

                    @if($isKuliah && $kualitas)
                        <div class="quality-box">
                            <div class="quality-label">Kualitas Rekomendasi</div>
                            <div class="quality-value">{{ $kualitas['status'] ?? '-' }}</div>
                            <div class="quality-desc">
                                Status ini menunjukkan apakah rekomendasi cukup relevan berdasarkan kemiripan profilmu dengan data alumni.
                            </div>
                        </div>
                    @endif

                    @if($isKuliah && $rekUniv->isNotEmpty())
                        @php
                            $top5 = $rekUniv->sortByDesc('final_score')->values()->take(5);
                        @endphp

                        @foreach($top5 as $i => $rek)
                            <div class="rek-univ-block">
                                <div class="rek-univ-header">
                                    <div class="rek-rank {{ $i === 0 ? 'rank-top' : '' }}">
                                        {{ $i + 1 }}
                                    </div>

                                    <div class="rek-content">
                                        <div class="rek-name">{{ $rek['universitas'] ?? '-' }}</div>
                                        <div class="rek-jurusan">{{ $rek['program_studi'] ?? '-' }}</div>
                                        <div class="rek-sub">
                                            {{ $rek['jumlah_alumni_mirip'] ?? 0 }} alumni mirip mengambil program studi ini
                                        </div>
                                    </div>

                                    <div class="rek-score-stack">
                                        <span class="rek-score-chip chip-cbf">
                                            Sim {{ number_format((float) ($rek['similarity_score'] ?? 0), 3) }}
                                        </span>
                                        <span class="rek-score-chip chip-hybrid">
                                            Final {{ number_format((float) ($rek['final_score'] ?? 0), 3) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @elseif($isKuliah)
                        <div class="empty-recommendation muted">
                            Tidak ada rekomendasi universitas dan program studi yang dapat ditampilkan.
                        </div>
                    @else
                        <div class="empty-recommendation">
                            <div class="empty-recommendation-title">Rekomendasi Belum Ditampilkan</div>
                            <div class="empty-recommendation-desc">
                                Sistem belum menampilkan rekomendasi kampus karena hasil saat ini belum memenuhi batas rekomendasi sistem.
                                Kamu tetap bisa berkonsultasi dengan guru BK untuk mempertimbangkan pilihan setelah lulus,
                                termasuk kuliah melalui jalur lain yang sesuai dengan minat dan kondisi pribadi.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="result-action-grid">
            <div class="action-row result-action-bottom">
                <a href="{{ route('siswa.hasil.prediksi') }}"
                   class="btn btn-primary btn-sm action-link">
                    Riwayat
                </a>

                <a href="{{ route('siswa.input.siswa') }}"
                   class="btn btn-primary btn-sm action-link">
                    Prediksi Baru
                </a>
            </div>

            <div class="result-action-spacer"></div>
        </div>
    @endisset

    @isset($data)

        <form method="GET" action="{{ route('siswa.hasil.prediksi') }}" class="filter-form">
            <select name="jurusan" class="form-select filter-select">
                <option value="">Semua Jurusan</option>

                @foreach($jurusanList as $j)
                    <option value="{{ $j }}" {{ request('jurusan') === $j ? 'selected' : '' }}>
                        {{ $j }}
                    </option>
                @endforeach
            </select>

            <select name="status" class="form-select filter-select">
                <option value="">Semua Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                    Memenuhi Batas Rekomendasi
                </option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                    Belum Memenuhi Batas Rekomendasi
                </option>
            </select>

            @if(request()->hasAny(['jurusan','status']))
                <a href="{{ route('siswa.hasil.prediksi') }}" class="reset-link">
                    Reset
                </a>
            @endif
        </form>

        @if($data->count())

            @php
                $allRowsData = $data->map(function($item) {
                    $rowIsKuliah = (int) ($item->prediksi_rf ?? 0) === 1;

                    return [
                        'id'                  => $item->id,
                        'nama_siswa'          => $item->nama_siswa ?? 'Siswa',
                        'jurusan_smk'         => $item->jurusan_smk,
                        'jurusan_smk_lengkap' => $item->jurusan_smk_lengkap ?? $item->jurusan_smk,
                        'status_prediksi'     => (int) ($item->prediksi_rf ?? 0),
                        'status_rf'           => $item->status_rf ?? '-',
                        'status_label'        => $rowIsKuliah
                                                    ? 'Memenuhi Batas Rekomendasi'
                                                    : 'Belum Memenuhi Batas Rekomendasi',
                        'tanggal'             => $item->created_at->format('d/m/Y H:i'),
                        'detail_url'          => route('siswa.hasil.prediksi.detail', $item->id),
                    ];
                })->values();
            @endphp

            <script type="application/json" id="hasilPrediksiRows">
                @json($allRowsData)
            </script>

            <div class="card card-history">
                <div id="tabelWrap" data-per-page="10">
                    <table class="prediksi-table">
                        <thead>
                            <tr class="table-head-row">
                                <th>#</th>
                                <th>Nama Siswa</th>
                                <th>Jurusan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="tabelBody"></tbody>
                    </table>
                </div>

                <div class="pagination-row">
                    <span id="paginfoText" class="pagination-info"></span>

                    <div class="pagination-controls">
                        <button type="button" id="btnPrev" class="pagination-btn" data-page-action="prev">
                            Prev
                        </button>

                        <span id="pageIndicator" class="page-indicator"></span>

                        <button type="button" id="btnNext" class="pagination-btn" data-page-action="next">
                            Next
                        </button>
                    </div>
                </div>
            </div>

        @else
            <div id="hasil-placeholder" class="card card-placeholder">
                <div class="placeholder-icon">—</div>
                <div class="placeholder-title">Belum ada prediksi</div>
                <div class="placeholder-desc">Silakan input data siswa terlebih dahulu</div>
                <a href="{{ route('siswa.input.siswa') }}" class="btn btn-primary btn-sm">
                    Input Data Siswa
                </a>
            </div>
        @endif

    @endisset

    @if(!isset($detail) && !isset($data))
        <div id="hasil-placeholder" class="card card-placeholder">
            <div class="placeholder-icon">—</div>
            <div class="placeholder-title">Belum ada prediksi</div>
            <div class="placeholder-desc">Silakan input data siswa terlebih dahulu</div>
            <a href="{{ route('siswa.input.siswa') }}" class="btn btn-primary btn-sm">
                Input Data Siswa
            </a>
        </div>
    @endif

</div>

@endsection