<footer class="footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-5">
        <h3>Katalog UMKM Nusantara</h3>
        <p class="mb-2" style="color:#cfe1d7">Tempat usaha kecil di daerah mendaftarkan produknya supaya lebih mudah ditemukan pembeli. Pemesanan dilakukan langsung ke pemilik usaha lewat WhatsApp.</p>
      </div>
      <div class="col-6 col-lg-3">
        <h3>Jelajahi</h3>
        <ul class="list-unstyled mb-0">
          <li class="mb-1"><a href="<?= url('produk.php') ?>">Semua produk</a></li>
          <li class="mb-1"><a href="<?= url('umkm.php') ?>">Daftar UMKM</a></li>
          <li class="mb-1"><a href="<?= url('kategori.php') ?>">Kategori</a></li>
          <li class="mb-1"><a href="<?= url('kontak.php') ?>">Kontak</a></li>
        </ul>
      </div>
      <div class="col-6 col-lg-4">
        <h3>Punya usaha?</h3>
        <ul class="list-unstyled mb-0">
          <li class="mb-1"><a href="<?= url('auth/register.php') ?>">Daftarkan usaha Anda</a></li>
          <li class="mb-1"><a href="<?= url('auth/login.php') ?>">Masuk ke dashboard</a></li>
        </ul>
      </div>
    </div>
    <hr style="border-color:rgba(255,255,255,.2)">
    <p class="mb-0" style="font-size:.85rem">&copy; <?= date('Y') ?> Katalog UMKM Nusantara. Dibuat untuk pembinaan UMKM daerah.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
