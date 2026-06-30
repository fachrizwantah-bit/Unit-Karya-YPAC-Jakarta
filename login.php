<?php
/**
 * HALAMAN LOGIN
 * Pengguna memasukkan email & password. Jika cocok dengan data di
 * tabel users, pengguna diarahkan ke dashboard sesuai role-nya.
 */
require_once 'config/fungsi.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) { header("Location: dashboard.php"); exit; }

$pesan_error = "";

// Proses ketika tombol "Masuk" ditekan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $pesan_error = "Email dan password wajib diisi.";
    } else {
        // Cari pengguna berdasarkan email (memakai prepared statement agar aman)
        $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $hasil = mysqli_stmt_get_result($stmt);
        $user  = mysqli_fetch_assoc($hasil);
        mysqli_stmt_close($stmt);

        // Verifikasi password
        if ($user && password_verify($password, $user['password'])) {
            // Simpan data login ke session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nama']    = $user['nama'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];
            header("Location: dashboard.php");
            exit;
        } else {
            $pesan_error = "Email atau password salah.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — Sistem Pengadaan Barang YPAC Jakarta</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
  <div class="login-page">
    <div class="auth-shell">

      <!-- Panel brand kiri -->
      <aside class="auth-art">
        <span class="blob b1"></span>
        <span class="blob b2"></span>
        <span class="blob b3"></span>

        <div class="mark">YP</div>

        <div class="art-head">
          <h1>Pengadaan Barang yang Rapi & Transparan</h1>
          <p>Satu tempat untuk mengajukan, meninjau, dan memantau kebutuhan barang Unit Karya YPAC Jakarta.</p>
        </div>

        <ul class="art-points">
          <li>Ajukan kebutuhan barang dalam hitungan menit</li>
          <li>Persetujuan berjenjang yang tercatat rapi</li>
          <li>Laporan pengadaan siap diekspor</li>
        </ul>

        <div class="art-foot">© <?= date('Y') ?> Yayasan Pembinaan Anak Cacat — Jakarta</div>
      </aside>

      <!-- Panel form kanan -->
      <main class="auth-form">
        <div class="login-box">
          <div class="logo">YP</div>
          <h2>Selamat datang</h2>
          <div class="sub">Masuk untuk melanjutkan ke sistem pengadaan.</div>

          <?php if ($pesan_error): ?>
            <div class="alert alert-danger"><?= bersihkan($pesan_error) ?></div>
          <?php endif; ?>

          <form method="POST" action="login.php">
            <label class="form-label" for="email">Email</label>
            <input class="form-input" id="email" type="email" name="email" placeholder="nama@email.com"
                   value="<?= isset($_POST['email']) ? bersihkan($_POST['email']) : '' ?>" required autofocus>

            <label class="form-label" for="password">Password</label>
            <div class="input-wrap">
              <input class="form-input" id="password" type="password" name="password" placeholder="••••••••" required>
              <button type="button" class="toggle-pass" id="togglePass" aria-label="Tampilkan password">Lihat</button>
            </div>

            <button type="submit" class="btn btn-primary">Masuk</button>
          </form>

          <div class="foot-note">Akses internal — gunakan akun yang diberikan oleh administrator.</div>
        </div>
      </main>

    </div>
  </div>

  <script>
    // Tampilkan / sembunyikan password
    (function () {
      var btn = document.getElementById('togglePass');
      var inp = document.getElementById('password');
      if (btn && inp) {
        btn.addEventListener('click', function () {
          var show = inp.type === 'password';
          inp.type = show ? 'text' : 'password';
          btn.textContent = show ? 'Sembunyikan' : 'Lihat';
          btn.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
          inp.focus();
        });
      }
    })();
  </script>
</body>
</html>
