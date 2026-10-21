-- PERBAIKAN: Pulihkan produk lama yang hilang setelah penambahan verifikasi UMKM.
-- Jalankan sekali pada database umkm_db melalui phpMyAdmin.
-- Hanya UMKM yang sudah memiliki produk berstatus disetujui yang dipulihkan,
-- sehingga pendaftar baru yang belum memiliki produk disetujui tetap menunggu verifikasi.
USE `umkm_db`;

UPDATE `umkm` AS u
SET u.`status` = 'disetujui'
WHERE u.`status` = 'menunggu'
  AND EXISTS (
    SELECT 1
    FROM `products` AS p
    WHERE p.`umkm_id` = u.`id`
      AND p.`status` = 'disetujui'
  );

-- Pemeriksaan jumlah setelah perbaikan:
SELECT
  (SELECT COUNT(*) FROM `umkm` WHERE `status` = 'disetujui') AS umkm_disetujui,
  (SELECT COUNT(*) FROM `products` WHERE `status` = 'disetujui') AS produk_disetujui;
