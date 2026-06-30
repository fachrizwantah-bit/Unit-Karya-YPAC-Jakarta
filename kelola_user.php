<?php
/**
 * HALAMAN KELOLA PENGGUNA (khusus Admin)
 * Admin dapat menambah pengguna baru (staff/approver/admin) dan menghapusnya.
 */
require_once 'config/fungsi.php';
wajib_role(['admin']);

$pesan = "";
$tipe  = "info";

// ----- TAMBAH PENGGUNA -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $nama  = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $role  = $_POST['role'] ?? 'staff';

    if ($nama === '' || $email === '' || $pass === '') {
        $pesan = "Semua kolom wajib diisi."; $tipe = "danger";
    } else {
        // Cek email belum dipakai
        $cek = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($cek, "s", $email);
        mysqli_stmt_execute($cek);
        mysqli_stmt_store_result($cek);
        if (mysqli_stmt_num_rows($cek) > 0) {
            $pesan = "Email sudah terdaftar."; $tipe = "danger";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);   // enkripsi password
            $stmt = mysqli_prepare($conn,
                "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "ssss", $nama, $email, $hash, $role);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            $pesan = "Pengguna '$nama' berhasil ditambahkan."; $tipe = "success";
        }
        mysqli_stmt_close($cek);
    }
}

// ----- HAPUS PENGGUNA (tidak boleh hapus diri sendiri) -----
if (isset($_GET['hapus'])) {
    $hid = (int)$_GET['hapus'];
    if ($hid === (int)$_SESSION['user_id']) {
        $pesan = "Anda tidak dapat menghapus akun Anda sendiri."; $tipe = "danger";
    } else {
        mysqli_query($conn, "DELETE FROM users WHERE id = $hid");
        $pesan = "Pengguna berhasil dihapus."; $tipe = "info";
    }
}

$data = mysqli_query($conn, "SELECT * FROM users ORDER BY id ASC");

$judul_halaman = "Kelola Pengguna";
$menu_aktif    = "user";
require 'templates/header.php';
?>

<h1 class="page-title">Kelola Pengguna</h1>

<?php if ($pesan): ?>
  <div class="alert alert-<?= $tipe ?>"><?= bersihkan($pesan) ?></div>
<?php endif; ?>

<div class="card-box">
  <h3 style="margin-top:0">Tambah Pengguna Baru</h3>
  <form method="POST" action="kelola_user.php">
    <input type="hidden" name="aksi" value="tambah">
    <div class="flex-between" style="gap:18px">
      <div style="flex:1">
        <label class="form-label">Nama Lengkap</label>
        <input class="form-input" type="text" name="nama" required>
      </div>
      <div style="flex:1">
        <label class="form-label">Email</label>
        <input class="form-input" type="email" name="email" required>
      </div>
    </div>
    <div class="flex-between" style="gap:18px">
      <div style="flex:1">
        <label class="form-label">Password</label>
        <input class="form-input" type="text" name="password" placeholder="Password awal" required>
      </div>
      <div style="flex:1">
        <label class="form-label">Role</label>
        <select class="form-input" name="role">
          <option value="staff">Staff</option>
          <option value="approver">Approver</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>
    <div style="margin-top:16px">
      <button type="submit" class="btn btn-primary">+ Tambah Pengguna</button>
    </div>
  </form>
</div>

<div class="card-box">
  <h3 style="margin-top:0">Daftar Pengguna</h3>
  <table class="tabel">
    <tr><th>No</th><th>Nama</th><th>Email</th><th>Role</th><th style="width:90px">Aksi</th></tr>
    <?php $no=1; while ($u = mysqli_fetch_assoc($data)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= bersihkan($u['nama']) ?></td>
        <td><?= bersihkan($u['email']) ?></td>
        <td><?= ucfirst($u['role']) ?></td>
        <td>
          <?php if ($u['id'] != $_SESSION['user_id']): ?>
            <a class="btn btn-danger btn-sm" href="kelola_user.php?hapus=<?= $u['id'] ?>"
               onclick="return confirm('Yakin ingin menghapus pengguna ini?')">Hapus</a>
          <?php else: ?>
            <span class="helper">(Anda)</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<?php require 'templates/footer.php'; ?>
