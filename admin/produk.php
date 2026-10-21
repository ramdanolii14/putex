<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

// ---------- Aksi dari tabel: hapus / setujui cepat ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int) ($_POST['id'] ?? 0);

    $s = db()->prepare('SELECT * FROM products WHERE id = ?');
    $s->execute([$id]);
    $produk = $s->fetch();

    if (!$produk) {
        notif('gagal', 'Produk tidak ditemukan.');
    } elseif ($aksi === 'hapus') {
        db()->prepare('DELETE FROM products WHERE id = ?')->execute([$id]);
        hapus_gambar('produk', $produk['foto_produk']);
        notif('sukses', 'Produk "' . $produk['nama_produk'] . '" dihapus.');
    } elseif ($aksi === 'setujui') {
        db()->prepare('UPDATE products SET status = "disetujui", alasan_penolakan = NULL WHERE id = ?')->execute([$id]);
        db()->prepare('INSERT INTO verification_logs (product_id, admin_id, status, catatan) VALUES (?, ?, "disetujui", ?)')
            ->execute([$id, user()['id'], 'Disetujui langsung dari daftar produk.']);
        notif('sukses', 'Produk "' . $produk['nama_produk'] . '" disetujui dan sekarang tampil di halaman publik.');
    }
    redirect('admin/produk.php?' . http_build_query(array_diff_key($_GET, ['hal' => 1])));
}

// ---------- Filter ----------
$q          = trim($_GET['q'] ?? '');
$status     = $_GET['status'] ?? '';
$kategoriId = (int) ($_GET['kategori'] ?? 0);

$where  = ['1 = 1'];
$params = [];

if ($q !== '') {
    $where[] = '(p.nama_produk LIKE :q1 OR u.nama_usaha LIKE :q2)';
    $params[':q1'] = $params[':q2'] = '%' . $q . '%';
}
if (in_array($status, [ST_MENUNGGU, ST_DISETUJUI, ST_DITOLAK], true)) {
    $where[] = 'p.status = :status';
    $params[':status'] = $status;
}
if ($kategoriId > 0) {
    $where[] = 'p.category_id = :kat';
    $params[':kat'] = $kategoriId;
}

$stmt = db()->prepare(
    'SELECT p.*, u.nama_usaha, c.nama_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY FIELD(p.status, "menunggu", "ditolak", "disetujui"), p.created_at DESC'
);
$stmt->execute($params);
$daftar   = $stmt->fetchAll();
$kategori = ambil_kategori();

$judul     = 'Semua produk';
$menuAktif = $status === ST_MENUNGGU ? 'verifikasi' : 'produk';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="kotak">
  <div class="kotak-kepala">
    <h2><?= $status === ST_MENUNGGU ? 'Produk menunggu verifikasi' : 'Daftar produk' ?> (<?= count($daftar) ?>)</h2>
  </div>

  <div class="kotak-badan border-bottom">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-4">
        <label class="form-label" for="q">Cari</label>
        <input class="form-control" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Nama produk atau nama UMKM">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" id="status" name="status">
          <option value="">Semua status</option>
          <?php foreach ([ST_MENUNGGU, ST_DISETUJUI, ST_DITOLAK] as $s): ?>
            <option value="<?= e($s) ?>" <?= $status === $s ? 'selected' : '' ?>><?= e(status_teks($s)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label" for="kategori">Kategori</label>
        <select class="form-select" id="kategori" name="kategori">
          <option value="0">Semua kategori</option>
          <?php foreach ($kategori as $k): ?>
            <option value="<?= (int) $k['id'] ?>" <?= $kategoriId === (int) $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid"><button class="btn btn-garis" type="submit">Terapkan filter</button></div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr><th>Produk</th><th>UMKM</th><th>Kategori</th><th>Harga</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr>
      </thead>
      <tbody>
      <?php foreach ($daftar as $row): ?>
        <tr>
          <td class="d-flex align-items-center gap-2">
            <img class="foto-tabel" src="<?= gambar('produk', $row['foto_produk']) ?>" alt="">
            <div>
              <div class="fw-bold"><?= e($row['nama_produk']) ?></div>
              <div class="bantuan">Stok <?= (int) $row['stok'] ?></div>
            </div>
          </td>
          <td><?= e($row['nama_usaha']) ?></td>
          <td class="bantuan"><?= e($row['nama_kategori'] ?? '-') ?></td>
          <td><?= rupiah($row['harga']) ?></td>
          <td><?= status_badge($row['status']) ?></td>
          <td class="bantuan"><?= tanggal_id($row['created_at']) ?></td>
          <td class="text-end text-nowrap">
            <a class="btn btn-garis btn-sm" href="<?= url('admin/produk-detail.php?id=' . (int) $row['id']) ?>">Detail</a>
            <?php if ($row['status'] !== ST_DISETUJUI): ?>
              <form method="post" class="d-inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="aksi" value="setujui">
                <input type="hidden" name="id" value="<?= (int) $row['id'] ?>">
                <button class="btn btn-kunyit btn-sm" type="submit" title="Setujui produk"><i class="bi bi-check-lg"></i></button>
              </form>
            <?php endif; ?>
            <a class="btn btn-garis btn-sm" href="<?= url('admin/produk-edit.php?id=' . (int) $row['id']) ?>" title="Edit"><i class="bi bi-pencil"></i></a>
            <button type="button" class="btn btn-outline-danger btn-sm tombol-hapus"
                    data-nama="produk <?= e($row['nama_produk']) ?>"
                    data-id="<?= (int) $row['id'] ?>"
                    data-aksi="<?= url('admin/produk.php') ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$daftar): ?>
        <tr><td colspan="7" class="text-center bantuan py-4">Tidak ada produk pada filter ini.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
