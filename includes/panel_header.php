<?php
/**
 * Layout dashboard (dipakai bersama oleh Admin dan Pemilik UMKM).
 * Halaman mengisi $judul dan $menuAktif sebelum memanggil file ini.
 */
wajib_login();

$judul     = $judul     ?? 'Dashboard';
$menuAktif = $menuAktif ?? '';
$u         = user();

if (is_admin()) {
    $menungguCount = (int) db()->query("SELECT COUNT(*) FROM products WHERE status = 'menunggu'")->fetchColumn();
    $menu = [
        'dashboard' => ['Dashboard',     'admin/index.php',    'speedometer2', null],
        'umkm'      => ['Data UMKM',     'admin/umkm.php',     'shop',         null],
        'produk'    => ['Semua Produk',  'admin/produk.php',   'box-seam',     null],
        'verifikasi'=> ['Verifikasi',    'admin/produk.php?status=menunggu', 'patch-check', $menungguCount ?: null],
        'kategori'  => ['Kategori',      'admin/kategori.php', 'tags',         null],
        'laporan'   => ['Laporan',       'admin/laporan.php',  'file-earmark-text', null],
    ];
    $peran = 'Admin';
} else {
    $menu = [
        'dashboard' => ['Dashboard',      'pemilik/index.php',         'speedometer2',  null],
        'profil'    => ['Profil Usaha',   'pemilik/profil.php',        'shop',          null],
        'produk'    => ['Produk Saya',    'pemilik/produk.php',        'box-seam',      null],
        'tambah'    => ['Tambah Produk',  'pemilik/produk-tambah.php', 'plus-circle',   null],
    ];
    $peran = 'Pemilik UMKM';
}

/** Menggambar daftar menu (dipakai sidebar desktop dan menu mobile). */
function gambar_menu(array $menu, $menuAktif)
{
    foreach ($menu as $kunci => $m) {
        $aktif = $menuAktif === $kunci ? ' aktif' : '';
        echo '<a class="menu' . $aktif . '" href="' . url($m[1]) . '">'
           . '<i class="bi bi-' . e($m[2]) . '"></i><span>' . e($m[0]) . '</span>';
        if (!empty($m[3])) {
            echo '<span class="badge rounded-pill ms-auto" style="background:#e5a32e;color:#3a2b06">' . (int) $m[3] . '</span>';
        }
        echo '</a>';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> — <?= e(APP_NAME) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="panel-wrap">

  <!-- Sidebar (layar besar) -->
  <aside class="sidebar">
    <a class="merek" href="<?= url('index.php') ?>">Katalog UMKM <span>Nusantara</span></a>
    <div class="peran"><?= e($peran) ?></div>
    <?php gambar_menu($menu, $menuAktif); ?>
    <hr>
    <a class="menu" href="<?= url('index.php') ?>"><i class="bi bi-globe2"></i><span>Lihat website</span></a>
    <a class="menu" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></a>
  </aside>

  <div class="panel-isi">
    <!-- Bar atas -->
    <header class="panel-atas">
      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-garis btn-sm d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuPanel" aria-controls="menuPanel" aria-label="Buka menu">
          <i class="bi bi-list"></i>
        </button>
        <div>
          <h1 class="h5 mb-0"><?= e($judul) ?></h1>
        </div>
      </div>
      <div class="dropdown">
        <button class="btn btn-garis btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="bi bi-person-circle me-1"></i><span class="d-none d-sm-inline"><?= e($u['nama']) ?></span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><span class="dropdown-item-text small text-muted"><?= e($u['email']) ?></span></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="<?= url('index.php') ?>">Lihat website</a></li>
          <li><a class="dropdown-item" href="<?= url('auth/logout.php') ?>">Keluar</a></li>
        </ul>
      </div>
    </header>

    <!-- Menu mobile -->
    <div class="offcanvas offcanvas-start text-bg-dark" tabindex="-1" id="menuPanel" style="background:#17513a !important;max-width:80vw">
      <div class="offcanvas-header">
        <span class="fw-bold">Menu <?= e($peran) ?></span>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
      </div>
      <div class="offcanvas-body sidebar" style="width:100%;height:auto;position:static;padding-top:0">
        <?php gambar_menu($menu, $menuAktif); ?>
        <hr>
        <a class="menu" href="<?= url('index.php') ?>"><i class="bi bi-globe2"></i><span>Lihat website</span></a>
        <a class="menu" href="<?= url('auth/logout.php') ?>"><i class="bi bi-box-arrow-right"></i><span>Keluar</span></a>
      </div>
    </div>

    <main class="panel-konten">
      <?php tampilkan_notif(); ?>
