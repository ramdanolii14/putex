<?php
/**
 * Konfigurasi koneksi database.
 * Ubah 4 baris di bawah ini sesuai pengaturan MySQL di komputer Anda.
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'umkm_db');
define('DB_USER', 'root');
define('DB_PASS', '');          // XAMPP/Laragon default: kosong

/**
 * db() mengembalikan satu koneksi PDO yang dipakai ulang di seluruh halaman.
 * PDO + prepared statement = proteksi utama terhadap SQL Injection.
 */
function db()
{
    static $pdo = null;

    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Jangan tampilkan detail error ke pengunjung.
            error_log('Koneksi database gagal: ' . $e->getMessage());
            exit('Koneksi ke database gagal. Periksa kembali file config/database.php dan pastikan MySQL sudah berjalan.');
        }
    }

    return $pdo;
}
