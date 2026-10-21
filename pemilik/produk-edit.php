<?php
require_once __DIR__ . '/../config/config.php';
wajib_pemilik();

$umkm = umkm_saya();
if (!$umkm) {
    redirect('pemilik/profil.php');
}

$id     = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$produk = produk_milik_saya($id, $umkm['id']);   // otorisasi: hanya produk sendiri

if (!$produk) {
    notif('gagal', 'Produk tidak ditemukan atau bukan milik Anda.');
    redirect('pemilik/produk.php');
}

$kategori = ambil_kategori();
$error    = [];
$isi = [
    'nama_produk' => $produk['nama_produk'],
    'category_id' => $produk['category_id'],
    'harga'       => (int) $produk['harga'],
    'stok'        => $produk['stok'],
    'no_wa'       => $produk['no_wa'] ?: $umkm['no_telepon'],
    'deskripsi'   => $produk['deskripsi'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach ($isi as $k => $v) {
        $isi[$k] = trim($_POST[$k] ?? '');
    }

    if (mb_strlen($isi['nama_produk']) < 3)       { $error['nama_produk'] = 'Nama produk minimal 3 huruf.'; }
    if ($isi['category_id'] === '')               { $error['category_id'] = 'Pilih salah satu kategori.'; }
    if (!is_numeric($isi['harga']) || $isi['harga'] < 0) { $error['harga'] = 'Harga harus berupa angka.'; }
    if (!ctype_digit((string) $isi['stok']))      { $error['stok'] = 'Stok harus berupa angka bulat.'; }
    if (mb_strlen($isi['deskripsi']) < 10)        { $error['deskripsi'] = 'Deskripsi minimal 10 huruf.'; }
    $digit = preg_replace('/\D/', '', $isi['no_wa']);
    if (strlen($digit) < 9 || strlen($digit) > 15) { $error['no_wa'] = 'Nomor WhatsApp belum benar.'; }

    // Foto baru bersifat opsional; kalau kosong, foto lama dipakai.
    $fotoBaru = null;
    if (!empty($_FILES['foto_produk']['name'])) {
        $pesanUpload = null;
        $fotoBaru = upload_gambar($_FILES['foto_produk'], 'produk', $pesanUpload);
        if ($pesanUpload) { $error['foto_produk'] = $pesanUpload; }
    }

    if (!$error) {
        // Setiap perubahan dikirim ulang untuk diverifikasi admin.
        $sql = 'UPDATE products
                SET category_id = ?, nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, no_wa = ?,
                    status = "menunggu", alasan_penolakan = NULL'
             . ($fotoBaru ? ', foto_produk = ?' : '')
             . ' WHERE id = ? AND umkm_id = ?';
        $data = [
            (int) $isi['category_id'], $isi['nama_produk'], $isi['deskripsi'],
            (float) $isi['harga'], (int) $isi['stok'], $isi['no_wa'],
        ];
        if ($fotoBaru) { $data[] = $fotoBaru; }
        $data[] = $produk['id'];
        $data[] = $umkm['id'];
        db()->prepare($sql)->execute($data);

        if ($fotoBaru) { hapus_gambar('produk', $produk['foto_produk']); }

        // Catat di riwayat verifikasi
        db()->prepare('INSERT INTO verification_logs (product_id, admin_id, status, catatan) VALUES (?, NULL, "menunggu", ?)')
            ->execute([$produk['id'], 'Data diperbarui oleh pemilik UMKM dan dikirim ulang untuk verifikasi.']);

        notif('sukses', 'Produk diperbarui dan dikirim ulang ke admin untuk diverifikasi.');
        redirect('pemilik/produk.php');
    }

    if ($error && $fotoBaru) { hapus_gambar('produk', $fotoBaru); }
}

// Riwayat verifikasi produk ini
$log = db()->prepare('SELECT v.*, u.nama AS nama_admin FROM verification_logs v LEFT JOIN users u ON u.id = v.admin_id WHERE v.product_id = ? ORDER BY v.verified_at DESC');
$log->execute([$produk['id']]);
$riwayat = $log->fetchAll();

$judul     = 'Edit produk';
$menuAktif = 'produk';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-8">
    <?php if ($produk['status'] === ST_DITOLAK && $produk['alasan_penolakan']): ?>
      <div class="alert alert-danger notif">
        <strong class="d-block mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Produk ini ditolak admin</strong>
        <?= e($produk['alasan_penolakan']) ?>
        <div class="mt-2 small">Perbaiki data di bawah, lalu simpan untuk mengirim ulang.</div>
      </div>
    <?php endif; ?>

    <div class="kotak">
      <div class="kotak-kepala">
        <h2>Ubah data produk</h2>
        <?= status_badge($produk['status']) ?>
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
              <select class="form-select <?= isset($error['category_id']) ? 'is-invalid' : '' ?>" id="category_id" name="category_id" required>
                <option value="">Pilih kategori</option>
                <?php foreach ($kategori as $k): ?>
                  <option value="<?= (int) $k['id'] ?>" <?= (string) $isi['category_id'] === (string) $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($error['category_id'])): ?><div class="invalid-feedback"><?= e($error['category_id']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="harga">Harga (Rp)</label>
              <input class="form-control <?= isset($error['harga']) ? 'is-invalid' : '' ?>" type="number" min="0" step="500" id="harga" name="harga" value="<?= e($isi['harga']) ?>" required>
              <?php if (isset($error['harga'])): ?><div class="invalid-feedback"><?= e($error['harga']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="stok">Stok tersedia</label>
              <input class="form-control <?= isset($error['stok']) ? 'is-invalid' : '' ?>" type="number" min="0" id="stok" name="stok" value="<?= e($isi['stok']) ?>" required>
              <?php if (isset($error['stok'])): ?><div class="invalid-feedback"><?= e($error['stok']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="no_wa">WhatsApp pemesanan</label>
              <input class="form-control <?= isset($error['no_wa']) ? 'is-invalid' : '' ?>" id="no_wa" name="no_wa" value="<?= e($isi['no_wa']) ?>" required>
              <?php if (isset($error['no_wa'])): ?><div class="invalid-feedback"><?= e($error['no_wa']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="deskripsi">Deskripsi produk</label>
              <textarea class="form-control <?= isset($error['deskripsi']) ? 'is-invalid' : '' ?>" id="deskripsi" name="deskripsi" rows="4" required><?= e($isi['deskripsi']) ?></textarea>
              <?php if (isset($error['deskripsi'])): ?><div class="invalid-feedback"><?= e($error['deskripsi']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="foto_produk">Ganti foto produk</label>
              <input class="form-control <?= isset($error['foto_produk']) ? 'is-invalid' : '' ?>" type="file" id="foto_produk" name="foto_produk" accept="image/jpeg,image/png,image/webp" data-pratinjau="#pratinjauProduk">
              <?php if (isset($error['foto_produk'])): ?><div class="invalid-feedback"><?= e($error['foto_produk']) ?></div><?php endif; ?>
              <div class="bantuan mt-1">Biarkan kosong jika foto lama masih dipakai.</div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-utama px-4" type="submit">Simpan perubahan</button>
            <a class="btn btn-garis px-4" href="<?= url('pemilik/produk.php') ?>">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="kotak mb-3">
      <div class="kotak-kepala"><h2>Foto saat ini</h2></div>
      <div class="kotak-badan text-center">
        <img id="pratinjauProduk" class="pratinjau" src="<?= gambar('produk', $produk['foto_produk']) ?>" alt="Foto <?= e($produk['nama_produk']) ?>">
      </div>
    </div>

    <div class="kotak">
      <div class="kotak-kepala"><h2>Riwayat verifikasi</h2></div>
      <div class="kotak-badan">
        <?php if ($riwayat): ?>
          <ul class="list-unstyled mb-0">
            <?php foreach ($riwayat as $r): ?>
              <li class="mb-3">
                <?= status_badge($r['status']) ?>
                <div class="bantuan mt-1"><?= tanggal_id($r['verified_at'], true) ?><?= $r['nama_admin'] ? ' &middot; ' . e($r['nama_admin']) : '' ?></div>
                <?php if ($r['catatan']): ?><div class="small"><?= e($r['catatan']) ?></div><?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="bantuan mb-0">Belum ada riwayat. Produk ini belum pernah diperiksa admin.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
