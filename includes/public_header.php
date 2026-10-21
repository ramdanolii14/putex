<?php
/** Bagian atas halaman publik. Halaman cukup mengisi $judul dan $halaman sebelum memanggil file ini. */
$judul   = $judul   ?? APP_NAME;
$halaman = $halaman ?? '';
$menuPublik = [
    'beranda'  => ['Beranda',  'index.php'],
    'produk'   => ['Produk',   'produk.php'],
    'umkm'     => ['UMKM',     'umkm.php'],
    'kategori' => ['Kategori', 'kategori.php'],
    'kontak'   => ['Kontak',   'kontak.php'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($judul) ?> — <?= e(APP_NAME) ?></title>
<meta name="description" content="Katalog produk UMKM lokal. Temukan makanan, minuman, dan kerajinan dari usaha kecil di sekitar Anda, lalu pesan langsung lewat WhatsApp.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link href="<?= asset('css/style.css') ?>" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-umkm sticky-top">
  <div class="container">
    <a class="navbar-brand" href="<?= url('index.php') ?>">Katalog UMKM <span>Nusantara</span></a>
    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menuUtama" aria-controls="menuUtama" aria-expanded="false" aria-label="Buka menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuUtama">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php foreach ($menuPublik as $kunci => $m): ?>
          <li class="nav-item">
            <a class="nav-link <?= $halaman === $kunci ? 'aktif' : '' ?>" href="<?= url($m[1]) ?>"><?= e($m[0]) ?></a>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="d-flex flex-wrap gap-2">
        <?php if (is_admin()): ?>
          <a class="btn btn-garis btn-sm px-3" href="<?= url('admin/index.php') ?>"><i class="bi bi-speedometer2 me-1"></i>Dashboard admin</a>
          <a class="btn btn-utama btn-sm px-3" href="<?= url('auth/logout.php') ?>">Keluar</a>
        <?php elseif (is_pemilik()): ?>
          <a class="btn btn-garis btn-sm px-3" href="<?= url('pemilik/index.php') ?>"><i class="bi bi-shop me-1"></i>Dashboard saya</a>
          <a class="btn btn-utama btn-sm px-3" href="<?= url('auth/logout.php') ?>">Keluar</a>
        <?php else: ?>
          <a class="btn btn-garis btn-sm px-3" href="<?= url('auth/login.php') ?>">Masuk</a>
          <a class="btn btn-utama btn-sm px-3" href="<?= url('auth/register.php') ?>">Daftarkan usaha</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container pt-3"><?php tampilkan_notif(); ?></div>
