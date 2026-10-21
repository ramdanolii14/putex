<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

$id = (int) ($_GET['id'] ?? 0);
$s  = db()->prepare('SELECT u.*, us.email, us.created_at AS akun_dibuat FROM umkm u JOIN users us ON us.id = u.user_id WHERE u.id = ?');
$s->execute([$id]);
$umkm = $s->fetch();

if (!$umkm) {
    notif('gagal', 'Data UMKM tidak ditemukan.');
    redirect('admin/umkm.php');
}

$sp = db()->prepare(
    'SELECT p.*, c.nama_kategori FROM products p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.umkm_id = ? ORDER BY p.created_at DESC'
);
$sp->execute([$id]);
$produk = $sp->fetchAll();

$judul     = 'Detail UMKM';
$menuAktif = 'umkm';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Profil usaha</h2></div>
      <div class="kotak-badan">
        <img class="w-100 mb-3" style="border-radius:12px;aspect-ratio:1/1;object-fit:cover;border:1px solid var(--garis)"
             src="<?= gambar('umkm', $umkm['foto_umkm']) ?>" alt="Logo <?= e($umkm['nama_usaha']) ?>">
        <h3 class="h5 mb-1"><?= e($umkm['nama_usaha']) ?></h3>
        <table class="table table-sm mb-3">
          <tbody>
            <tr><td class="bantuan">Pemilik</td><td><?= e($umkm['nama_pemilik']) ?></td></tr>
            <tr><td class="bantuan">Email akun</td><td><?= e($umkm['email']) ?></td></tr>
            <tr><td class="bantuan">WhatsApp</td><td><?= e($umkm['no_telepon']) ?></td></tr>
            <tr><td class="bantuan">Alamat</td><td><?= e($umkm['alamat']) ?></td></tr>
            <tr><td class="bantuan">Terdaftar</td><td><?= tanggal_id($umkm['created_at']) ?></td></tr>
            <tr><td class="bantuan">Diperbarui</td><td><?= tanggal_id($umkm['updated_at']) ?></td></tr>
          </tbody>
        </table>
        <p class="mb-3"><?= nl2br(e($umkm['deskripsi'])) ?></p>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-garis btn-sm px-3" href="<?= url('admin/umkm-edit.php?id=' . (int) $umkm['id']) ?>">Edit data</a>
          <a class="btn btn-wa btn-sm px-3" target="_blank" rel="noopener" href="<?= e(link_wa($umkm['no_telepon'], 'Halo ' . $umkm['nama_usaha'] . ', kami dari pengelola Katalog UMKM Nusantara.')) ?>"><i class="bi bi-whatsapp me-1"></i>Hubungi</a>
          <a class="btn btn-garis btn-sm px-3" target="_blank" href="<?= url('umkm-detail.php?id=' . (int) $umkm['id']) ?>">Lihat halaman publik</a>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Produk yang didaftarkan (<?= count($produk) ?>)</h2></div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr><th>Produk</th><th>Kategori</th><th>Harga</th><th>Stok</th><th>Status</th><th>Tanggal</th><th class="text-end">Aksi</th></tr>
          </thead>
          <tbody>
          <?php foreach ($produk as $row): ?>
            <tr>
              <td class="d-flex align-items-center gap-2">
                <img class="foto-tabel" src="<?= gambar('produk', $row['foto_produk']) ?>" alt="">
                <span class="fw-bold"><?= e($row['nama_produk']) ?></span>
              </td>
              <td class="bantuan"><?= e($row['nama_kategori'] ?? '-') ?></td>
              <td><?= rupiah($row['harga']) ?></td>
              <td><?= (int) $row['stok'] ?></td>
              <td><?= status_badge($row['status']) ?></td>
              <td class="bantuan"><?= tanggal_id($row['created_at']) ?></td>
              <td class="text-end"><a class="btn btn-garis btn-sm" href="<?= url('admin/produk-detail.php?id=' . (int) $row['id']) ?>">Detail</a></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$produk): ?>
            <tr><td colspan="7" class="text-center bantuan py-4">UMKM ini belum mendaftarkan produk.</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
