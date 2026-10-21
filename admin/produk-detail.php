<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);

$s = db()->prepare(
    'SELECT p.*, u.id AS id_umkm, u.nama_usaha, u.nama_pemilik, u.no_telepon, u.alamat, c.nama_kategori
     FROM products p
     JOIN umkm u ON u.id = p.umkm_id
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.id = ?'
);
$s->execute([$id]);
$produk = $s->fetch();

if (!$produk) {
    notif('gagal', 'Produk tidak ditemukan.');
    redirect('admin/produk.php');
}

// ---------- Proses verifikasi ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'verifikasi') {
    cek_csrf();

    $statusBaru = $_POST['status'] ?? '';
    $catatan    = trim($_POST['catatan'] ?? '');

    if (!in_array($statusBaru, [ST_DISETUJUI, ST_DITOLAK], true)) {
        notif('gagal', 'Pilih Setujui atau Tolak.');
    } elseif ($statusBaru === ST_DITOLAK && mb_strlen($catatan) < 10) {
        notif('gagal', 'Tulis alasan penolakan minimal 10 huruf agar pemilik UMKM tahu yang harus diperbaiki.');
    } else {
        db()->prepare('UPDATE products SET status = ?, alasan_penolakan = ? WHERE id = ?')
            ->execute([$statusBaru, $statusBaru === ST_DITOLAK ? $catatan : null, $produk['id']]);

        db()->prepare('INSERT INTO verification_logs (product_id, admin_id, status, catatan) VALUES (?, ?, ?, ?)')
            ->execute([$produk['id'], user()['id'], $statusBaru, $catatan]);

        notif('sukses', $statusBaru === ST_DISETUJUI
            ? 'Produk disetujui dan langsung tampil di halaman publik.'
            : 'Produk ditolak. Alasan penolakan sudah dikirim ke dashboard pemilik UMKM.');
        redirect('admin/produk.php?status=menunggu');
    }
    redirect('admin/produk-detail.php?id=' . $produk['id']);
}

$log = db()->prepare('SELECT v.*, us.nama AS nama_admin FROM verification_logs v LEFT JOIN users us ON us.id = v.admin_id WHERE v.product_id = ? ORDER BY v.verified_at DESC');
$log->execute([$produk['id']]);
$riwayat = $log->fetchAll();

$judul     = 'Detail produk';
$menuAktif = 'produk';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-7">
    <div class="kotak mb-3">
      <div class="kotak-kepala">
        <h2>Data produk</h2>
        <?= status_badge($produk['status']) ?>
      </div>
      <div class="kotak-badan">
        <div class="row g-3">
          <div class="col-md-5">
            <img class="w-100" style="border-radius:12px;aspect-ratio:4/3;object-fit:cover;border:1px solid var(--garis)"
                 src="<?= gambar('produk', $produk['foto_produk']) ?>" alt="Foto <?= e($produk['nama_produk']) ?>">
          </div>
          <div class="col-md-7">
            <h3 class="h5 mb-1"><?= e($produk['nama_produk']) ?></h3>
            <div class="kartu-harga mb-2"><?= rupiah($produk['harga']) ?></div>
            <table class="table table-sm mb-0">
              <tbody>
                <tr><td class="bantuan">Kategori</td><td><?= e($produk['nama_kategori'] ?? 'Tanpa kategori') ?></td></tr>
                <tr><td class="bantuan">Stok</td><td><?= (int) $produk['stok'] ?></td></tr>
                <tr><td class="bantuan">WhatsApp pemesanan</td><td><?= e($produk['no_wa'] ?: $produk['no_telepon']) ?></td></tr>
                <tr><td class="bantuan">Didaftarkan</td><td><?= tanggal_id($produk['created_at'], true) ?></td></tr>
                <tr><td class="bantuan">Diperbarui</td><td><?= tanggal_id($produk['updated_at'], true) ?></td></tr>
              </tbody>
            </table>
          </div>
          <div class="col-12">
            <h4 class="h6 mb-1">Deskripsi</h4>
            <p class="mb-0"><?= nl2br(e($produk['deskripsi'])) ?></p>
          </div>
          <?php if ($produk['status'] === ST_DITOLAK && $produk['alasan_penolakan']): ?>
            <div class="col-12">
              <div class="alert alert-danger mb-0"><strong>Alasan penolakan saat ini:</strong> <?= e($produk['alasan_penolakan']) ?></div>
            </div>
          <?php endif; ?>
        </div>

        <div class="d-flex flex-wrap gap-2 mt-3">
          <a class="btn btn-garis btn-sm px-3" href="<?= url('admin/produk-edit.php?id=' . (int) $produk['id']) ?>">Edit data produk</a>
          <?php if ($produk['status'] === ST_DISETUJUI): ?>
            <a class="btn btn-garis btn-sm px-3" target="_blank" href="<?= url('produk-detail.php?id=' . (int) $produk['id']) ?>">Lihat di halaman publik</a>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="kotak" id="verifikasi">
      <div class="kotak-kepala"><h2>Verifikasi produk</h2></div>
      <div class="kotak-badan">
        <form method="post" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="aksi" value="verifikasi">
          <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">

          <div class="mb-3" style="max-width:320px">
            <label class="form-label" for="pilihStatus">Keputusan</label>
            <select class="form-select" id="pilihStatus" name="status">
              <option value="disetujui" <?= $produk['status'] === ST_DISETUJUI ? 'selected' : '' ?>>Setujui — produk tampil di website</option>
              <option value="ditolak"   <?= $produk['status'] === ST_DITOLAK ? 'selected' : '' ?>>Tolak — minta perbaikan data</option>
            </select>
          </div>

          <div class="mb-3 d-none" id="kotakAlasan">
            <label class="form-label" for="catatan">Alasan penolakan</label>
            <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: Foto produk buram dan harga belum diisi dengan benar."><?= e($produk['alasan_penolakan']) ?></textarea>
            <div class="bantuan mt-1">Alasan ini ditampilkan di dashboard pemilik UMKM.</div>
          </div>

          <button class="btn btn-utama px-4" type="submit">Simpan keputusan</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="kotak mb-3">
      <div class="kotak-kepala"><h2>UMKM pendaftar</h2></div>
      <div class="kotak-badan">
        <div class="fw-bold"><?= e($produk['nama_usaha']) ?></div>
        <div class="bantuan mb-2"><?= e($produk['nama_pemilik']) ?> &middot; <?= e($produk['no_telepon']) ?></div>
        <div class="bantuan mb-3"><?= e($produk['alamat']) ?></div>
        <div class="d-flex flex-wrap gap-2">
          <a class="btn btn-garis btn-sm px-3" href="<?= url('admin/umkm-detail.php?id=' . (int) $produk['id_umkm']) ?>">Lihat profil UMKM</a>
          <a class="btn btn-wa btn-sm px-3" target="_blank" rel="noopener" href="<?= e(link_wa($produk['no_telepon'], 'Halo ' . $produk['nama_usaha'] . ', kami ingin mengonfirmasi data produk "' . $produk['nama_produk'] . '".')) ?>"><i class="bi bi-whatsapp me-1"></i>Hubungi</a>
        </div>
      </div>
    </div>

    <div class="kotak">
      <div class="kotak-kepala"><h2>Riwayat verifikasi</h2></div>
      <div class="kotak-badan">
        <?php if ($riwayat): ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($riwayat as $r): ?>
              <li class="mb-3 pb-3 border-bottom">
                <?= status_badge($r['status']) ?>
                <div class="bantuan mt-1"><?= tanggal_id($r['verified_at'], true) ?> &middot; <?= e($r['nama_admin'] ?? 'Pemilik UMKM') ?></div>
                <?php if ($r['catatan']): ?><div class="small mt-1"><?= e($r['catatan']) ?></div><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="bantuan mb-0">Produk ini belum pernah diverifikasi.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
