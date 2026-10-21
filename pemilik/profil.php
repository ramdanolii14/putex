<?php
require_once __DIR__ . '/../config/config.php';
wajib_pemilik();

$umkm  = umkm_saya();
$error = [];

// Nilai awal form
$isi = [
    'nama_usaha'   => $umkm['nama_usaha']   ?? '',
    'nama_pemilik' => $umkm['nama_pemilik'] ?? user()['nama'],
    'no_telepon'   => $umkm['no_telepon']   ?? '',
    'alamat'       => $umkm['alamat']       ?? '',
    'deskripsi'    => $umkm['deskripsi']    ?? '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();

    foreach ($isi as $k => $v) {
        $isi[$k] = trim($_POST[$k] ?? '');
    }

    if (mb_strlen($isi['nama_usaha']) < 3)   { $error['nama_usaha']   = 'Nama usaha minimal 3 huruf.'; }
    if (mb_strlen($isi['nama_pemilik']) < 3) { $error['nama_pemilik'] = 'Nama pemilik minimal 3 huruf.'; }
    $digit = preg_replace('/\D/', '', $isi['no_telepon']);
    if (strlen($digit) < 9 || strlen($digit) > 15) {
        $error['no_telepon'] = 'Nomor WhatsApp belum benar. Contoh: 081234567890';
    }
    if ($isi['alamat'] === '') { $error['alamat'] = 'Alamat usaha wajib diisi.'; }

    // Upload logo (opsional)
    $fotoBaru = null;
    if (!empty($_FILES['foto_umkm']['name'])) {
        $pesanUpload = null;
        $fotoBaru = upload_gambar($_FILES['foto_umkm'], 'umkm', $pesanUpload);
        if ($pesanUpload) {
            $error['foto_umkm'] = $pesanUpload;
        }
    }

    if (!$error) {
        if ($umkm) {
            $sql = 'UPDATE umkm SET nama_usaha = ?, nama_pemilik = ?, no_telepon = ?, alamat = ?, deskripsi = ?'
                 . ($fotoBaru ? ', foto_umkm = ?' : '') . ' WHERE id = ? AND user_id = ?';
            $data = [$isi['nama_usaha'], $isi['nama_pemilik'], $isi['no_telepon'], $isi['alamat'], $isi['deskripsi']];
            if ($fotoBaru) { $data[] = $fotoBaru; }
            $data[] = $umkm['id'];
            $data[] = user()['id'];
            db()->prepare($sql)->execute($data);

            if ($fotoBaru) { hapus_gambar('umkm', $umkm['foto_umkm']); }
            notif('sukses', 'Profil usaha berhasil diperbarui.');
        } else {
            db()->prepare(
                'INSERT INTO umkm (user_id, nama_usaha, nama_pemilik, no_telepon, alamat, deskripsi, foto_umkm, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, "menunggu")'
            )->execute([user()['id'], $isi['nama_usaha'], $isi['nama_pemilik'], $isi['no_telepon'], $isi['alamat'], $isi['deskripsi'], $fotoBaru]);
            notif('sukses', 'Profil usaha tersimpan dan menunggu verifikasi admin. Anda bisa menambahkan produk, tetapi usaha belum tampil di katalog publik sampai disetujui.');
        }
        redirect('pemilik/index.php');
    }

    if ($error && $fotoBaru) { hapus_gambar('umkm', $fotoBaru); }
}

$judul     = $umkm ? 'Profil usaha' : 'Lengkapi profil usaha';
$menuAktif = 'profil';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Data usaha</h2></div>
      <div class="kotak-badan">
        <?php if (!$umkm): ?>
          <div class="alert alert-info notif mb-4"><i class="bi bi-info-circle-fill me-2"></i>Isi data ini dulu sebelum menambahkan produk.</div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" novalidate>
          <?php csrf_field(); ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="nama_usaha">Nama usaha</label>
              <input class="form-control <?= isset($error['nama_usaha']) ? 'is-invalid' : '' ?>" id="nama_usaha" name="nama_usaha" value="<?= e($isi['nama_usaha']) ?>" required>
              <?php if (isset($error['nama_usaha'])): ?><div class="invalid-feedback"><?= e($error['nama_usaha']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nama_pemilik">Nama pemilik</label>
              <input class="form-control <?= isset($error['nama_pemilik']) ? 'is-invalid' : '' ?>" id="nama_pemilik" name="nama_pemilik" value="<?= e($isi['nama_pemilik']) ?>" required>
              <?php if (isset($error['nama_pemilik'])): ?><div class="invalid-feedback"><?= e($error['nama_pemilik']) ?></div><?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="no_telepon">Nomor telepon / WhatsApp</label>
              <input class="form-control <?= isset($error['no_telepon']) ? 'is-invalid' : '' ?>" id="no_telepon" name="no_telepon" value="<?= e($isi['no_telepon']) ?>" placeholder="081234567890" required>
              <?php if (isset($error['no_telepon'])): ?><div class="invalid-feedback"><?= e($error['no_telepon']) ?></div><?php endif; ?>
              <div class="bantuan mt-1">Nomor ini dipakai pembeli untuk menghubungi Anda.</div>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="foto_umkm">Logo atau foto usaha</label>
              <input class="form-control <?= isset($error['foto_umkm']) ? 'is-invalid' : '' ?>" type="file" id="foto_umkm" name="foto_umkm" accept="image/jpeg,image/png,image/webp" data-pratinjau="#pratinjauLogo">
              <?php if (isset($error['foto_umkm'])): ?><div class="invalid-feedback"><?= e($error['foto_umkm']) ?></div><?php endif; ?>
              <div class="bantuan mt-1">JPG, PNG, atau WEBP. Maksimal 2 MB.</div>
            </div>
            <div class="col-12">
              <label class="form-label" for="alamat">Alamat usaha</label>
              <textarea class="form-control <?= isset($error['alamat']) ? 'is-invalid' : '' ?>" id="alamat" name="alamat" rows="2" required><?= e($isi['alamat']) ?></textarea>
              <?php if (isset($error['alamat'])): ?><div class="invalid-feedback"><?= e($error['alamat']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="deskripsi">Deskripsi usaha</label>
              <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Ceritakan singkat tentang usaha Anda, sejak kapan berjalan, dan apa yang dijual."><?= e($isi['deskripsi']) ?></textarea>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-utama px-4" type="submit">Simpan profil</button>
            <a class="btn btn-garis px-4" href="<?= url('pemilik/index.php') ?>">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Tampilan logo</h2></div>
      <div class="kotak-badan text-center">
        <img id="pratinjauLogo" class="pratinjau mb-2" src="<?= gambar('umkm', $umkm['foto_umkm'] ?? null) ?>" alt="Pratinjau logo usaha">
        <p class="bantuan mb-0">Gunakan gambar persegi agar tampil rapi di katalog.</p>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
