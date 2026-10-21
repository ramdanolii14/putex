<?php
require_once __DIR__ . '/config/config.php';

// Statistik ringkas
$totalUmkm   = (int) db()->query("SELECT COUNT(*) FROM umkm WHERE status = 'disetujui'")->fetchColumn();
$totalProduk = (int) db()->query("SELECT COUNT(*) FROM products WHERE status = 'disetujui'")->fetchColumn();

$kategori = ambil_kategori();

// Produk terbaru yang sudah disetujui admin
$produkTerbaru = db()->query(
    "SELECT p.id, p.nama_produk, p.harga, p.stok, p.foto_produk, u.nama_usaha, c.nama_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.status = 'disetujui' AND u.status = 'disetujui'
     ORDER BY p.updated_at DESC
     LIMIT 8"
)->fetchAll();

// UMKM dengan produk terbanyak
$umkmPilihan = db()->query(
    "SELECT u.*, COUNT(p.id) AS jumlah_produk
     FROM umkm u
     LEFT JOIN products p ON p.umkm_id = u.id AND p.status = 'disetujui'
     WHERE u.status = 'disetujui'
     GROUP BY u.id
     ORDER BY jumlah_produk DESC, u.created_at DESC
     LIMIT 3"
)->fetchAll();

$judul   = 'Beranda';
$halaman = 'beranda';
require_once APP_ROOT . '/includes/public_header.php';
?>

<section class="hero">
  <div class="container">
    <div class="row align-items-center g-4">
      <div class="col-lg-7">
        <h1 class="mb-3">Produk usaha kecil di daerah kita, dalam satu katalog</h1>
        <p class="lead mb-4">Cari makanan, minuman, dan kerajinan buatan UMKM setempat. Pesanan langsung ke pemilik usaha lewat WhatsApp, tanpa perantara.</p>

        <form class="kotak-cari mb-3" action="<?= url('produk.php') ?>" method="get" role="search">
          <label class="visually-hidden" for="cariHero">Cari produk atau nama usaha</label>
          <i class="bi bi-search" style="color:#5b6a61"></i>
          <input type="search" id="cariHero" name="q" placeholder="Cari kue lapis, kopi, sambal…">
          <button class="btn btn-utama rounded-pill px-4" type="submit">Cari</button>
        </form>

        <div class="d-flex flex-wrap gap-2">
          <?php foreach (array_slice($kategori, 0, 6) as $k): ?>
            <a class="chip" href="<?= url('produk.php?kategori=' . (int) $k['id']) ?>"><?= e($k['nama_kategori']) ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="row g-3">
          <div class="col-6">
            <div class="statistik tepi-hijau">
              <div class="angka"><?= $totalUmkm ?></div>
              <div class="label">UMKM terdaftar</div>
            </div>
          </div>
          <div class="col-6">
            <div class="statistik tepi-kunyit">
              <div class="angka"><?= $totalProduk ?></div>
              <div class="label">Produk tayang</div>
            </div>
          </div>
          <div class="col-12">
            <div class="kotak">
              <div class="kotak-badan py-3">
                <p class="mb-2 fw-bold">Punya usaha dan ingin ikut tampil?</p>
                <p class="bantuan mb-3">Daftar gratis, isi data usaha, lalu unggah produk. Admin akan memeriksa sebelum tayang.</p>
                <a class="btn btn-kunyit btn-sm px-3" href="<?= url('auth/register.php') ?>">Daftarkan usaha</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="container py-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
    <div>
      <h2 class="mb-1">Produk terbaru</h2>
      <p class="bantuan mb-0">Sudah diperiksa dan disetujui admin.</p>
    </div>
    <a class="btn btn-garis btn-sm px-3" href="<?= url('produk.php') ?>">Lihat semua produk</a>
  </div>

  <?php if ($produkTerbaru): ?>
    <div class="row g-3 g-lg-4">
      <?php foreach ($produkTerbaru as $p) { require APP_ROOT . '/includes/kartu_produk.php'; } ?>
    </div>
  <?php else: ?>
    <div class="kotak"><div class="kotak-badan text-center py-5">
      <p class="mb-2 fw-bold">Belum ada produk yang tayang</p>
      <p class="bantuan mb-3">Produk akan muncul di sini setelah admin menyetujuinya.</p>
      <a class="btn btn-utama btn-sm px-3" href="<?= url('auth/register.php') ?>">Jadi UMKM pertama</a>
    </div></div>
  <?php endif; ?>
</section>

<section class="container pb-5">
  <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
    <h2 class="mb-0">UMKM yang bergabung</h2>
    <a class="btn btn-garis btn-sm px-3" href="<?= url('umkm.php') ?>">Lihat semua UMKM</a>
  </div>
  <div class="row g-3 g-lg-4">
    <?php foreach ($umkmPilihan as $u): ?>
      <div class="col-md-4">
        <a class="kartu kartu-link" href="<?= url('umkm-detail.php?id=' . (int) $u['id']) ?>">
          <img class="kartu-gambar" src="<?= gambar('umkm', $u['foto_umkm']) ?>" alt="Logo <?= e($u['nama_usaha']) ?>" loading="lazy">
          <div class="kartu-isi">
            <h3 class="kartu-judul"><?= e($u['nama_usaha']) ?></h3>
            <div class="kartu-meta"><i class="bi bi-geo-alt me-1"></i><?= e(potong($u['alamat'], 60)) ?></div>
            <div class="kartu-meta"><?= (int) $u['jumlah_produk'] ?> produk tayang</div>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
