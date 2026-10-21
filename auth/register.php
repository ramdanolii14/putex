<?php
require_once __DIR__ . '/../config/config.php';

// Yang sudah masuk tidak perlu mendaftar lagi.
if (sudah_login()) {
    redirect(is_admin() ? 'admin/index.php' : 'pemilik/index.php');
}

$error = [];
$isi   = ['nama' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();

    $nama     = trim($_POST['nama'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');
    $ulangi   = (string) ($_POST['ulangi'] ?? '');
    $isi      = ['nama' => $nama, 'email' => $email];

    // ---------- Validasi input ----------
    if ($nama === '' || mb_strlen($nama) < 3) {
        $error['nama'] = 'Nama minimal 3 huruf.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error['email'] = 'Format email belum benar, contoh: nama@email.com';
    }
    if (mb_strlen($password) < 6) {
        $error['password'] = 'Kata sandi minimal 6 karakter.';
    }
    if ($password !== $ulangi) {
        $error['ulangi'] = 'Ulangi kata sandi belum sama.';
    }

    if (!$error) {
        $cek = db()->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $cek->execute([$email]);
        if ($cek->fetch()) {
            $error['email'] = 'Email ini sudah terdaftar. Silakan masuk.';
        }
    }

    if (!$error) {
        $simpan = db()->prepare('INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, "umkm")');
        $simpan->execute([$nama, $email, password_hash($password, PASSWORD_DEFAULT)]); // password di-hash
        $id = (int) db()->lastInsertId();

        login_kan(['id' => $id, 'nama' => $nama, 'email' => $email, 'role' => 'umkm']);
        notif('sukses', 'Akun dibuat. Langkah berikutnya: lengkapi profil usaha Anda.');
        redirect('pemilik/profil.php');
    }
}

$judul   = 'Daftar akun UMKM';
$halaman = '';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4 py-lg-5">
  <div class="row justify-content-center g-4">
    <div class="col-lg-5">
      <div class="kotak">
        <div class="kotak-badan p-4">
          <h1 class="h4 mb-1">Daftarkan usaha Anda</h1>
          <p class="bantuan mb-4">Isi 4 kolom berikut. Data usaha dan produk dilengkapi setelah akun jadi.</p>

          <form method="post" novalidate>
            <?php csrf_field(); ?>
            <div class="mb-3">
              <label class="form-label" for="nama">Nama lengkap</label>
              <input class="form-control <?= isset($error['nama']) ? 'is-invalid' : '' ?>" type="text" id="nama" name="nama" value="<?= e($isi['nama']) ?>" required autofocus>
              <?php if (isset($error['nama'])): ?><div class="invalid-feedback"><?= e($error['nama']) ?></div><?php endif; ?>
            </div>
            <div class="mb-3">
              <label class="form-label" for="email">Email</label>
              <input class="form-control <?= isset($error['email']) ? 'is-invalid' : '' ?>" type="email" id="email" name="email" value="<?= e($isi['email']) ?>" required>
              <?php if (isset($error['email'])): ?><div class="invalid-feedback"><?= e($error['email']) ?></div><?php endif; ?>
              <div class="bantuan mt-1">Email dipakai untuk masuk ke dashboard.</div>
            </div>
            <div class="mb-3">
              <label class="form-label" for="password">Kata sandi</label>
              <input class="form-control <?= isset($error['password']) ? 'is-invalid' : '' ?>" type="password" id="password" name="password" required>
              <?php if (isset($error['password'])): ?><div class="invalid-feedback"><?= e($error['password']) ?></div><?php endif; ?>
            </div>
            <div class="mb-4">
              <label class="form-label" for="ulangi">Ulangi kata sandi</label>
              <input class="form-control <?= isset($error['ulangi']) ? 'is-invalid' : '' ?>" type="password" id="ulangi" name="ulangi" required>
              <?php if (isset($error['ulangi'])): ?><div class="invalid-feedback"><?= e($error['ulangi']) ?></div><?php endif; ?>
            </div>
            <button class="btn btn-utama w-100 py-2" type="submit">Buat akun</button>
          </form>

          <p class="mt-3 mb-0 bantuan">Sudah punya akun? <a href="<?= url('auth/login.php') ?>">Masuk di sini</a>.</p>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="kotak h-100">
        <div class="kotak-badan p-4">
          <h2 class="h6 mb-3">Setelah mendaftar</h2>
          <ol class="ps-3 mb-0" style="line-height:2">
            <li>Lengkapi profil usaha</li>
            <li>Tambahkan produk beserta fotonya</li>
            <li>Admin memeriksa produk Anda</li>
            <li>Produk yang disetujui tampil di halaman publik</li>
          </ol>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
