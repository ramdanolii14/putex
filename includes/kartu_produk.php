<?php
/** Kartu produk untuk halaman publik. Butuh variabel $p (satu baris produk). */
?>
<div class="col-6 col-lg-4 col-xl-3">
  <a class="kartu kartu-link" href="<?= url('produk-detail.php?id=' . (int) $p['id']) ?>">
    <img class="kartu-gambar" src="<?= gambar('produk', $p['foto_produk']) ?>" alt="Foto <?= e($p['nama_produk']) ?>" loading="lazy">
    <div class="kartu-isi">
      <span class="kartu-kategori align-self-start"><?= e($p['nama_kategori'] ?? 'Tanpa kategori') ?></span>
      <h3 class="kartu-judul"><?= e($p['nama_produk']) ?></h3>
      <div class="kartu-harga"><?= rupiah($p['harga']) ?></div>
      <div class="kartu-meta"><i class="bi bi-shop me-1"></i><?= e($p['nama_usaha']) ?></div>
      <div class="kartu-meta mt-auto pt-2">
        <?php if ((int) $p['stok'] > 0): ?>
          <i class="bi bi-check-circle-fill" style="color:#1f6b4a"></i> Tersedia
        <?php else: ?>
          <i class="bi bi-x-circle-fill" style="color:#c0452f"></i> Stok habis
        <?php endif; ?>
      </div>
    </div>
  </a>
</div>
