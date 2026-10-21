<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

// ---------- Statistik ----------
$totalUmkm = (int) db()->query('SELECT COUNT(*) FROM umkm')->fetchColumn();
$p = db()->query(
    "SELECT COUNT(*) AS total,
            SUM(status = 'menunggu')  AS menunggu,
            SUM(status = 'disetujui') AS disetujui,
            SUM(status = 'ditolak')   AS ditolak
     FROM products"
)->fetch();

// ---------- Produk terbaru ----------
$terbaru = db()->query(
    "SELECT p.id, p.nama_produk, p.harga, p.status, p.created_at, p.foto_produk,
            u.nama_usaha, c.nama_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     ORDER BY p.created_at DESC
     LIMIT 8"
)->fetchAll();

// ---------- UMKM terbaru ----------
$umkmBaru = db()->query('SELECT id, nama_usaha, nama_pemilik, created_at FROM umkm ORDER BY created_at DESC LIMIT 5')->fetchAll();

$judul     = 'Dashboard admin';
$menuAktif = 'dashboard';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-4 col-xl"><div class="statistik tepi-hijau"><div class="angka"><?= $totalUmkm ?></div><div class="label">Total UMKM</div></div></div>
  <div class="col-6 col-lg-4 col-xl"><div class="statistik tepi-tinta"><div class="angka"><?= (int) $p['total'] ?></div><div class="label">Total produk</div></div></div>
  <div class="col-6 col-lg-4 col-xl"><div class="statistik tepi-kunyit"><div class="angka"><?= (int) $p['menunggu'] ?></div><div class="label">Menunggu verifikasi</div></div></div>
  <div class="col-6 col-lg-6 col-xl"><div class="statistik tepi-hijau"><div class="angka"><?= (int) $p['disetujui'] ?></div><div class="label">Produk disetujui</div></div></div>
  <div class="col-6 col-lg-6 col-xl"><div class="statistik tepi-bata"><div class="angka"><?= (int) $p['ditolak'] ?></div><div class="label">Produk ditolak</div></div></div>
</div>

<?php if ((int) $p['menunggu'] > 0): ?>
  <div class="alert alert-warning notif d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span><i class="bi bi-hourglass-split me-2"></i><?= (int) $p['menunggu'] ?> produk menunggu diperiksa.</span>
    <a class="btn btn-utama btn-sm px-3" href="<?= url('admin/produk.php?status=menunggu') ?>">Periksa sekarang</a>
  </div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="kotak">
      <div class="kotak-kepala">
        <h2>Produk terbaru</h2>
        <a class="btn btn-garis btn-sm px-3" href="<?= url('admin/produk.php') ?>">Semua produk</a>
      </div>
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead>
            <tr>
              <th>Nama produk</th>
              <th>UMKM</th>
              <th>Kategori</th>
              <th>Harga</th>
              <th>Status</th>
              <th>Tanggal daftar</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
          <?php foreach ($terbaru as $row): ?>
            <tr>
              <td class="d-flex align-items-center gap-2">
                <img class="foto-tabel" src="<?= gambar('produk', $row['foto_produk']) ?>" alt="">
                <span class="fw-bold"><?= e($row['nama_produk']) ?></span>
              </td>
              <td><?= e($row['nama_usaha']) ?></td>
              <td class="bantuan"><?= e($row['nama_kategori'] ?? '-') ?></td>
              <td><?= rupiah($row['harga']) ?></td>
              <td><?= status_badge($row['status']) ?></td>
              <td class="bantuan"><?= tanggal_id($row['created_at']) ?></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-garis btn-sm" href="<?= url('admin/produk-detail.php?id=' . (int) $row['id']) ?>">Detail</a>
                <?php if ($row['status'] === ST_MENUNGGU): ?>
                  <a class="btn btn-kunyit btn-sm" href="<?= url('admin/produk-detail.php?id=' . (int) $row['id']) ?>#verifikasi">Verifikasi</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$terbaru): ?>
            <tr><td colspan="7" class="text-center bantuan py-4">Belum ada produk yang didaftarkan.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-4">
    <div class="kotak">
      <div class="kotak-kepala">
        <h2>Pendaftar UMKM terbaru</h2>
        <a class="btn btn-garis btn-sm px-3" href="<?= url('admin/umkm.php') ?>">Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <tbody>
          <?php foreach ($umkmBaru as $u): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= e($u['nama_usaha']) ?></div>
                <div class="bantuan"><?= e($u['nama_pemilik']) ?> &middot; <?= tanggal_id($u['created_at']) ?></div>
              </td>
              <td class="text-end"><a class="btn btn-garis btn-sm" href="<?= url('admin/umkm-detail.php?id=' . (int) $u['id']) ?>">Detail</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$umkmBaru): ?>
            <tr><td class="text-center bantuan py-4">Belum ada UMKM terdaftar.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
