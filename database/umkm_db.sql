-- =========================================================
--  Database: umkm_db
--  Platform Pendaftaran & Katalog Produk UMKM
--  Cara pakai: buka phpMyAdmin -> tab Import -> pilih file ini -> Go
-- =========================================================

CREATE DATABASE IF NOT EXISTS `umkm_db`
  DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `umkm_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `verification_logs`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `umkm`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------
-- 1. users : akun admin dan akun pemilik UMKM
-- ---------------------------------------------------------
CREATE TABLE `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `nama`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `password`   VARCHAR(255) NOT NULL,           -- disimpan sebagai hash bcrypt
  `role`       ENUM('admin','umkm') NOT NULL DEFAULT 'umkm',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 2. umkm : profil usaha (satu user = satu UMKM)
-- ---------------------------------------------------------
CREATE TABLE `umkm` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`      INT NOT NULL,
  `nama_usaha`   VARCHAR(150) NOT NULL,
  `nama_pemilik` VARCHAR(100) NOT NULL,
  `alamat`       TEXT NULL,
  `no_telepon`   VARCHAR(20) NOT NULL,
  `deskripsi`    TEXT NULL,
  `foto_umkm`    VARCHAR(255) NULL,
  `status`       ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `created_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_umkm_user` (`user_id`),
  CONSTRAINT `fk_umkm_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 3. categories : kategori produk
-- ---------------------------------------------------------
CREATE TABLE `categories` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(80) NOT NULL,
  `deskripsi`     VARCHAR(255) NULL,
  UNIQUE KEY `uk_kategori_nama` (`nama_kategori`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 4. products : produk yang didaftarkan UMKM
-- ---------------------------------------------------------
CREATE TABLE `products` (
  `id`               INT AUTO_INCREMENT PRIMARY KEY,
  `umkm_id`          INT NOT NULL,
  `category_id`      INT NULL,
  `nama_produk`      VARCHAR(150) NOT NULL,
  `deskripsi`        TEXT NULL,
  `harga`            DECIMAL(12,2) NOT NULL DEFAULT 0,
  `stok`             INT NOT NULL DEFAULT 0,
  `no_wa`            VARCHAR(20) NULL,           -- nomor WhatsApp untuk pemesanan
  `foto_produk`      VARCHAR(255) NULL,
  `status`           ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu',
  `alasan_penolakan` TEXT NULL,
  `created_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_produk_status` (`status`),
  KEY `idx_produk_umkm` (`umkm_id`),
  KEY `idx_produk_kategori` (`category_id`),
  CONSTRAINT `fk_produk_umkm` FOREIGN KEY (`umkm_id`) REFERENCES `umkm` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------
-- 5. verification_logs : riwayat verifikasi produk oleh admin
-- ---------------------------------------------------------
CREATE TABLE `verification_logs` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `product_id`  INT NOT NULL,
  `admin_id`    INT NULL,
  `status`      ENUM('menunggu','disetujui','ditolak') NOT NULL,
  `catatan`     TEXT NULL,
  `verified_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_log_produk` (`product_id`),
  CONSTRAINT `fk_log_produk` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_log_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
--  DATA CONTOH
--  Password semua akun contoh:
--    admin@umkm.test  -> admin123
--    akun pemilik     -> umkm123
-- =========================================================
INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`) VALUES
(1, 'Administrator', 'admin@umkm.test', '$2y$10$AFy8aQ.1vp4zlIlTyMtKTOASmLGUA8c6L.eA6zefEVaWdzcMisdZm', 'admin'),
(2, 'Siti Rahmawati', 'siti@umkm.test',  '$2y$10$1Gwg4yYemKWD/Tg2LtC62enJ1buk1Bqe1p36wx8x2P/.0ZpjZZGc6', 'umkm'),
(3, 'Bayu Pratama',  'bayu@umkm.test',  '$2y$10$1Gwg4yYemKWD/Tg2LtC62enJ1buk1Bqe1p36wx8x2P/.0ZpjZZGc6', 'umkm'),
(4, 'Nurlela Hasan', 'lela@umkm.test',  '$2y$10$1Gwg4yYemKWD/Tg2LtC62enJ1buk1Bqe1p36wx8x2P/.0ZpjZZGc6', 'umkm');

INSERT INTO `categories` (`id`, `nama_kategori`, `deskripsi`) VALUES
(1, 'Makanan Ringan', 'Keripik, kue kering, camilan tradisional.'),
(2, 'Makanan Berat',  'Nasi, lauk, dan masakan siap santap.'),
(3, 'Minuman',        'Kopi, teh, jus, dan minuman kemasan.'),
(4, 'Kue & Roti',     'Kue basah, roti, dan aneka jajanan pasar.'),
(5, 'Bumbu & Olahan', 'Sambal, bumbu dapur, ikan dan daging olahan.'),
(6, 'Kerajinan',      'Anyaman, kain, dan produk kriya lokal.');

INSERT INTO `umkm` (`id`, `user_id`, `nama_usaha`, `nama_pemilik`, `alamat`, `no_telepon`, `deskripsi`, `status`) VALUES
(1, 2, 'Dapur Bu Siti',     'Siti Rahmawati', 'Jl. Merdeka No. 12, Kota Gorontalo', '081234567890', 'Usaha rumahan yang menjual aneka kue basah dan jajanan pasar sejak 2018. Menerima pesanan untuk acara keluarga dan kantor.', 'disetujui'),
(2, 3, 'Kopi Tanjung',      'Bayu Pratama',   'Jl. Pantai Indah No. 5, Kota Gorontalo', '082198765432', 'Roastery kecil yang mengolah biji kopi robusta dari kebun petani sekitar. Tersedia biji utuh maupun bubuk.', 'disetujui'),
(3, 4, 'Sambal Lela Pedas', 'Nurlela Hasan',  'Jl. Anggrek No. 8, Kabupaten Bone Bolango', '085233344455', 'Produsen sambal roa dan bumbu dapur khas Gorontalo dengan bahan segar tanpa pengawet.', 'disetujui');

INSERT INTO `products` (`umkm_id`, `category_id`, `nama_produk`, `deskripsi`, `harga`, `stok`, `no_wa`, `status`, `alasan_penolakan`) VALUES
(1, 4, 'Kue Lapis Pandan',      'Kue lapis dengan aroma pandan asli, dikukus setiap pagi. Isi 10 potong per kotak.', 35000, 20, '081234567890', 'disetujui', NULL),
(1, 1, 'Keripik Pisang Manis',  'Keripik pisang kepok renyah dengan taburan gula aren. Kemasan 250 gram.', 18000, 50, '081234567890', 'disetujui', NULL),
(1, 2, 'Nasi Kuning Komplit',   'Nasi kuning dengan ayam suwir, telur, dan sambal. Cocok untuk sarapan.', 20000, 15, '081234567890', 'menunggu', NULL),
(2, 3, 'Kopi Robusta Bubuk 200g','Robusta petani lokal, sangrai medium, digiling halus untuk tubruk.', 45000, 30, '082198765432', 'disetujui', NULL),
(2, 3, 'Kopi Susu Botol 250ml', 'Kopi susu siap minum tanpa pemanis buatan. Tahan 3 hari di kulkas.', 22000, 24, '082198765432', 'menunggu', NULL),
(2, 3, 'Biji Kopi Spesial 1kg', 'Biji kopi pilihan untuk kafe dan warung kopi.', 160000, 10, '082198765432', 'ditolak', 'Foto produk belum diunggah dan deskripsi masih terlalu singkat. Mohon lengkapi lalu kirim ulang untuk diverifikasi.'),
(3, 5, 'Sambal Roa Botol 200g', 'Sambal roa asap dengan cabai rawit pilihan. Tanpa pengawet.', 38000, 40, '085233344455', 'disetujui', NULL),
(3, 5, 'Bumbu Rica Instan',     'Bumbu rica siap pakai untuk 4 porsi masakan.', 15000, 35, '085233344455', 'menunggu', NULL),
(3, 1, 'Abon Ikan Tuna 100g',   'Abon ikan tuna segar, cocok untuk bekal anak sekolah.', 30000, 25, '085233344455', 'disetujui', NULL);

INSERT INTO `verification_logs` (`product_id`, `admin_id`, `status`, `catatan`) VALUES
(1, 1, 'disetujui', 'Data lengkap dan sesuai.'),
(2, 1, 'disetujui', 'Data lengkap dan sesuai.'),
(4, 1, 'disetujui', 'Data lengkap dan sesuai.'),
(6, 1, 'ditolak',   'Foto produk belum diunggah dan deskripsi masih terlalu singkat.'),
(7, 1, 'disetujui', 'Data lengkap dan sesuai.'),
(9, 1, 'disetujui', 'Data lengkap dan sesuai.');
