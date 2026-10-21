-- Jalankan sekali pada database umkm_db yang sudah terpasang (bukan instalasi baru).
-- UMKM yang sudah ada akan berstatus menunggu dan harus diverifikasi admin.
USE `umkm_db`;
ALTER TABLE `umkm`
  ADD COLUMN `status` ENUM('menunggu','disetujui','ditolak') NOT NULL DEFAULT 'menunggu' AFTER `foto_umkm`;
