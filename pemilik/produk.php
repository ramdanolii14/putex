<?php
require_once __DIR__ . '/../config/config.php';
wajib_pemilik();

$umkm = umkm_saya();
if (!$umkm) {
    notif('info', 'Lengkapi profil usaha dulu sebelum mengelola produk.');
    redirect('pemilik/profil.php');
}

// ---------- Hapus produk ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    cek_csrf();
    $id = (int) ($_POST['id'] ?? 0);

    // Pastikan produk benar-benar milik UMKM yang sedang login.
    $produk = produk_milik_saya($id, $umkm['id']);
    if (!$produk) {
        notif('gagal', 'Produk tidak ditemukan atau bukan milik Anda.');
    } else {
        db()->prepare('DELETE FROM products WHERE id = ? AND umkm_id = ?')->execute([$id, $umkm['id']]);
        hapus_gambar('produk', $produk['foto_produk']);
        notif('sukses', 'Produk "' . $produk['nama_produk'] . '" dihapus.');
    }
    redirect('pemilik/produk.php');
}

// ---------- Filter ----------
$status = $_GET['status'] ?? '';
$cari   = trim($_GET['q'] ?? '');

$where  = ['p.umkm_id = :umkm'];
$params = [':umkm' => $umkm['id']];

if (in_array($status, [ST_MENUNGGU, ST_DISETUJUI, ST_DITOLAK], true)) {
    $where[] = 'p.status = :status';
    $params[':status'] = $status;
}
if ($cari !== '') {
    $where[] = 'p.nama_produk LIKE :q';
    $params[':q'] = '%' . $cari . '%';
}

$stmt = db()->prepare(
    'SELECT p.*, c.nama_kategori
     FROM products p LEFT JOIN categories c ON c.id = p.category_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY p.created_at DESC'
);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$judul     = 'Produk saya';
$menuAktif = 'produk';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="kotak">
  <div class="kotak-kepala">
    <h2>Produk <?= e($umkm['nama_usaha']) ?> (<?= count($daftar) ?>)</h2>
    <a class="btn btn-utama btn-sm px-3" href="<?= url('pemilik/produk-tambah.php') ?>"><i class="bi bi-plus-lg me-1"></i>Tambah produk</a>
  </div>

  <div class="kotak-badan border-bottom">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-5">
        <label class="form-label" for="q">Cari produk</label>
        <input class="form-control" type="search" id="q" name="q" value="<?= e($cari) ?>" placeholder="Nama produk">
      </div>
      <div class="col-md-4">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" id="status" name="status">
          <option value="">Semua status</option>
          <?php foreach ([ST_MENUNGGU, ST_DISETUJUI, ST_DITOLAK] as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_teks($s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3 d-grid"><button class="btn btn-garis" type="submit">Terapkan filter</button></div>
    </form>
  </div>

  <?php if ($daftar): ?>
    <?php require APP_ROOT . '/includes/tabel_produk_pemilik.php'; ?>
  <?php else: ?>
    <div class="kotak-badan text-center py-5">
      <p class="fw-bold mb-1">Tidak ada produk pada filter ini</p>
      <p class="bantuan mb-3">Ubah filter, atau tambahkan produk baru.</p>
      <a class="btn btn-utama px-4" href="<?= url('pemilik/produk-tambah.php') ?>">Tambah produk</a>
    </div>
  <?php endif; ?>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
