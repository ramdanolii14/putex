<?php
require_once __DIR__ . '/config/config.php';

$kategori = db()->query(
    "SELECT c.*, COUNT(p.id) AS jumlah
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND p.status = 'disetujui'
     GROUP BY c.id
     ORDER BY c.nama_kategori ASC"
)->fetchAll();

$judul   = 'Kategori produk';
$halaman = 'kategori';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4">
  <h1 class="h3 mb-1">Kategori produk</h1>
  <p class="bantuan mb-4">Pilih kategori untuk melihat produk di dalamnya.</p>

  <div class="row g-3">
    <?php foreach ($kategori as $k): ?>
      <div class="col-md-6 col-lg-4">
        <a class="kotak kartu-link d-block h-100" href="<?= url('produk.php?kategori=' . (int) $k['id']) ?>">
          <div class="kotak-badan">
            <h2 class="h6 mb-1"><?= e($k['nama_kategori']) ?></h2>
            <p class="bantuan mb-2"><?= e($k['deskripsi']) ?></p>
            <span class="badge-status badge-disetujui"><?= (int) $k['jumlah'] ?> produk</span>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
