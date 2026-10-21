<?php
require_once __DIR__ . '/config/config.php';

$id   = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM umkm WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$umkm = $stmt->fetch();

if (!$umkm) {
    http_response_code(404);
    $judul = 'UMKM tidak ditemukan';
    require_once APP_ROOT . '/includes/public_header.php';
    echo '<div class="container py-5 text-center"><h1 class="h4">UMKM tidak ditemukan</h1>'
       . '<a class="btn btn-utama btn-sm px-3 mt-2" href="' . url('umkm.php') . '">Lihat daftar UMKM</a></div>';
    require_once APP_ROOT . '/includes/public_footer.php';
    exit;
}

$sp = db()->prepare(
    "SELECT p.id, p.nama_produk, p.harga, p.stok, p.foto_produk, u.nama_usaha, c.nama_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.umkm_id = ? AND p.status = 'disetujui'
     ORDER BY p.updated_at DESC"
);
$sp->execute([$umkm['id']]);
$produk = $sp->fetchAll();

$judul   = $umkm['nama_usaha'];
$halaman = 'umkm';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4">
  <div class="kotak mb-4">
    <div class="kotak-badan">
      <div class="row g-4 align-items-center">
        <div class="col-md-3">
          <img class="w-100" style="border-radius:12px;aspect-ratio:1/1;object-fit:cover;border:1px solid var(--garis)"
               src="<?= gambar('umkm', $umkm['foto_umkm']) ?>" alt="Logo <?= e($umkm['nama_usaha']) ?>">
        </div>
        <div class="col-md-9">
          <h1 class="h3 mb-1"><?= e($umkm['nama_usaha']) ?></h1>
          <p class="bantuan mb-2">
            <i class="bi bi-person me-1"></i><?= e($umkm['nama_pemilik']) ?>
            <span class="mx-2">|</span>
            <i class="bi bi-geo-alt me-1"></i><?= e($umkm['alamat']) ?>
          </p>
          <p class="mb-3"><?= nl2br(e($umkm['deskripsi'])) ?></p>
          <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-wa px-4" target="_blank" rel="noopener"
               href="<?= e(link_wa($umkm['no_telepon'], 'Halo ' . $umkm['nama_usaha'] . ', saya menemukan usaha Anda di Katalog UMKM Nusantara.')) ?>">
              <i class="bi bi-whatsapp me-2"></i>Hubungi via WhatsApp
            </a>
            <span class="btn btn-garis disabled"><?= e($umkm['no_telepon']) ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <h2 class="h5 mb-3">Produk dari usaha ini (<?= count($produk) ?>)</h2>
  <?php if ($produk): ?>
    <div class="row g-3 g-lg-4">
      <?php foreach ($produk as $p) { require APP_ROOT . '/includes/kartu_produk.php'; } ?>
    </div>
  <?php else: ?>
    <div class="kotak"><div class="kotak-badan text-center py-5">
      <p class="fw-bold mb-1">Belum ada produk yang tayang</p>
      <p class="bantuan mb-0">Produk usaha ini masih menunggu verifikasi admin.</p>
    </div></div>
  <?php endif; ?>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
