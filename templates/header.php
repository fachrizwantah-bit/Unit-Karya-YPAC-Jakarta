<?php
/**
 * TEMPLATE BAGIAN ATAS (header + navbar + sidebar)
 * Cara pakai di setiap halaman:
 *   $judul_halaman = "Dashboard";
 *   $menu_aktif    = "dashboard";
 *   require 'templates/header.php';
 */
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }

$role  = $_SESSION['role'];
$nama  = $_SESSION['nama'];
$jml_notif = jumlah_notif_belum_dibaca($conn, $_SESSION['user_id']);
$aktif = isset($menu_aktif) ? $menu_aktif : '';

// Fungsi kecil untuk menandai menu yang sedang aktif
if (!function_exists('kelas_aktif')) {
    function kelas_aktif($nama, $aktif) { return $nama === $aktif ? 'active' : ''; }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= isset($judul_halaman) ? $judul_halaman : 'Pengadaan Barang' ?> - YPAC Jakarta</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

  <!-- NAVBAR ATAS -->
  <div class="topbar">
    <div class="brand">Sistem Pengadaan Barang — Unit Karya YPAC Jakarta</div>
    <div class="user-area">
      <span><?= bersihkan($nama) ?> (<?= ucfirst($role) ?>)</span>
      <a href="logout.php" title="Keluar">⏻ Logout</a>
    </div>
  </div>

  <div class="layout">

    <!-- SIDEBAR MENU -->
    <div class="sidebar">
      <div class="menu-title">Menu</div>
      <a href="dashboard.php" class="<?= kelas_aktif('dashboard', $aktif) ?>">Dashboard</a>

      <?php if ($role === 'staff'): ?>
        <a href="ajukan.php"  class="<?= kelas_aktif('ajukan', $aktif) ?>">Ajukan Barang</a>
        <a href="riwayat.php" class="<?= kelas_aktif('riwayat', $aktif) ?>">Riwayat Pengajuan</a>
      <?php endif; ?>

      <?php if ($role === 'approver' || $role === 'admin'): ?>
        <a href="kelola_pengajuan.php" class="<?= kelas_aktif('kelola', $aktif) ?>">Kelola Pengajuan</a>
      <?php endif; ?>

      <?php if ($role === 'admin'): ?>
        <a href="data_barang.php"  class="<?= kelas_aktif('barang', $aktif) ?>">Kelola Data Barang</a>
        <a href="kelola_user.php"  class="<?= kelas_aktif('user', $aktif) ?>">Kelola Pengguna</a>
        <a href="laporan.php"      class="<?= kelas_aktif('laporan', $aktif) ?>">Laporan Pengadaan</a>
      <?php endif; ?>

      <a href="notifikasi.php" class="<?= kelas_aktif('notifikasi', $aktif) ?>">
        Notifikasi
        <?php if ($jml_notif > 0): ?><span class="notif-dot"><?= $jml_notif ?></span><?php endif; ?>
      </a>
    </div>

    <!-- AREA KONTEN (ditutup di footer.php) -->
    <div class="content">
