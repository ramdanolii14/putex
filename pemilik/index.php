<?php
require_once __DIR__ . '/../config/config.php';
wajib_pemilik();

$umkm = umkm_saya();

// Statistik produk milik UMKM ini
$stat = ['total' => 0, 'menunggu' => 0, 'disetujui' => 0, 'ditolak' => 0];
$daftar = [];

if ($umkm) {
    $s = db()->prepare(
        "SELECT COUNT(*) AS total,
                SUM(status = 'menunggu')  AS menunggu,
                SUM(status = 'disetujui') AS disetujui,
                SUM(status = 'ditolak')   AS ditolak
         FROM products WHERE umkm_id = ?"
    );
    $s->execute([$umkm['id']]);
    $hasil = $s->fetch();
    $stat = [
        'total'     => (int) $hasil['total'],
        'menunggu'  => (int) $hasil['menunggu'],
        'disetujui' => (int) $hasil['disetujui'],
        'ditolak'   => (int) $hasil['ditolak'],
    ];

    $d = db()->prepare(
        'SELECT p.*, c.nama_kategori
         FROM products p LEFT JOIN categories c ON c.id = p.category_id
         WHERE p.umkm_id = ? ORDER BY p.created_at DESC LIMIT 5'
    );
    $d->execute([$umkm['id']]);
    $daftar = $d->fetchAll();
}

$judul     = 'Dashboard';
$menuAktif = 'dashboard';
require_once APP_ROOT . '/includes/panel_header.php';
?>

<?php if (!$umkm): ?>
  <div class="kotak">
    <div class="kotak-badan text-center py-5">
      <h2 class="h5">Lengkapi profil usaha dulu</h2>
      <p class="bantuan">Produk baru bisa ditambahkan setelah data usaha Anda terisi.</p>
      <a class="btn btn-utama px-4" href="<?= url('pemilik/profil.php') ?>">Isi profil usaha</a>
    </div>
  </div>
<?php else: ?>

  <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
    <div>
      <h2 class="h4 mb-0"><?= e($umkm['nama_usaha']) ?></h2>
      <p class="bantuan mb-0"><?= e($umkm['nama_pemilik']) ?> &middot; <?= e($umkm['no_telepon']) ?></p>
    </div>
    <a class="btn btn-utama px-4" href="<?= url('pemilik/produk-tambah.php') ?>"><i class="bi bi-plus-lg me-1"></i>Tambah produk</a>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="statistik tepi-tinta"><div class="angka"><?= $stat['total'] ?></div><div class="label">Jumlah produk</div></div></div>
    <div class="col-6 col-lg-3"><div class="statistik tepi-kunyit"><div class="angka"><?= $stat['menunggu'] ?></div><div class="label">Menunggu verifikasi</div></div></div>
    <div class="col-6 col-lg-3"><div class="statistik tepi-hijau"><div class="angka"><?= $stat['disetujui'] ?></div><div class="label">Disetujui</div></div></div>
    <div class="col-6 col-lg-3"><div class="statistik tepi-bata"><div class="angka"><?= $stat['ditolak'] ?></div><div class="label">Ditolak</div></div></div>
  </div>

  <?php if ($stat['ditolak'] > 0): ?>
    <div class="alert alert-warning notif mb-4">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      Ada <?= $stat['ditolak'] ?> produk yang ditolak. Buka Produk Saya, baca alasannya, perbaiki datanya, lalu simpan untuk dikirim ulang ke admin.
    </div>
  <?php endif; ?>

  <div class="kotak">
    <div class="kotak-kepala">
      <h2>Produk terbaru Anda</h2>
      <a class="btn btn-garis btn-sm px-3" href="<?= url('pemilik/produk.php') ?>">Lihat semua produk</a>
    </div>
    <?php if ($daftar): ?>
      <?php require APP_ROOT . '/includes/tabel_produk_pemilik.php'; ?>
    <?php else: ?>
      <div class="kotak-badan text-center py-5">
        <p class="fw-bold mb-1">Belum ada produk</p>
        <p class="bantuan mb-3">Tambahkan produk pertama Anda, lengkap dengan foto dan harga.</p>
        <a class="btn btn-utama px-4" href="<?= url('pemilik/produk-tambah.php') ?>">Tambah produk</a>
      </div>
    <?php endif; ?>
  </div>

<?php endif; ?>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
