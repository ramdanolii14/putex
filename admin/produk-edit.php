<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$s  = db()->prepare('SELECT p.*, u.nama_usaha FROM products p JOIN umkm u ON u.id = p.umkm_id WHERE p.id = ?');
$s->execute([$id]);
$produk = $s->fetch();

if (!$produk) {
    notif('gagal', 'Produk tidak ditemukan.');
    redirect('admin/produk.php');
}

$kategori = ambil_kategori();
$error    = [];
$isi = [
    'nama_produk'      => $produk['nama_produk'],
    'category_id'      => $produk['category_id'],
    'harga'            => (int) $produk['harga'],
    'stok'             => $produk['stok'],
    'no_wa'            => $produk['no_wa'],
    'deskripsi'        => $produk['deskripsi'],
    'status'           => $produk['status'],
    'alasan_penolakan' => $produk['alasan_penolakan'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach ($isi as $k => $v) { $isi[$k] = trim($_POST[$k] ?? ''); }

    if (mb_strlen($isi['nama_produk']) < 3) { $error['nama_produk'] = 'Nama produk minimal 3 huruf.'; }
    if (!is_numeric($isi['harga']) || $isi['harga'] < 0) { $error['harga'] = 'Harga harus berupa angka.'; }
    if (!ctype_digit((string) $isi['stok'])) { $error['stok'] = 'Stok harus berupa angka bulat.'; }
    if (!in_array($isi['status'], [ST_MENUNGGU, ST_DISETUJUI, ST_DITOLAK], true)) { $error['status'] = 'Status tidak dikenal.'; }
    if ($isi['status'] === ST_DITOLAK && mb_strlen($isi['alasan_penolakan']) < 10) {
        $error['alasan_penolakan'] = 'Isi alasan penolakan minimal 10 huruf.';
    }

    $fotoBaru = null;
    if (!empty($_FILES['foto_produk']['name'])) {
        $pesanUpload = null;
        $fotoBaru = upload_gambar($_FILES['foto_produk'], 'produk', $pesanUpload);
        if ($pesanUpload) { $error['foto_produk'] = $pesanUpload; }
    }

    if (!$error) {
        $sql = 'UPDATE products SET category_id = ?, nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, no_wa = ?, status = ?, alasan_penolakan = ?'
             . ($fotoBaru ? ', foto_produk = ?' : '') . ' WHERE id = ?';
        $data = [
            $isi['category_id'] !== '' ? (int) $isi['category_id'] : null,
            $isi['nama_produk'], $isi['deskripsi'], (float) $isi['harga'], (int) $isi['stok'],
            $isi['no_wa'], $isi['status'],
            $isi['status'] === ST_DITOLAK ? $isi['alasan_penolakan'] : null,
        ];
        if ($fotoBaru) { $data[] = $fotoBaru; }
        $data[] = $produk['id'];
        db()->prepare($sql)->execute($data);

        if ($fotoBaru) { hapus_gambar('produk', $produk['foto_produk']); }

        if ($isi['status'] !== $produk['status']) {
            db()->prepare('INSERT INTO verification_logs (product_id, admin_id, status, catatan) VALUES (?, ?, ?, ?)')
                ->execute([$produk['id'], user()['id'], $isi['status'], 'Status diubah lewat halaman edit produk.']);
        }

        notif('sukses', 'Data produk diperbarui.');
        redirect('admin/produk-detail.php?id=' . $produk['id']);
    }

    if ($error && $fotoBaru) { hapus_gambar('produk', $fotoBaru); }
}

$judul     = 'Edit produk';
$menuAktif = 'produk';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="kotak">
      <div class="kotak-kepala">
        <h2>Ubah produk milik <?= e($produk['nama_usaha']) ?></h2>
      </div>
      <div class="kotak-badan">
        <form method="post" enctype="multipart/form-data" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="id" value="<?= (int) $produk['id'] ?>">
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label" for="nama_produk">Nama produk</label>
              <input class="form-control <?= isset($error['nama_produk']) ? 'is-invalid' : '' ?>" id="nama_produk" name="nama_produk" value="<?= e($isi['nama_produk']) ?>" required>
              <?php if (isset($error['nama_produk'])): ?><div class="invalid-feedback"><?= e($error['nama_produk']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="category_id">Kategori</label>
              <select class="form-select" id="category_id" name="category_id">
                <option value="">Tanpa kategori</option>
                <?php foreach ($kategori as $k): ?>
                  <option value="<?= (int) $k['id'] ?>" <?= (string) $isi['category_id'] === (string) $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="harga">Harga (Rp)</label>
              <input class="form-control <?= isset($error['harga']) ? 'is-invalid' : '' ?>" type="number" min="0" step="500" id="harga" name="harga" value="<?= e($isi['harga']) ?>" required>
              <?php if (isset($error['harga'])): ?><div class="invalid-feedback"><?= e($error['harga']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="stok">Stok</label>
              <input class="form-control <?= isset($error['stok']) ? 'is-invalid' : '' ?>" type="number" min="0" id="stok" name="stok" value="<?= e($isi['stok']) ?>" required>
              <?php if (isset($error['stok'])): ?><div class="invalid-feedback"><?= e($error['stok']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="no_wa">WhatsApp pemesanan</label>
              <input class="form-control" id="no_wa" name="no_wa" value="<?= e($isi['no_wa']) ?>">
            </div>
            <div class="col-12">
              <label class="form-label" for="deskripsi">Deskripsi</label>
              <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4"><?= e($isi['deskripsi']) ?></textarea>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="pilihStatus">Status verifikasi</label>
              <select class="form-select" id="pilihStatus" name="status">
                <?php foreach ([ST_MENUNGGU, ST_DISETUJUI, ST_DITOLAK] as $st): ?>
                  <option value="<?= e($st) ?>" <?= $isi['status'] === $st ? 'selected' : '' ?>><?= e(status_teks($st)) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="foto_produk">Ganti foto produk</label>
              <input class="form-control <?= isset($error['foto_produk']) ? 'is-invalid' : '' ?>" type="file" id="foto_produk" name="foto_produk" accept="image/jpeg,image/png,image/webp" data-pratinjau="#pratinjauProduk">
              <?php if (isset($error['foto_produk'])): ?><div class="invalid-feedback"><?= e($error['foto_produk']) ?></div><?php endif; ?>
            </div>
            <div class="col-12 <?= $isi['status'] === ST_DITOLAK ? '' : 'd-none' ?>" id="kotakAlasan">
              <label class="form-label" for="alasan_penolakan">Alasan penolakan</label>
              <textarea class="form-control <?= isset($error['alasan_penolakan']) ? 'is-invalid' : '' ?>" id="alasan_penolakan" name="alasan_penolakan" rows="3"><?= e($isi['alasan_penolakan']) ?></textarea>
              <?php if (isset($error['alasan_penolakan'])): ?><div class="invalid-feedback"><?= e($error['alasan_penolakan']) ?></div><?php endif; ?>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-utama px-4" type="submit">Simpan perubahan</button>
            <a class="btn btn-garis px-4" href="<?= url('admin/produk-detail.php?id=' . (int) $produk['id']) ?>">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Foto saat ini</h2></div>
      <div class="kotak-badan text-center">
        <img id="pratinjauProduk" class="pratinjau" src="<?= gambar('produk', $produk['foto_produk']) ?>" alt="Foto produk">
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
