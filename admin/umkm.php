<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

// ---------- Verifikasi UMKM oleh admin ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($_POST['aksi'] ?? '', ['setujui', 'tolak'], true)) {
    cek_csrf();
    $id = (int) ($_POST['id'] ?? 0);
    $status = $_POST['aksi'] === 'setujui' ? 'disetujui' : 'ditolak';
    $cekUmkm = db()->prepare('SELECT id, nama_usaha FROM umkm WHERE id = ?');
    $cekUmkm->execute([$id]);
    $dataUmkm = $cekUmkm->fetch();
    if (!$dataUmkm) {
        notif('gagal', 'Data UMKM tidak ditemukan.');
    } else {
        db()->prepare('UPDATE umkm SET status = ? WHERE id = ?')->execute([$status, $id]);
        notif('sukses', 'UMKM "' . $dataUmkm['nama_usaha'] . '" ' . ($status === 'disetujui' ? 'disetujui.' : 'ditolak.'));
    }
    redirect('admin/umkm.php');
}

// ---------- Hapus UMKM (produk ikut terhapus karena ON DELETE CASCADE) ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    cek_csrf();
    $id = (int) ($_POST['id'] ?? 0);

    $s = db()->prepare('SELECT * FROM umkm WHERE id = ?');
    $s->execute([$id]);
    $data = $s->fetch();

    if (!$data) {
        notif('gagal', 'Data UMKM tidak ditemukan.');
    } else {
        // Ambil nama file foto produk supaya bisa ikut dibersihkan.
        $fotoProduk = db()->prepare('SELECT foto_produk FROM products WHERE umkm_id = ?');
        $fotoProduk->execute([$id]);
        $foto = $fotoProduk->fetchAll(PDO::FETCH_COLUMN);

        db()->prepare('DELETE FROM umkm WHERE id = ?')->execute([$id]);

        hapus_gambar('umkm', $data['foto_umkm']);
        foreach ($foto as $f) { hapus_gambar('produk', $f); }

        notif('sukses', 'Data UMKM "' . $data['nama_usaha'] . '" beserta produknya dihapus.');
    }
    redirect('admin/umkm.php');
}

// ---------- Pencarian ----------
$q      = trim($_GET['q'] ?? '');
$where  = '';
$params = [];
if ($q !== '') {
    $where = 'WHERE (u.nama_usaha LIKE :q1 OR u.nama_pemilik LIKE :q2 OR u.no_telepon LIKE :q3)';
    $params[':q1'] = $params[':q2'] = $params[':q3'] = '%' . $q . '%';
}

$stmt = db()->prepare(
    "SELECT u.*, us.email,
            COUNT(p.id) AS total_produk,
            SUM(p.status = 'menunggu')  AS menunggu,
            SUM(p.status = 'disetujui') AS disetujui
     FROM umkm u
     JOIN users us ON us.id = u.user_id
     LEFT JOIN products p ON p.umkm_id = u.id
     $where
     GROUP BY u.id
     ORDER BY u.created_at DESC"
);
$stmt->execute($params);
$daftar = $stmt->fetchAll();

$judul     = 'Data UMKM';
$menuAktif = 'umkm';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="kotak">
  <div class="kotak-kepala">
    <h2>Daftar pendaftar UMKM (<?= count($daftar) ?>)</h2>
    <form class="d-flex gap-2" method="get">
      <input class="form-control form-control-sm" type="search" name="q" value="<?= e($q) ?>" placeholder="Cari nama usaha / pemilik">
      <button class="btn btn-garis btn-sm px-3" type="submit">Cari</button>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Usaha</th>
          <th>Pemilik</th>
          <th>Kontak</th>
          <th>Produk</th>
          <th>Terdaftar</th>
          <th class="text-end">Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($daftar as $u): ?>
        <tr>
          <td class="d-flex align-items-center gap-2">
            <img class="foto-tabel" src="<?= gambar('umkm', $u['foto_umkm']) ?>" alt="">
            <div>
              <div class="fw-bold"><?= e($u['nama_usaha']) ?></div>
              <div class="bantuan"><?= e(potong($u['alamat'], 40)) ?></div>
            </div>
          </td>
          <td>
            <div><?= e($u['nama_pemilik']) ?></div>
            <div class="bantuan"><?= e($u['email']) ?></div>
          </td>
          <td><?= e($u['no_telepon']) ?></td>
          <td>
            <span class="fw-bold"><?= (int) $u['total_produk'] ?></span> produk
            <div class="bantuan"><?= (int) $u['disetujui'] ?> tayang &middot; <?= (int) $u['menunggu'] ?> menunggu</div>
          </td>
          <td>
            <span class="badge <?= $u['status'] === 'disetujui' ? 'text-bg-success' : ($u['status'] === 'ditolak' ? 'text-bg-danger' : 'text-bg-warning') ?>"><?= e(ucfirst($u['status'])) ?></span>
          </td>
          <td class="bantuan"><?= tanggal_id($u['created_at']) ?></td>
          <td class="text-end text-nowrap">
            <?php if ($u['status'] !== 'disetujui'): ?>
              <form method="post" class="d-inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <input type="hidden" name="aksi" value="setujui">
                <button class="btn btn-success btn-sm" type="submit">Setujui</button>
              </form>
            <?php endif; ?>
            <?php if ($u['status'] !== 'ditolak'): ?>
              <form method="post" class="d-inline">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                <input type="hidden" name="aksi" value="tolak">
                <button class="btn btn-outline-danger btn-sm" type="submit">Tolak</button>
              </form>
            <?php endif; ?>
            <a class="btn btn-garis btn-sm" href="<?= url('admin/umkm-detail.php?id=' . (int) $u['id']) ?>">Detail</a>
            <a class="btn btn-garis btn-sm" href="<?= url('admin/umkm-edit.php?id=' . (int) $u['id']) ?>"><i class="bi bi-pencil"></i></a>
            <button type="button" class="btn btn-outline-danger btn-sm tombol-hapus"
                    data-nama="UMKM <?= e($u['nama_usaha']) ?> beserta seluruh produknya"
                    data-id="<?= (int) $u['id'] ?>"
                    data-aksi="<?= url('admin/umkm.php') ?>"><i class="bi bi-trash"></i></button>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$daftar): ?>
        <tr><td colspan="7" class="text-center bantuan py-4">Tidak ada data UMKM.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
