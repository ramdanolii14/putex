<?php
require_once __DIR__ . '/../config/config.php';

if (sudah_login()) {
    redirect(is_admin() ? 'admin/index.php' : 'pemilik/index.php');
}

$pesan = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();

    $email    = trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $pesan = 'Email dan kata sandi wajib diisi.';
    } else {
        $stmt = db()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // password_verify membandingkan input dengan hash di database.
        if ($user && password_verify($password, $user['password'])) {
            login_kan($user);
            notif('sukses', 'Selamat datang kembali, ' . $user['nama'] . '.');
            redirect($user['role'] === 'admin' ? 'admin/index.php' : 'pemilik/index.php');
        }
        // Pesan sengaja tidak menyebut mana yang salah.
        $pesan = 'Email atau kata sandi salah.';
    }
}

$judul   = 'Masuk';
$halaman = '';
require_once APP_ROOT . '/includes/public_header.php';
?>
<div class="container py-4 py-lg-5">
  <div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
      <div class="kotak">
        <div class="kotak-badan p-4">
          <h1 class="h4 mb-1">Masuk ke dashboard</h1>
          <p class="bantuan mb-4">Untuk admin dan pemilik UMKM.</p>

          <?php if ($pesan): ?>
            <div class="alert alert-danger notif"><i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($pesan) ?></div>
          <?php endif; ?>

          <form method="post" novalidate>
            <?php csrf_field(); ?>
            <div class="mb-3">
              <label class="form-label" for="email">Email</label>
              <input class="form-control" type="email" id="email" name="email" value="<?= e($email) ?>" required autofocus>
            </div>
            <div class="mb-4">
              <label class="form-label" for="password">Kata sandi</label>
              <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <button class="btn btn-utama w-100 py-2" type="submit">Masuk</button>
          </form>

          <p class="mt-3 mb-0 bantuan">Belum punya akun? <a href="<?= url('auth/register.php') ?>">Daftarkan usaha Anda</a>.</p>
        </div>
      </div>

      <div class="kotak mt-3">
        <div class="kotak-badan p-3">
          <p class="bantuan mb-1 fw-bold">Akun contoh untuk pengujian</p>
          <p class="bantuan mb-0">Admin: admin@umkm.test / admin123<br>Pemilik UMKM: siti@umkm.test / umkm123</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require_once APP_ROOT . '/includes/public_footer.php'; ?>
