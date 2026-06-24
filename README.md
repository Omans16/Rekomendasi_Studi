# Sistem Rekomendasi Studi Lanjut Siswa SMK

Sistem Rekomendasi Studi Lanjut Siswa SMK adalah aplikasi berbasis web yang digunakan untuk membantu siswa kelas 12 dalam melihat potensi studi lanjut serta mendapatkan rekomendasi universitas dan program studi berdasarkan data akademik dan pola data alumni.

Project ini dikembangkan menggunakan Laravel sebagai aplikasi utama dan terintegrasi dengan layanan Machine Learning untuk proses prediksi dan rekomendasi.

---

## Deskripsi Singkat

Sistem ini dirancang untuk mendukung Guru BK dan siswa dalam proses pertimbangan studi lanjut. Data akademik siswa diproses oleh sistem untuk menentukan apakah siswa memenuhi batas rekomendasi studi lanjut. Jika hasil memenuhi batas rekomendasi, sistem akan menampilkan rekomendasi universitas dan program studi berdasarkan kemiripan dengan data alumni.

Sistem ini tidak menggantikan keputusan akhir siswa, orang tua, atau Guru BK. Hasil rekomendasi digunakan sebagai bahan pertimbangan awal dalam proses bimbingan karier dan studi lanjut.

---

## Fitur Utama

### Admin / Guru BK

* Login menggunakan akun admin atau Guru BK.
* Dashboard monitoring data prediksi dan rekomendasi.
* Input data siswa secara manual.
* Upload data siswa secara massal menggunakan file Excel atau CSV.
* Pembuatan akun siswa otomatis berdasarkan NISN saat upload data siswa.
* Manajemen akun untuk menambah akun Guru BK dan siswa.
* Melihat seluruh hasil prediksi siswa.
* Melihat detail hasil rekomendasi.
* Melihat informasi model dan evaluasi sistem.

### Siswa

* Login menggunakan NISN.
* Password awal menggunakan NISN.
* Popup saran mengganti password saat pertama kali login.
* Input data akademik untuk meminta rekomendasi.
* Melihat dashboard siswa.
* Melihat riwayat hasil rekomendasi.
* Melihat detail hasil analisis dan rekomendasi.
* Jika hasil belum memenuhi batas rekomendasi, sistem tidak menampilkan rekomendasi kampus dan memberikan arahan untuk berdiskusi dengan Guru BK.

---

## Metode Sistem

Sistem rekomendasi menggunakan pendekatan hybrid:

1. **Random Forest**

   * Digunakan untuk mengidentifikasi potensi studi lanjut siswa.
   * Output berupa status apakah siswa memenuhi batas rekomendasi atau belum.

2. **KNN Similarity**

   * Digunakan untuk mencari alumni dengan profil akademik yang mirip.
   * Digunakan hanya jika hasil Random Forest memenuhi batas rekomendasi.
   * Rekomendasi universitas dan program studi disusun berdasarkan kemiripan data siswa dengan data alumni.

---

## Alur Sistem

```text
Admin / Guru BK upload data siswa
        ↓
Sistem membaca NISN, nama, jurusan, dan nilai akademik
        ↓
Jika NISN belum memiliki akun, sistem membuat akun siswa otomatis
        ↓
Data dikirim ke layanan Machine Learning
        ↓
Random Forest menentukan status rekomendasi
        ↓
Jika memenuhi batas rekomendasi, KNN mencari alumni terdekat
        ↓
Sistem menyimpan hasil prediksi dan rekomendasi
        ↓
Siswa dapat login dan melihat hasilnya
```

---

## Teknologi yang Digunakan

* Laravel
* PHP
* MySQL
* Blade Template
* CSS
* JavaScript
* PhpSpreadsheet
* Flask API
* Random Forest
* KNN Similarity

Contoh jurusan yang digunakan:

```text
Agribisnis Perikanan
Agriteknologi Pengolahan Hasil Pertanian
Desain Pemodelan dan Informasi Bangunan
Nautika Kapal Penangkap Ikan
Teknik Elektronika
Teknik Fabrikasi Logam dan Manufaktur
Teknik Instalasi Tenaga Listrik
Teknik Jaringan Komputer dan Telekomunikasi
Teknik Konstruksi dan Perumahan
Teknik Mesin
Teknik Otomotif
Teknika Kapal Penangkap Ikan
```

Saat file siswa diupload:

* Sistem membaca NISN siswa.
* Jika NISN belum ada di tabel user, sistem membuat akun siswa otomatis.
* Username siswa adalah NISN.
* Password awal siswa adalah NISN.
* Hasil prediksi akan dihubungkan ke akun siswa berdasarkan NISN.

---

## Alur Login Siswa

Setelah data siswa diupload oleh Admin atau Guru BK:

```text
Username / NISN : NISN siswa
Password        : NISN siswa
```

Saat pertama kali login, siswa akan mendapatkan saran untuk mengganti password.

Siswa dapat memilih:

* Mengganti password baru.
* Tetap menggunakan password saat ini.

---

## Struktur Role

Sistem memiliki beberapa role pengguna:

### Admin

Memiliki akses ke seluruh fitur pengelolaan sistem.

### Guru BK

Memiliki akses untuk input data, upload data siswa, melihat hasil prediksi, dan melihat informasi model.

### Siswa

Memiliki akses ke dashboard siswa, input data akademik, riwayat hasil, dan detail rekomendasi.

---

## Route Utama

Beberapa route utama dalam sistem:

```text
/                         Landing Page
/login                    Halaman Login
/admin/dashboard          Dashboard Admin / Guru BK
/admin/input-siswa        Input Siswa oleh Admin / Guru BK
/admin/upload-siswa       Upload Data Siswa
/admin/akun               Manajemen Akun
/admin/hasil-prediksi     Hasil Prediksi Admin
/admin/info-model         Informasi Model

/siswa/dashboard          Dashboard Siswa
/siswa/input-siswa        Input Data Akademik Siswa
/siswa/hasil-prediksi     Riwayat Hasil Siswa
```

---

## Reset Database

Jika ingin mengulang pengujian dari awal:

```bash
php artisan migrate:fresh --seed
```

Jika terjadi masalah cache, jalankan:

```bash
php artisan optimize:clear
```

Jika session lama menyebabkan halaman login tidak muncul, hapus session Laravel:

```powershell
Remove-Item storage\framework\sessions\* -Force
```

---

## Catatan Penggunaan

* Data akademik digunakan sebagai input sistem rekomendasi.
* Hasil sistem bukan keputusan akhir.
* Siswa tetap disarankan berdiskusi dengan Guru BK dan orang tua.
* Rekomendasi hanya muncul jika hasil memenuhi batas rekomendasi sistem.
* Jika belum memenuhi batas rekomendasi, siswa tetap dapat mempertimbangkan jalur lain seperti bekerja, magang, pelatihan, sertifikasi, wirausaha, atau studi lanjut melalui jalur yang sesuai.

---

## Developer

Dibuat oleh:

**Muhamad Nur Rohman**
TRPL Politeknik Negeri Banyuwangi

GitHub:

```text
https://github.com/Omans16/Rekomendasi_Studi
```

---

## Lisensi

Project ini dibuat untuk kebutuhan pengembangan sistem rekomendasi studi lanjut siswa SMK.

Silakan sesuaikan bagian lisensi sesuai kebutuhan repository.
