# Katalog UMKM Nusantara

Platform pendaftaran dan katalog produk UMKM: pemilik usaha mendaftar, mengisi profil usaha,
dan mengunggah produk. Admin memverifikasi. Produk yang disetujui otomatis tampil di halaman
publik dan bisa dipesan pengunjung lewat WhatsApp.

Dibuat dengan PHP native + MySQL + Bootstrap 5 tanpa framework, supaya mudah dibaca dan
dikembangkan mahasiswa.

---

## 1. Teknologi

| Bagian   | Teknologi                                              |
|----------|--------------------------------------------------------|
| Frontend | HTML, CSS, JavaScript, Bootstrap 5.3, Bootstrap Icons  |
| Backend  | PHP 8 (PDO, prepared statement)                         |
| Database | MySQL / MariaDB                                         |
| Upload   | Folder `uploads/` dengan validasi tipe dan ukuran file  |

---

## 2. Instalasi di komputer sendiri (XAMPP)

1. **Salin project.** Letakkan folder `katalog-umkm` di dalam `C:\xampp\htdocs\`
   (Laragon: `C:\laragon\www\`, MAMP: folder `htdocs`).
2. **Jalankan Apache dan MySQL** lewat XAMPP Control Panel.
3. **Buat database.** Buka <http://localhost/phpmyadmin> → tab **Import** → pilih file
   `database/umkm_db.sql` → **Go**. Database `umkm_db` beserta tabel dan data contoh akan dibuat.
4. **Sesuaikan koneksi** bila perlu. Buka `config/database.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'umkm_db');
   define('DB_USER', 'root');
   define('DB_PASS', '');   // XAMPP default kosong
   ```
5. **Pastikan folder `uploads/` bisa ditulis.** Di Windows biasanya sudah otomatis.
   Di Linux/Mac jalankan: `chmod -R 775 uploads`
6. **Buka website:** <http://localhost/katalog-umkm/>

> Alamat dasar (BASE_URL) dideteksi otomatis. Kalau nama foldernya diubah dan link terasa
> salah, buka `config/config.php` lalu isi manual:
> `define('BASE_URL', '/nama-folder-anda');` di baris paling atas file tersebut.

### Akun untuk mencoba

| Peran        | Email             | Kata sandi |
|--------------|-------------------|------------|
| Admin        | admin@umkm.test   | admin123   |
| Pemilik UMKM | siti@umkm.test    | umkm123    |
| Pemilik UMKM | bayu@umkm.test    | umkm123    |
| Pemilik UMKM | lela@umkm.test    | umkm123    |

Ganti kata sandi akun admin sebelum dipakai sungguhan.

---

## 3. Struktur folder

```
katalog-umkm/
├── index.php                  Beranda publik
├── produk.php                 Daftar produk + pencarian, filter kategori, urutkan
├── produk-detail.php          Detail produk + tombol Pesan via WhatsApp
├── umkm.php                   Daftar UMKM + pencarian
├── umkm-detail.php            Profil UMKM + produknya
├── kategori.php               Daftar kategori
├── kontak.php                 Informasi kontak pengelola
│
├── config/
│   ├── config.php             Pengaturan aplikasi, session aman, header keamanan
│   └── database.php           Koneksi PDO ke MySQL  ← ubah di sini
│
├── includes/
│   ├── functions.php          Fungsi bantuan: escape, CSRF, login, upload, rupiah, WA
│   ├── public_header.php      Navbar + pembuka halaman publik
│   ├── public_footer.php      Footer publik
│   ├── panel_header.php       Sidebar + topbar dashboard (admin & pemilik)
│   ├── panel_footer.php       Penutup dashboard + modal konfirmasi hapus
│   ├── kartu_produk.php       Komponen kartu produk
│   └── tabel_produk_pemilik.php  Komponen tabel produk pemilik
│
├── auth/
│   ├── register.php           Registrasi pemilik UMKM
│   ├── login.php              Login admin & pemilik
│   └── logout.php             Keluar
│
├── admin/
│   ├── index.php              Dashboard: statistik + produk terbaru + pendaftar terbaru
│   ├── umkm.php               Daftar UMKM, cari, hapus
│   ├── umkm-detail.php        Detail UMKM + produk yang didaftarkan
│   ├── umkm-edit.php          Edit data UMKM
│   ├── produk.php             Semua produk, filter status/kategori, setujui cepat, hapus
│   ├── produk-detail.php      Detail produk + form verifikasi + riwayat verifikasi
│   ├── produk-edit.php        Edit data produk oleh admin
│   ├── kategori.php           CRUD kategori
│   └── laporan.php            Laporan per rentang tanggal, unduh CSV, cetak
│
├── pemilik/
│   ├── index.php              Dashboard pemilik: statistik + produk terbaru
│   ├── profil.php             Isi / ubah profil usaha + logo
│   ├── produk.php             Produk saya, filter status, hapus
│   ├── produk-tambah.php      Tambah produk + upload foto
│   └── produk-edit.php        Edit produk + riwayat verifikasi
│
├── assets/
│   ├── css/style.css          Tema warna dan komponen
│   ├── js/app.js              Modal hapus, pratinjau gambar, notifikasi
│   └── img/                   Gambar pengganti bila foto belum ada
│
├── uploads/
│   ├── umkm/                  Logo usaha hasil upload
│   ├── produk/                Foto produk hasil upload
│   └── .htaccess              Blokir eksekusi skrip di folder upload
│
├── database/
│   └── umkm_db.sql            Skema + data contoh
│
└── README.md
```

---

## 4. Alur sistem

```
Pemilik UMKM
   └─ Registrasi ─ Login ─ Melengkapi profil usaha ─ Menambah produk
                                                          │
                                            status: "Menunggu Verifikasi"
                                                          │
                                                     Admin memeriksa
                                                    ┌─────┴─────┐
                                               Disetujui     Ditolak
                                                    │             │
                                        tampil di halaman   alasan penolakan
                                             publik          tampil di dashboard
                                                                  │
                                                     pemilik memperbaiki data,
                                                     simpan → kembali "Menunggu"
```

Status produk: **Menunggu Verifikasi**, **Disetujui**, **Ditolak**.
Hanya produk berstatus *Disetujui* yang muncul di halaman publik.
Setiap keputusan admin dan setiap pengiriman ulang tercatat di tabel `verification_logs`.

---

## 5. Database

Lima tabel, semua memakai InnoDB agar relasi antar tabel dijaga database.

| Tabel               | Isi                                                                 |
|---------------------|----------------------------------------------------------------------|
| `users`             | id, nama, email, password (hash), role, created_at                   |
| `umkm`              | id, user_id, nama_usaha, nama_pemilik, alamat, no_telepon, deskripsi, foto_umkm, created_at, updated_at |
| `categories`        | id, nama_kategori, deskripsi                                         |
| `products`          | id, umkm_id, category_id, nama_produk, deskripsi, harga, stok, no_wa, foto_produk, status, alasan_penolakan, created_at, updated_at |
| `verification_logs` | id, product_id, admin_id, status, catatan, verified_at               |

Relasi:

* satu `users` (role umkm) memiliki satu `umkm` — `user_id` UNIQUE, hapus user → data UMKM ikut terhapus
* satu `umkm` memiliki banyak `products` — hapus UMKM → produknya ikut terhapus
* satu `categories` memiliki banyak `products` — hapus kategori → produk menjadi "Tanpa kategori"
* satu `products` memiliki banyak `verification_logs`
* `verification_logs.admin_id` menunjuk ke admin yang memverifikasi (kosong bila catatan dibuat karena pemilik mengirim ulang)

---

## 6. Fitur

**Admin**
login, dashboard statistik (total UMKM, total produk, menunggu, disetujui, ditolak),
daftar pendaftar UMKM, detail UMKM, produk tiap UMKM, memeriksa produk masuk,
menyetujui/menolak beserta alasan, edit data UMKM, edit data produk, hapus UMKM/produk,
kelola kategori, pencarian dan filter (nama UMKM, kategori, status), laporan + unduh CSV + cetak, logout.

**Pemilik UMKM**
registrasi, login, dashboard, profil usaha (nama usaha, nama pemilik, telepon/WhatsApp,
alamat, deskripsi, logo), tambah produk (foto, nama, kategori, deskripsi, harga, stok,
nomor WhatsApp pemesanan), edit produk, hapus produk, melihat status verifikasi dan alasan
penolakan, riwayat verifikasi, logout.

**Pengunjung (tanpa login)**
beranda, daftar UMKM, daftar produk, detail UMKM, detail produk, halaman kategori,
pencarian produk/UMKM, informasi kontak, tombol "Pesan via WhatsApp" yang langsung
membuka chat berisi nama produk dan harganya.

---

## 7. Keamanan yang diterapkan

* Password disimpan sebagai hash (`password_hash` / `password_verify`), bukan teks biasa.
* Autentikasi dan otorisasi per peran: `wajib_admin()` untuk folder `admin/`, `wajib_pemilik()` untuk folder `pemilik/`.
* Pemilik UMKM hanya bisa mengubah dan menghapus produk miliknya sendiri (dicek lewat `produk_milik_saya()`, bukan lewat parameter URL saja).
* Semua query memakai PDO prepared statement → aman dari SQL Injection.
* Semua keluaran dicetak lewat `e()` (`htmlspecialchars`) → aman dari XSS.
* Setiap form POST memakai token CSRF (`csrf_field()` + `cek_csrf()`).
* Session: nama cookie khusus, `httponly`, `samesite=Lax`, dan `session_regenerate_id()` saat login.
* Upload gambar diperiksa isinya dengan `getimagesize()` (bukan hanya nama file), dibatasi 2 MB, hanya JPG/PNG/WEBP, nama file diacak, dan folder `uploads/` diblokir dari eksekusi skrip lewat `.htaccess`.
* Validasi input di setiap form dengan pesan kesalahan berbahasa Indonesia.

---

## 8. Catatan pengembangan

* **Mengubah warna tema:** semua warna ada di `:root` pada `assets/css/style.css`.
* **Menambah halaman publik:** buat file `.php` di folder utama, panggil
  `require_once __DIR__ . '/config/config.php';` lalu `includes/public_header.php` dan `includes/public_footer.php`.
* **Menambah halaman dashboard:** sama, tetapi pakai `includes/panel_header.php` dan
  `includes/panel_footer.php`, serta tambahkan menunya di array `$menu` dalam `panel_header.php`.
* **Menambah kategori:** lewat menu Kategori di dashboard admin, tidak perlu mengubah kode.

### Masalah yang sering muncul

| Gejala | Penyebab dan solusi |
|--------|---------------------|
| "Koneksi ke database gagal" | MySQL belum jalan, atau `DB_NAME`/`DB_USER`/`DB_PASS` di `config/database.php` belum sesuai. |
| Tampilan berantakan, link salah | Nama folder berbeda. Isi `BASE_URL` manual di `config/config.php`. |
| Foto tidak muncul setelah upload | Folder `uploads/produk` atau `uploads/umkm` tidak bisa ditulis. Jalankan `chmod -R 775 uploads`. |
| "Gambar gagal disimpan ke server" | Sama seperti di atas, atau `upload_max_filesize` di php.ini lebih kecil dari ukuran gambar. |
| Produk tidak muncul di halaman publik | Statusnya belum "Disetujui". Periksa lewat dashboard admin. |
