<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

$error = [];
$isi   = ['nama_kategori' => '', 'deskripsi' => ''];

// Mode edit bila ada ?edit=id
$editId = (int) ($_GET['edit'] ?? 0);
$sedangEdit = null;
if ($editId) {
    $s = db()->prepare('SELECT * FROM categories WHERE id = ?');
    $s->execute([$editId]);
    $sedangEdit = $s->fetch();
    if ($sedangEdit) {
        $isi = ['nama_kategori' => $sedangEdit['nama_kategori'], 'deskripsi' => $sedangEdit['deskripsi']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $aksi = $_POST['aksi'] ?? '';

    // ---------- Hapus kategori ----------
    if ($aksi === 'hapus') {
        $id = (int) ($_POST['id'] ?? 0);
        $s = db()->prepare('SELECT * FROM categories WHERE id = ?');
        $s->execute([$id]);
        $data = $s->fetch();

        if (!$data) {
            notif('gagal', 'Kategori tidak ditemukan.');
        } else {
            // Produk yang memakai kategori ini otomatis menjadi "Tanpa kategori".
            db()->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            notif('sukses', 'Kategori "' . $data['nama_kategori'] . '" dihapus.');
        }
        redirect('admin/kategori.php');
    }

    // ---------- Tambah / ubah kategori ----------
    $isi['nama_kategori'] = trim($_POST['nama_kategori'] ?? '');
    $isi['deskripsi']     = trim($_POST['deskripsi'] ?? '');
    $id                   = (int) ($_POST['id'] ?? 0);

    if (mb_strlen($isi['nama_kategori']) < 3) {
        $error['nama_kategori'] = 'Nama kategori minimal 3 huruf.';
    } else {
        $cek = db()->prepare('SELECT id FROM categories WHERE nama_kategori = ? AND id <> ?');
        $cek->execute([$isi['nama_kategori'], $id]);
        if ($cek->fetch()) {
            $error['nama_kategori'] = 'Kategori dengan nama itu sudah ada.';
        }
    }

    if (!$error) {
        if ($aksi === 'ubah' && $id) {
            db()->prepare('UPDATE categories SET nama_kategori = ?, deskripsi = ? WHERE id = ?')
                ->execute([$isi['nama_kategori'], $isi['deskripsi'], $id]);
            notif('sukses', 'Kategori diperbarui.');
        } else {
            db()->prepare('INSERT INTO categories (nama_kategori, deskripsi) VALUES (?, ?)')
                ->execute([$isi['nama_kategori'], $isi['deskripsi']]);
            notif('sukses', 'Kategori baru ditambahkan.');
        }
        redirect('admin/kategori.php');
    }
}

$daftar = db()->query(
    'SELECT c.*, COUNT(p.id) AS jumlah
     FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.nama_kategori ASC'
)->fetchAll();

$judul     = 'Kelola kategori';
$menuAktif = 'kategori';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="row g-3">
  <div class="col-lg-4">
    <div class="kotak">
      <div class="kotak-kepala"><h2><?= $sedangEdit ? 'Ubah kategori' : 'Tambah kategori' ?></h2></div>
      <div class="kotak-badan">
        <form method="post" novalidate>
          <?php csrf_field(); ?>
          <input type="hidden" name="aksi" value="<?= $sedangEdit ? 'ubah' : 'tambah' ?>">
          <input type="hidden" name="id" value="<?= (int) ($sedangEdit['id'] ?? 0) ?>">
          <div class="mb-3">
            <label class="form-label" for="nama_kategori">Nama kategori</label>
            <input class="form-control <?= isset($error['nama_kategori']) ? 'is-invalid' : '' ?>" id="nama_kategori" name="nama_kategori" value="<?= e($isi['nama_kategori']) ?>" required>
            <?php if (isset($error['nama_kategori'])): ?><div class="invalid-feedback"><?= e($error['nama_kategori']) ?></div><?php endif; ?>
          </div>
          <div class="mb-3">
            <label class="form-label" for="deskripsi">Deskripsi singkat</label>
            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"><?= e($isi['deskripsi']) ?></textarea>
          </div>
          <div class="d-flex gap-2">
            <button class="btn btn-utama px-4" type="submit"><?= $sedangEdit ? 'Simpan perubahan' : 'Tambah kategori' ?></button>
            <?php if ($sedangEdit): ?>
              <a class="btn btn-garis px-3" href="<?= url('admin/kategori.php') ?>">Batal</a>
            <?php endif; ?>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Daftar kategori (<?= count($daftar) ?>)</h2></div>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead><tr><th>Kategori</th><th>Deskripsi</th><th>Produk</th><th class="text-end">Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($daftar as $k): ?>
            <tr>
              <td class="fw-bold"><?= e($k['nama_kategori']) ?></td>
              <td class="bantuan"><?= e($k['deskripsi']) ?></td>
              <td><?= (int) $k['jumlah'] ?></td>
              <td class="text-end text-nowrap">
                <a class="btn btn-garis btn-sm" href="<?= url('admin/kategori.php?edit=' . (int) $k['id']) ?>"><i class="bi bi-pencil"></i></a>
                <button type="button" class="btn btn-outline-danger btn-sm tombol-hapus"
                        data-nama="kategori <?= e($k['nama_kategori']) ?>"
                        data-id="<?= (int) $k['id'] ?>"
                        data-aksi="<?= url('admin/kategori.php') ?>"><i class="bi bi-trash"></i></button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div class="kotak-badan bantuan border-top">Produk pada kategori yang dihapus akan berubah menjadi “Tanpa kategori”, bukan ikut terhapus.</div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
