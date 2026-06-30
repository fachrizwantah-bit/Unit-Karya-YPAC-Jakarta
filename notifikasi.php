<?php

require_once 'config/fungsi.php';
wajib_login();

$user_id = $_SESSION['user_id'];

// Ambil daftar notifikasi (yang belum dibaca tampil lebih dulu)
$data = mysqli_query($conn,
    "SELECT * FROM notifikasi WHERE user_id = $user_id ORDER BY dibaca ASC, id DESC");

$judul_halaman = "Notifikasi";
$menu_aktif    = "notifikasi";
require 'templates/header.php';

// Tandai semua notifikasi sebagai sudah dibaca (dilakukan SETELAH header
// agar jumlah notif di sidebar tetap tampil saat halaman ini dibuka)
mysqli_query($conn, "UPDATE notifikasi SET dibaca = 1 WHERE user_id = $user_id");
?>

<h1 class="page-title">Notifikasi</h1>

<div class="card-box">
  <?php if (mysqli_num_rows($data) === 0): ?>
    <p style="color:#9aa3b2;margin:0">Belum ada notifikasi.</p>
  <?php else: ?>
    <?php while ($n = mysqli_fetch_assoc($data)): ?>
      <div style="padding:12px 4px; border-bottom:1px solid #eef1f6;
                  <?= $n['dibaca'] == 0 ? 'background:#f4f8ff' : '' ?>">
        <div style="font-size:14.5px"><?= bersihkan($n['pesan']) ?></div>
        <div class="helper" style="margin-top:3px">
          <?= tanggal_indonesia($n['tanggal']) ?>
          <?= $n['dibaca'] == 0 ? ' • <b style="color:#1f4e9c">Baru</b>' : '' ?>
        </div>
      </div>
    <?php endwhile; ?>
  <?php endif; ?>
</div>

<?php require 'templates/footer.php'; ?>
