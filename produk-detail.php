<?php
require_once __DIR__ . '/config/config.php';

$id = (int) ($_GET['id'] ?? 0);

// Hanya produk berstatus "disetujui" yang boleh dilihat pengunjung.
$stmt = db()->prepare(
    "SELECT p.*, u.id AS id_umkm, u.nama_usaha, u.nama_pemilik, u.alamat, u.no_telepon, u.foto_umkm,
            c.nama_kategori, c.id AS id_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ? AND p.status = 'disetujui'
     LIMIT 1"
);
$stmt->execute([$id]);
$produk = $stmt->fetch();

if (!$produk) {
    http_response_code(404);
    $judul = 'Produk tidak ditemukan';
    require_once APP_ROOT . '/includes/public_header.php';
    echo '<div class="container py-5 text-center"><h1 class="h4">Produk tidak ditemukan</h1>'
       . '<p class="bantuan">Produk mungkin sudah dihapus atau belum disetujui admin.</p>'
       . '<a class="btn btn-utama btn-sm px-3" href="' . url('produk.php') . '">Lihat produk lain</a></div>';
    require_once APP_ROOT . '/includes/public_footer.php';
    exit;
}

// Produk lain dari UMKM yang sama
$lain = db()->prepare(
    "SELECT p.id, p.nama_produk, p.harga, p.stok, p.foto_produk, u.nama_usaha, c.nama_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.umkm_id = ? AND p.status = 'disetujui' AND p.id <> ?
     ORDER BY p.updated_at DESC LIMIT 4"
);
$lain->execute([$produk['id_umkm'], $produk['id']]);
$produkLain = $lain->fetchAll();

$nomorWa = $produk['no_wa'] ?: $produk['no_telepon'];
$pesanWa = 'Halo ' . $produk['nama_usaha'] . ', saya melihat produk "' . $produk['nama_produk']
         . '" (' . rupiah($produk['harga']) . ') di Katalog UMKM Nusantara. Apakah masih tersedia?';

$judul   = $produk['nama_produk'];
$halaman = 'produk';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4">
  <nav aria-label="Jejak halaman" class="mb-3">
    <ol class="breadcrumb bantuan mb-0">
      <li class="breadcrumb-item"><a href="<?= url('index.php') ?>">Beranda</a></li>
      <li class="breadcrumb-item"><a href="<?= url('produk.php') ?>">Produk</a></li>
      <li class="breadcrumb-item active" aria-current="page"><?= e($produk['nama_produk']) ?></li>
    </ol>
  </nav>

  <div class="row g-4">
    <div class="col-lg-6">
      <img class="w-100" style="border-radius:14px;border:1px solid var(--garis);aspect-ratio:4/3;object-fit:cover"
           src="<?= gambar('produk', $produk['foto_produk']) ?>" alt="Foto <?= e($produk['nama_produk']) ?>">
    </div>

    <div class="col-lg-6">
      <?php if ($produk['nama_kategori']): ?>
        <a class="kartu-kategori text-decoration-none" href="<?= url('produk.php?kategori=' . (int) $produk['id_kategori']) ?>"><?= e($produk['nama_kategori']) ?></a>
      <?php endif; ?>
      <h1 class="h3 mt-2 mb-1"><?= e($produk['nama_produk']) ?></h1>
      <p class="bantuan mb-3">Dijual oleh <a href="<?= url('umkm-detail.php?id=' . (int) $produk['id_umkm']) ?>"><?= e($produk['nama_usaha']) ?></a></p>

      <div class="kartu-harga mb-3" style="font-size:1.7rem"><?= rupiah($produk['harga']) ?></div>

      <p class="mb-4"><?= nl2br(e($produk['deskripsi'])) ?></p>

      <table class="table table-sm mb-4" style="max-width:420px">
        <tbody>
          <tr><td class="bantuan">Ketersediaan</td>
              <td><?= (int) $produk['stok'] > 0
                    ? '<span class="badge-status badge-disetujui">Tersedia, stok ' . (int) $produk['stok'] . '</span>'
                    : '<span class="badge-status badge-ditolak">Stok habis</span>' ?></td></tr>
          <tr><td class="bantuan">Kategori</td><td><?= e($produk['nama_kategori'] ?? 'Tanpa kategori') ?></td></tr>
          <tr><td class="bantuan">Pemilik usaha</td><td><?= e($produk['nama_pemilik']) ?></td></tr>
          <tr><td class="bantuan">Alamat usaha</td><td><?= e($produk['alamat']) ?></td></tr>
          <tr><td class="bantuan">Diperbarui</td><td><?= tanggal_id($produk['updated_at']) ?></td></tr>
        </tbody>
      </table>

      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-wa px-4 py-2" target="_blank" rel="noopener" href="<?= e(link_wa($nomorWa, $pesanWa)) ?>">
          <i class="bi bi-whatsapp me-2"></i>Pesan via WhatsApp
        </a>
        <a class="btn btn-garis px-4 py-2" href="<?= url('umkm-detail.php?id=' . (int) $produk['id_umkm']) ?>">Lihat profil usaha</a>
      </div>
      <p class="bantuan mt-2 mb-0">Pesanan dan pembayaran diurus langsung dengan pemilik usaha.</p>
    </div>
  </div>

  <?php if ($produkLain): ?>
    <h2 class="h5 mt-5 mb-3">Produk lain dari <?= e($produk['nama_usaha']) ?></h2>
    <div class="row g-3">
      <?php foreach ($produkLain as $p) { require APP_ROOT . '/includes/kartu_produk.php'; } ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
