<?php
require_once __DIR__ . '/config/config.php';

// ---------- Ambil filter dari URL ----------
$q          = trim($_GET['q'] ?? '');
$kategoriId = (int) ($_GET['kategori'] ?? 0);
$urut       = $_GET['urut'] ?? 'terbaru';
$halamanKe  = max(1, (int) ($_GET['hal'] ?? 1));
$perHalaman = 12;

$where  = ["p.status = 'disetujui'"];
$params = [];

if ($q !== '') {
    $where[] = '(p.nama_produk LIKE :q1 OR p.deskripsi LIKE :q2 OR u.nama_usaha LIKE :q3)';
    $params[':q1'] = $params[':q2'] = $params[':q3'] = '%' . $q . '%';
}
if ($kategoriId > 0) {
    $where[] = 'p.category_id = :kat';
    $params[':kat'] = $kategoriId;
}

$pilihanUrut = [
    'terbaru'  => ['Terbaru',          'p.updated_at DESC'],
    'termurah' => ['Harga termurah',   'p.harga ASC'],
    'termahal' => ['Harga tertinggi',  'p.harga DESC'],
    'nama'     => ['Nama A-Z',         'p.nama_produk ASC'],
];
$orderBy = $pilihanUrut[$urut][1] ?? $pilihanUrut['terbaru'][1];
$sqlWhere = 'WHERE ' . implode(' AND ', $where);

// ---------- Hitung total untuk pagination ----------
$hitung = db()->prepare("SELECT COUNT(*) FROM products p JOIN umkm u ON u.id = p.umkm_id $sqlWhere");
$hitung->execute($params);
$total       = (int) $hitung->fetchColumn();
$totalHalaman = max(1, (int) ceil($total / $perHalaman));
$halamanKe   = min($halamanKe, $totalHalaman);
$offset      = ($halamanKe - 1) * $perHalaman;

// LIMIT/OFFSET dipaksa menjadi integer, jadi tetap aman dari SQL Injection.
$sql = "SELECT p.id, p.nama_produk, p.harga, p.stok, p.foto_produk,
               u.nama_usaha, c.nama_kategori
        FROM products p
        JOIN umkm u ON u.id = p.umkm_id
        LEFT JOIN categories c ON c.id = p.category_id
        $sqlWhere
        ORDER BY $orderBy
        LIMIT " . (int) $perHalaman . " OFFSET " . (int) $offset;
$stmt = db()->prepare($sql);
$stmt->execute($params);
$produk = $stmt->fetchAll();

$kategori = ambil_kategori();

/** Membuat link pagination sambil mempertahankan filter. */
function link_halaman($nomor)
{
    $qs = $_GET;
    $qs['hal'] = $nomor;
    return url('produk.php?' . http_build_query($qs));
}

$judul   = 'Daftar produk';
$halaman = 'produk';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4">
  <h1 class="h3 mb-1">Daftar produk</h1>
  <p class="bantuan"><?= $total ?> produk tayang<?= $q !== '' ? ' untuk pencarian "' . e($q) . '"' : '' ?>.</p>

  <form class="kotak mb-4" method="get" action="<?= url('produk.php') ?>">
    <div class="kotak-badan">
      <div class="row g-2 align-items-end">
        <div class="col-md-5">
          <label class="form-label" for="q">Cari produk atau UMKM</label>
          <input class="form-control" type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Nama produk, bahan, atau nama usaha">
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
        <div class="col-md-2">
          <label class="form-label" for="urut">Urutkan</label>
          <select class="form-select" id="urut" name="urut">
            <?php foreach ($pilihanUrut as $kunci => $o): ?>
              <option value="<?= e($kunci) ?>" <?= $urut === $kunci ? 'selected' : '' ?>><?= e($o[0]) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2 d-grid">
          <button class="btn btn-utama" type="submit">Terapkan</button>
        </div>
      </div>
    </div>
  </form>

  <?php if ($produk): ?>
    <div class="row g-3 g-lg-4">
      <?php foreach ($produk as $p) { require APP_ROOT . '/includes/kartu_produk.php'; } ?>
    </div>

    <?php if ($totalHalaman > 1): ?>
      <nav class="mt-4" aria-label="Navigasi halaman">
        <ul class="pagination justify-content-center">
          <li class="page-item <?= $halamanKe <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= link_halaman($halamanKe - 1) ?>">Sebelumnya</a>
          </li>
          <?php for ($i = 1; $i <= $totalHalaman; $i++): ?>
            <li class="page-item <?= $i === $halamanKe ? 'active' : '' ?>"><a class="page-link" href="<?= link_halaman($i) ?>"><?= $i ?></a></li>
          <?php endfor; ?>
          <li class="page-item <?= $halamanKe >= $totalHalaman ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= link_halaman($halamanKe + 1) ?>">Berikutnya</a>
          </li>
        </ul>
      </nav>
    <?php endif; ?>

  <?php else: ?>
    <div class="kotak"><div class="kotak-badan text-center py-5">
      <p class="fw-bold mb-2">Produk tidak ditemukan</p>
      <p class="bantuan mb-3">Coba kata kunci lain atau pilih kategori yang berbeda.</p>
      <a class="btn btn-garis btn-sm px-3" href="<?= url('produk.php') ?>">Hapus filter</a>
    </div></div>
  <?php endif; ?>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
