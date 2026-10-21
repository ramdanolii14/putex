<?php
/**
 * Kumpulan fungsi bantuan yang dipakai di seluruh halaman.
 */

// ==================== Cadangan bila ekstensi mbstring mati ====================
// XAMPP/Laragon sudah mengaktifkan mbstring. Dua fungsi cadangan ini hanya
// dipakai kalau ekstensi tersebut tidak tersedia, supaya website tetap jalan.
if (!function_exists('mb_strlen')) {
    function mb_strlen($teks, $encoding = 'UTF-8')
    {
        return (int) preg_match_all('/./us', (string) $teks);
    }
}
if (!function_exists('mb_substr')) {
    function mb_substr($teks, $mulai, $panjang = null, $encoding = 'UTF-8')
    {
        preg_match_all('/./us', (string) $teks, $cocok);
        return implode('', array_slice($cocok[0], (int) $mulai, $panjang));
    }
}

// ==================== Tampilan & URL ====================

/** Escape output -> proteksi XSS. Pakai e() setiap kali menampilkan data dari database/form. */
function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Membuat link absolut, contoh: url('produk.php') -> /katalog-umkm/produk.php */
function url($path = '')
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function asset($path)
{
    return url('assets/' . ltrim($path, '/'));
}

/** Link gambar hasil upload. Kalau kosong, pakai gambar pengganti. */
function gambar($folder, $file)
{
    if ($file && is_file(UPLOAD_PATH . '/' . $folder . '/' . $file)) {
        return url('uploads/' . $folder . '/' . rawurlencode($file));
    }
    return asset('img/placeholder-' . ($folder === 'umkm' ? 'umkm' : 'produk') . '.svg');
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function rupiah($angka)
{
    return 'Rp ' . number_format((float) $angka, 0, ',', '.');
}

/** Tanggal versi Indonesia: 2026-05-12 -> 12 Mei 2026 */
function tanggal_id($datetime, $denganJam = false)
{
    if (!$datetime) {
        return '-';
    }
    $bulan = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $t = strtotime($datetime);
    $hasil = date('j', $t) . ' ' . $bulan[(int) date('n', $t)] . ' ' . date('Y', $t);
    return $denganJam ? $hasil . ', ' . date('H:i', $t) : $hasil;
}

function potong($teks, $panjang = 120)
{
    $teks = trim(preg_replace('/\s+/', ' ', (string) $teks));
    return mb_strlen($teks) > $panjang ? mb_substr($teks, 0, $panjang) . '…' : $teks;
}

// ==================== Notifikasi (flash message) ====================

/** Simpan notifikasi untuk ditampilkan setelah redirect. Tipe: sukses | gagal | info */
function notif($tipe, $pesan)
{
    $_SESSION['flash'][] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function tampilkan_notif()
{
    if (empty($_SESSION['flash'])) {
        return;
    }
    $peta = ['sukses' => 'success', 'gagal' => 'danger', 'info' => 'info'];
    $ikon = ['sukses' => 'check-circle-fill', 'gagal' => 'exclamation-triangle-fill', 'info' => 'info-circle-fill'];
    foreach ($_SESSION['flash'] as $f) {
        $kelas = $peta[$f['tipe']] ?? 'info';
        echo '<div class="alert alert-' . $kelas . ' alert-dismissible fade show notif" role="alert">'
           . '<i class="bi bi-' . ($ikon[$f['tipe']] ?? 'info-circle-fill') . ' me-2"></i>' . e($f['pesan'])
           . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>';
    }
    unset($_SESSION['flash']);
}

// ==================== CSRF ====================

function csrf_token()
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

/** Tempel di dalam setiap <form method="post">. */
function csrf_field()
{
    echo '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

/** Panggil di awal pemrosesan POST. Menghentikan permintaan palsu dari situs lain. */
function cek_csrf()
{
    $kirim = $_POST['_token'] ?? '';
    if (!is_string($kirim) || !hash_equals(csrf_token(), $kirim)) {
        http_response_code(419);
        exit('Sesi Anda sudah kedaluwarsa. Muat ulang halaman lalu coba lagi.');
    }
}

// ==================== Autentikasi & otorisasi ====================

function login_kan(array $user)
{
    session_regenerate_id(true); // cegah session fixation
    $_SESSION['user'] = [
        'id'    => (int) $user['id'],
        'nama'  => $user['nama'],
        'email' => $user['email'],
        'role'  => $user['role'],
    ];
}

function keluar()
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function user()
{
    return $_SESSION['user'] ?? null;
}

function sudah_login()
{
    return user() !== null;
}

function is_admin()
{
    return sudah_login() && user()['role'] === 'admin';
}

function is_pemilik()
{
    return sudah_login() && user()['role'] === 'umkm';
}

function wajib_login()
{
    if (!sudah_login()) {
        notif('info', 'Masuk dulu untuk membuka halaman tersebut.');
        redirect('auth/login.php');
    }
}

/** Halaman admin hanya untuk admin. */
function wajib_admin()
{
    wajib_login();
    if (!is_admin()) {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini hanya untuk admin.');
    }
}

/** Halaman panel hanya untuk pemilik UMKM. */
function wajib_pemilik()
{
    wajib_login();
    if (!is_pemilik()) {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini hanya untuk pemilik UMKM.');
    }
}

/** Data UMKM milik user yang sedang login (null kalau profil belum diisi). */
function umkm_saya()
{
    if (!is_pemilik()) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM umkm WHERE user_id = ? LIMIT 1');
    $stmt->execute([user()['id']]);
    $data = $stmt->fetch();
    return $data ?: null;
}

/** Pastikan sebuah produk benar-benar milik UMKM yang sedang login. */
function produk_milik_saya($produk_id, $umkm_id)
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = ? AND umkm_id = ? LIMIT 1');
    $stmt->execute([(int) $produk_id, (int) $umkm_id]);
    $data = $stmt->fetch();
    return $data ?: null;
}

// ==================== Status produk ====================

function status_teks($status)
{
    $peta = [
        ST_MENUNGGU  => 'Menunggu verifikasi',
        ST_DISETUJUI => 'Disetujui',
        ST_DITOLAK   => 'Ditolak',
    ];
    return $peta[$status] ?? $status;
}

function status_badge($status)
{
    $kelas = [
        ST_MENUNGGU  => 'badge-menunggu',
        ST_DISETUJUI => 'badge-disetujui',
        ST_DITOLAK   => 'badge-ditolak',
    ];
    return '<span class="badge-status ' . ($kelas[$status] ?? '') . '">' . e(status_teks($status)) . '</span>';
}

// ==================== WhatsApp ====================

/** 0812xxxx / +62812xxxx -> 62812xxxx */
function no_wa($nomor)
{
    $n = preg_replace('/\D/', '', (string) $nomor);
    if (strpos($n, '0') === 0) {
        $n = '62' . substr($n, 1);
    } elseif (strpos($n, '62') !== 0 && $n !== '') {
        $n = '62' . $n;
    }
    return $n;
}

function link_wa($nomor, $pesan = '')
{
    return 'https://wa.me/' . no_wa($nomor) . ($pesan ? '?text=' . rawurlencode($pesan) : '');
}

// ==================== Upload gambar ====================

/**
 * Validasi + simpan gambar. Mengembalikan nama file baru, atau null bila gagal.
 * $pesanError diisi alasan kegagalan.
 */
function upload_gambar($file, $folder, &$pesanError = null)
{
    if (!isset($file) || !is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null; // tidak ada file dikirim
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $pesanError = 'Gambar gagal diunggah. Coba pilih file lain.';
        return null;
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        $pesanError = 'Ukuran gambar melebihi 2 MB. Perkecil dulu gambarnya.';
        return null;
    }
    if (!is_uploaded_file($file['tmp_name'])) {
        $pesanError = 'File tidak valid.';
        return null;
    }

    // Periksa isi file, bukan hanya namanya.
    $info = @getimagesize($file['tmp_name']);
    $izin = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!$info || !isset($izin[$info[2]])) {
        $pesanError = 'Format gambar harus JPG, PNG, atau WEBP.';
        return null;
    }

    $namaBaru = $folder . '-' . date('Ymd') . '-' . bin2hex(random_bytes(6)) . '.' . $izin[$info[2]];
    $tujuan   = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($tujuan)) {
        mkdir($tujuan, 0775, true);
    }
    if (!move_uploaded_file($file['tmp_name'], $tujuan . '/' . $namaBaru)) {
        $pesanError = 'Gambar gagal disimpan ke server. Periksa izin folder uploads/.';
        return null;
    }

    return $namaBaru;
}

function hapus_gambar($folder, $file)
{
    if (!$file) {
        return;
    }
    $path = UPLOAD_PATH . '/' . $folder . '/' . basename($file);
    if (is_file($path)) {
        @unlink($path);
    }
}

// ==================== Lain-lain ====================

function ambil_kategori()
{
    return db()->query('SELECT * FROM categories ORDER BY nama_kategori ASC')->fetchAll();
}

/** Nilai lama saat form gagal divalidasi. */
function lama($key, $default = '')
{
    return e($_SESSION['old'][$key] ?? $default);
}

function simpan_lama(array $data)
{
    $_SESSION['old'] = $data;
}

function bersihkan_lama()
{
    unset($_SESSION['old']);
}
