<?php
require_once __DIR__ . '/config/config.php';

$q      = trim($_GET['q'] ?? '');
$where  = '';
$params = [];

if ($q !== '') {
    $where = 'WHERE (u.nama_usaha LIKE :q1 OR u.alamat LIKE :q2 OR u.deskripsi LIKE :q3)';
    $params[':q1'] = $params[':q2'] = $params[':q3'] = '%' . $q . '%';
}

$stmt = db()->prepare(
    "SELECT u.*, COUNT(p.id) AS jumlah_produk
     FROM umkm u
     LEFT JOIN products p ON p.umkm_id = u.id AND p.status = 'disetujui'
     $where
     GROUP BY u.id
     ORDER BY jumlah_produk DESC, u.nama_usaha ASC"
);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$judul   = 'Daftar UMKM';
$halaman = 'umkm';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4">
  <h1 class="h3 mb-1">UMKM terdaftar</h1>
  <p class="bantuan"><?= count($daftar) ?> usaha ditampilkan.</p>

  <form class="kotak-cari mb-4" method="get" action="<?= url('umkm.php') ?>" role="search">
    <label class="visually-hidden" for="cariUmkm">Cari UMKM</label>
    <i class="bi bi-search" style="color:#5b6a61"></i>
    <input type="search" id="cariUmkm" name="q" value="<?= e($q) ?>" placeholder="Cari nama usaha atau daerah">
    <button class="btn btn-utama rounded-pill px-4" type="submit">Cari</button>
  </form>

  <?php if ($daftar): ?>
    <div class="row g-3 g-lg-4">
      <?php foreach ($daftar as $u): ?>
        <div class="col-md-6 col-lg-4">
          <a class="kartu kartu-link" href="<?= url('umkm-detail.php?id=' . (int) $u['id']) ?>">
            <img class="kartu-gambar" src="<?= gambar('umkm', $u['foto_umkm']) ?>" alt="Logo <?= e($u['nama_usaha']) ?>" loading="lazy">
            <div class="kartu-isi">
              <h2 class="kartu-judul"><?= e($u['nama_usaha']) ?></h2>
              <div class="kartu-meta"><i class="bi bi-person me-1"></i><?= e($u['nama_pemilik']) ?></div>
              <div class="kartu-meta"><i class="bi bi-geo-alt me-1"></i><?= e(potong($u['alamat'], 70)) ?></div>
              <p class="kartu-meta mb-0"><?= e(potong($u['deskripsi'], 90)) ?></p>
              <div class="kartu-meta mt-auto pt-2 fw-bold" style="color:var(--hijau-tua)"><?= (int) $u['jumlah_produk'] ?> produk tayang</div>
            </div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="kotak"><div class="kotak-badan text-center py-5">
      <p class="fw-bold mb-2">UMKM tidak ditemukan</p>
      <p class="bantuan mb-0">Coba kata kunci lain.</p>
    </div></div>
  <?php endif; ?>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
