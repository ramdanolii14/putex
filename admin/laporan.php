<?php
require_once __DIR__ . '/../config/config.php';
wajib_admin();

// ---------- Rentang tanggal ----------
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dari))   { $dari = date('Y-m-01'); }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $sampai)) { $sampai = date('Y-m-d'); }
$rentang = [':dari' => $dari, ':sampai' => $sampai];

// ---------- Ringkasan ----------
$ringkas = db()->prepare(
    "SELECT COUNT(*) AS total,
            SUM(status = 'menunggu')  AS menunggu,
            SUM(status = 'disetujui') AS disetujui,
            SUM(status = 'ditolak')   AS ditolak
     FROM products WHERE DATE(created_at) BETWEEN :dari AND :sampai"
);
$ringkas->execute($rentang);
$p = $ringkas->fetch();

$umkmBaru = db()->prepare('SELECT COUNT(*) FROM umkm WHERE DATE(created_at) BETWEEN :dari AND :sampai');
$umkmBaru->execute($rentang);
$jumlahUmkmBaru = (int) $umkmBaru->fetchColumn();

// ---------- Rekap per UMKM ----------
$perUmkm = db()->prepare(
    "SELECT u.nama_usaha, u.nama_pemilik, u.no_telepon,
            COUNT(p.id) AS total,
            SUM(p.status = 'disetujui') AS disetujui,
            SUM(p.status = 'menunggu')  AS menunggu,
            SUM(p.status = 'ditolak')   AS ditolak
     FROM umkm u
     LEFT JOIN products p ON p.umkm_id = u.id AND DATE(p.created_at) BETWEEN :dari AND :sampai
     GROUP BY u.id
     ORDER BY total DESC, u.nama_usaha ASC"
);
$perUmkm->execute($rentang);
$dataUmkm = $perUmkm->fetchAll();

// ---------- Rekap per kategori ----------
$perKategori = db()->prepare(
    "SELECT c.nama_kategori,
            COUNT(p.id) AS total,
            SUM(p.status = 'disetujui') AS disetujui
     FROM categories c
     LEFT JOIN products p ON p.category_id = c.id AND DATE(p.created_at) BETWEEN :dari AND :sampai
     GROUP BY c.id ORDER BY total DESC"
);
$perKategori->execute($rentang);
$dataKategori = $perKategori->fetchAll();

// ---------- Unduh CSV ----------
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan-umkm-' . $dari . '-sampai-' . $sampai . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Laporan Katalog UMKM', $dari . ' s/d ' . $sampai]);
    fputcsv($out, []);
    fputcsv($out, ['Nama Usaha', 'Pemilik', 'Kontak', 'Total Produk', 'Disetujui', 'Menunggu', 'Ditolak']);
    foreach ($dataUmkm as $r) {
        fputcsv($out, [$r['nama_usaha'], $r['nama_pemilik'], $r['no_telepon'], (int) $r['total'], (int) $r['disetujui'], (int) $r['menunggu'], (int) $r['ditolak']]);
    }
    fclose($out);
    exit;
}

$judul     = 'Laporan';
$menuAktif = 'laporan';
require_once APP_ROOT . '/includes/panel_header.php';
?>
<div class="kotak mb-3 tanpa-cetak">
  <div class="kotak-badan">
    <form class="row g-2 align-items-end" method="get">
      <div class="col-md-3">
        <label class="form-label" for="dari">Dari tanggal</label>
        <input class="form-control" type="date" id="dari" name="dari" value="<?= e($dari) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="sampai">Sampai tanggal</label>
        <input class="form-control" type="date" id="sampai" name="sampai" value="<?= e($sampai) ?>">
      </div>
      <div class="col-md-6 d-flex gap-2">
        <button class="btn btn-utama px-4" type="submit">Tampilkan</button>
        <a class="btn btn-garis px-3" href="<?= url('admin/laporan.php?' . http_build_query(['dari' => $dari, 'sampai' => $sampai, 'export' => 'csv'])) ?>"><i class="bi bi-download me-1"></i>Unduh CSV</a>
        <button class="btn btn-garis px-3" type="button" onclick="window.print()"><i class="bi bi-printer me-1"></i>Cetak</button>
      </div>
    </form>
  </div>
</div>

<h2 class="h5 mb-3">Laporan <?= tanggal_id($dari) ?> sampai <?= tanggal_id($sampai) ?></h2>

<div class="row g-3 mb-4">
  <div class="col-6 col-lg"><div class="statistik tepi-hijau"><div class="angka"><?= $jumlahUmkmBaru ?></div><div class="label">UMKM baru</div></div></div>
  <div class="col-6 col-lg"><div class="statistik tepi-tinta"><div class="angka"><?= (int) $p['total'] ?></div><div class="label">Produk didaftarkan</div></div></div>
  <div class="col-6 col-lg"><div class="statistik tepi-kunyit"><div class="angka"><?= (int) $p['menunggu'] ?></div><div class="label">Menunggu verifikasi</div></div></div>
  <div class="col-6 col-lg"><div class="statistik tepi-hijau"><div class="angka"><?= (int) $p['disetujui'] ?></div><div class="label">Disetujui</div></div></div>
  <div class="col-6 col-lg"><div class="statistik tepi-bata"><div class="angka"><?= (int) $p['ditolak'] ?></div><div class="label">Ditolak</div></div></div>
</div>

<div class="row g-3">
  <div class="col-xl-7">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Rekap per UMKM</h2></div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Usaha</th><th>Kontak</th><th>Total</th><th>Disetujui</th><th>Menunggu</th><th>Ditolak</th></tr></thead>
          <tbody>
          <?php foreach ($dataUmkm as $r): ?>
            <tr>
              <td>
                <div class="fw-bold"><?= e($r['nama_usaha']) ?></div>
                <div class="bantuan"><?= e($r['nama_pemilik']) ?></div>
              </td>
              <td class="bantuan"><?= e($r['no_telepon']) ?></td>
              <td><?= (int) $r['total'] ?></td>
              <td><?= (int) $r['disetujui'] ?></td>
              <td><?= (int) $r['menunggu'] ?></td>
              <td><?= (int) $r['ditolak'] ?></td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$dataUmkm): ?><tr><td colspan="6" class="text-center bantuan py-4">Belum ada data.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="col-xl-5">
    <div class="kotak">
      <div class="kotak-kepala"><h2>Rekap per kategori</h2></div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead><tr><th>Kategori</th><th>Produk didaftarkan</th><th>Disetujui</th></tr></thead>
          <tbody>
          <?php foreach ($dataKategori as $r): ?>
            <tr>
              <td><?= e($r['nama_kategori']) ?></td>
              <td><?= (int) $r['total'] ?></td>
              <td><?= (int) $r['disetujui'] ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/panel_footer.php'; ?>
