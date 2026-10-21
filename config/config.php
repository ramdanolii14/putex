<?php
/**
 * File utama yang dipanggil oleh SEMUA halaman.
 * Berisi: pengaturan aplikasi, session aman, koneksi database, dan fungsi bantuan.
 */

// ---------- Pengaturan dasar ----------
define('APP_NAME', 'Katalog UMKM Nusantara');
define('APP_ROOT', dirname(__DIR__));

// Alamat dasar website dideteksi otomatis (misal: /katalog-umkm).
// Kalau link terasa salah, isi manual: define('BASE_URL', '/katalog-umkm');
if (!defined('BASE_URL')) {
    $docRoot = str_replace('\\', '/', (string) realpath($_SERVER['DOCUMENT_ROOT'] ?? ''));
    $appRoot = str_replace('\\', '/', (string) realpath(APP_ROOT));
    $base    = ($docRoot !== '' && strpos($appRoot, $docRoot) === 0) ? substr($appRoot, strlen($docRoot)) : '';
    define('BASE_URL', rtrim($base, '/'));
}

define('UPLOAD_PATH', APP_ROOT . '/uploads');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2 MB

// Status verifikasi produk
define('ST_MENUNGGU',  'menunggu');
define('ST_DISETUJUI', 'disetujui');
define('ST_DITOLAK',   'ditolak');

// ---------- Session yang aman ----------
if (session_status() === PHP_SESSION_NONE) {
    session_name('UMKMSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL === '' ? '/' : BASE_URL,
        'httponly' => true,                                   // cookie tidak bisa dibaca JavaScript (anti XSS)
        'secure'   => !empty($_SERVER['HTTPS']),
        'samesite' => 'Lax',                                  // meredam CSRF
    ]);
    session_start();
}

// ---------- Header keamanan ----------
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');

require_once APP_ROOT . '/config/database.php';
require_once APP_ROOT . '/includes/functions.php';
