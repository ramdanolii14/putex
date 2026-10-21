<?php
require_once __DIR__ . '/../config/config.php';
wajib_pemilik();

$umkm = umkm_saya();
if (!$umkm) {
    notif('info', 'Lengkapi profil usaha dulu sebelum menambah produk.');
    redirect('pemilik/profil.php');
}

$kategori = ambil_kategori();
$error    = [];
$isi = [
    'nama_produk' => '',
    'category_id' => '',
    'harga'       => '',
    'stok'        => '',
    'no_wa'       => $umkm['no_telepon'],
    'deskripsi'   => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    foreach ($isi as $k => $v) {
        $isi[$k] = trim($_POST[$k] ?? '');
    }

    // ---------- Validasi ----------
    if (mb_strlen($isi['nama_produk']) < 3)       { $error['nama_produk'] = 'Nama produk minimal 3 huruf.'; }
    if ($isi['category_id'] === '')               { $error['category_id'] = 'Pilih salah satu kategori.'; }
    if (!is_numeric($isi['harga']) || $isi['harga'] < 0) { $error['harga'] = 'Harga harus berupa angka, contoh: 25000'; }
    if (!ctype_digit($isi['stok']))               { $error['stok'] = 'Stok harus berupa angka bulat, contoh: 10'; }
    if (mb_strlen($isi['deskripsi']) < 10)        { $error['deskripsi'] = 'Tulis deskripsi minimal 10 huruf agar pembeli paham.'; }
    $digit = preg_replace('/\D/', '', $isi['no_wa']);
    if (strlen($digit) < 9 || strlen($digit) > 15) { $error['no_wa'] = 'Nomor WhatsApp belum benar. Contoh: 081234567890'; }

    $foto = null;
    if (empty($_FILES['foto_produk']['name'])) {
        $error['foto_produk'] = 'Foto produk wajib diunggah.';
    } else {
        $pesanUpload = null;
        $foto = upload_gambar($_FILES['foto_produk'], 'produk', $pesanUpload);
        if ($pesanUpload) { $error['foto_produk'] = $pesanUpload; }
    }

    if (!$error) {
        db()->prepare(
            'INSERT INTO products (umkm_id, category_id, nama_produk, deskripsi, harga, stok, no_wa, foto_produk, status)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "menunggu")'
        )->execute([
            $umkm['id'], (int) $isi['category_id'], $isi['nama_produk'], $isi['deskripsi'],
            (float) $isi['harga'], (int) $isi['stok'], $isi['no_wa'], $foto,
        ]);

        notif('sukses', 'Produk ditambahkan dan dikirim ke admin. Statusnya sekarang "Menunggu verifikasi".');
        redirect('pemilik/produk.php');
    }

    if ($error && $foto) { hapus_gambar('produk', $foto); }
}

$judul     = 'Tambah produk';
$menuAktif = 'tambah';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-8">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Data produk baru</h2></div>
      <div class="kotak-badan">
        <form method="post" enctype="multipart/form-data" novalidate>
          <?php csrf_field(); ?>
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label" for="nama_produk">Nama produk</label>
              <input class="form-control <?= isset($error['nama_produk']) ? 'is-invalid' : '' ?>" id="nama_produk" name="nama_produk" value="<?= e($isi['nama_produk']) ?>" required autofocus>
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
              <div class="bantuan mt-1">Isi 0 jika sedang kosong.</div>
            </div>
            <div class="col-md-4">
              <label class="form-label" for="no_wa">WhatsApp pemesanan</label>
              <input class="form-control <?= isset($error['no_wa']) ? 'is-invalid' : '' ?>" id="no_wa" name="no_wa" value="<?= e($isi['no_wa']) ?>" required>
              <?php if (isset($error['no_wa'])): ?><div class="invalid-feedback"><?= e($error['no_wa']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="deskripsi">Deskripsi produk</label>
              <textarea class="form-control <?= isset($error['deskripsi']) ? 'is-invalid' : '' ?>" id="deskripsi" name="deskripsi" rows="4" placeholder="Bahan, ukuran atau berat, rasa, dan cara penyimpanan." required><?= e($isi['deskripsi']) ?></textarea>
              <?php if (isset($error['deskripsi'])): ?><div class="invalid-feedback"><?= e($error['deskripsi']) ?></div><?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="foto_produk">Foto produk</label>
              <input class="form-control <?= isset($error['foto_produk']) ? 'is-invalid' : '' ?>" type="file" id="foto_produk" name="foto_produk" accept="image/jpeg,image/png,image/webp" data-pratinjau="#pratinjauProduk" required>
              <?php if (isset($error['foto_produk'])): ?><div class="invalid-feedback"><?= e($error['foto_produk']) ?></div><?php endif; ?>
              <div class="bantuan mt-1">JPG, PNG, atau WEBP. Maksimal 2 MB. Foto terang dan tidak buram lebih cepat disetujui.</div>
            </div>
          </div>

          <div class="d-flex gap-2 mt-4">
            <button class="btn btn-utama px-4" type="submit">Simpan dan kirim ke admin</button>
            <a class="btn btn-garis px-4" href="<?= url('pemilik/produk.php') ?>">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="kotak mb-3">
      <div class="kotak-kepala"><h2>Pratinjau foto</h2></div>
      <div class="kotak-badan text-center">
        <img id="pratinjauProduk" class="pratinjau" src="<?= asset('img/placeholder-produk.svg') ?>" alt="Pratinjau foto produk">
      </div>
    </div>
    <div class="kotak">
      <div class="kotak-badan">
        <h2 class="h6">Setelah disimpan</h2>
        <p class="bantuan mb-0">Produk masuk antrean verifikasi admin. Anda bisa memantau statusnya di halaman Produk Saya. Produk baru tampil di katalog publik setelah disetujui.</p>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
