<?php
require_once __DIR__ . '/config/config.php';

$judul   = 'Kontak';
$halaman = 'kontak';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-lg-6">
      <h1 class="h3 mb-3">Hubungi pengelola katalog</h1>
      <p>Katalog ini dikelola oleh tim pembinaan UMKM daerah. Hubungi kami untuk pendampingan pendaftaran, perbaikan data usaha, atau laporan produk yang tidak sesuai.</p>

      <div class="kotak mt-4">
        <div class="kotak-badan">
          <p class="mb-2"><i class="bi bi-envelope me-2" style="color:var(--hijau)"></i>admin@umkm.test</p>
          <p class="mb-2"><i class="bi bi-whatsapp me-2" style="color:var(--wa)"></i>0812-3456-7890 (jam kerja 08.00–16.00 WITA)</p>
          <p class="mb-0"><i class="bi bi-geo-alt me-2" style="color:var(--bata)"></i>Kantor Dinas Koperasi dan UMKM, Jl. Merdeka No. 1</p>
        </div>
      </div>

      <div class="kotak mt-3">
        <div class="kotak-badan">
          <h2 class="h6">Memesan produk</h2>
          <p class="bantuan mb-0">Pemesanan tidak dilakukan lewat pengelola. Buka halaman produk, lalu tekan tombol “Pesan via WhatsApp” untuk menghubungi pemilik usaha secara langsung.</p>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="kotak">
        <div class="kotak-badan">
          <h2 class="h6 mb-3">Cara mendaftarkan usaha</h2>
          <ol class="ps-3" style="line-height:2">
            <li>Buat akun lewat halaman <a href="<?= url('auth/register.php') ?>">Daftarkan usaha</a>.</li>
            <li>Lengkapi profil usaha: nama usaha, alamat, nomor WhatsApp, dan logo.</li>
            <li>Tambahkan produk beserta foto, harga, dan stok.</li>
            <li>Produk berstatus “Menunggu verifikasi” sampai diperiksa admin.</li>
            <li>Setelah disetujui, produk langsung tampil di halaman publik.</li>
          </ol>
          <p class="bantuan mb-0">Produk yang ditolak dapat diperbaiki dan dikirim ulang. Alasan penolakan tampil di dashboard Anda.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
