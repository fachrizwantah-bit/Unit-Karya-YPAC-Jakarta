<?php
/**
 * DASHBOARD
 * Menampilkan ringkasan data sesuai role pengguna yang login.
 */
require_once 'config/fungsi.php';
wajib_login();

$role    = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// Mengambil statistik jumlah pengajuan berdasarkan status.
// Staff hanya melihat miliknya, approver/admin melihat semua.
if ($role === 'staff') {
    $where = "WHERE user_id = " . (int)$user_id;
} else {
    $where = "";
}

$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where"))['j'];
$menunggu  = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where " . ($where ? "AND" : "WHERE") . " status='menunggu'"))['j'];
$disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where " . ($where ? "AND" : "WHERE") . " status='disetujui'"))['j'];
$ditolak   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where " . ($where ? "AND" : "WHERE") . " status='ditolak'"))['j'];

$judul_halaman = "Dashboard";
$menu_aktif    = "dashboard";
require 'templates/header.php';
?>

<h1 class="page-title">Dashboard</h1>

<div class="card-box">
  Selamat datang, <b><?= bersihkan($_SESSION['nama']) ?></b>.
  Anda login sebagai <b><?= ucfirst($role) ?></b>.
  <?php if ($role === 'staff'): ?>
    Silakan ajukan kebutuhan barang melalui menu <b>Ajukan Barang</b>.
  <?php elseif ($role === 'approver'): ?>
    Silakan tinjau pengajuan yang masuk melalui menu <b>Kelola Pengajuan</b>.
  <?php else: ?>
    Anda memiliki akses penuh untuk mengelola barang, pengguna, dan laporan.
  <?php endif; ?>
</div>

<div class="stat-row">
  <div class="stat-card">
    <div class="label">Total Pengajuan</div>
    <div class="value"><?= $total ?></div>
  </div>
  <div class="stat-card amber">
    <div class="label">Menunggu</div>
    <div class="value"><?= $menunggu ?></div>
  </div>
  <div class="stat-card green">
    <div class="label">Disetujui</div>
    <div class="value"><?= $disetujui ?></div>
  </div>
  <div class="stat-card red">
    <div class="label">Ditolak</div>
    <div class="value"><?= $ditolak ?></div>
  </div>
</div>

<?php
// Tabel pengajuan terbaru
if ($role === 'staff') {
    $sql = "SELECT pb.*, u.nama FROM pengajuan_barang pb
            JOIN users u ON u.id = pb.user_id
            WHERE pb.user_id = " . (int)$user_id . "
            ORDER BY pb.id DESC LIMIT 5";
} else {
    $sql = "SELECT pb.*, u.nama FROM pengajuan_barang pb
            JOIN users u ON u.id = pb.user_id
            ORDER BY pb.id DESC LIMIT 5";
}
$pengajuan = mysqli_query($conn, $sql);
?>

<div class="card-box">
  <h3 style="margin-top:0">Pengajuan Terbaru</h3>
  <table class="tabel">
    <tr>
      <th>No. Pengajuan</th>
      <th>Pengaju</th>
      <th>Tanggal</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
    <?php if (mysqli_num_rows($pengajuan) === 0): ?>
      <tr><td colspan="5" style="text-align:center;color:#9aa3b2">Belum ada data pengajuan.</td></tr>
    <?php else: ?>
      <?php while ($p = mysqli_fetch_assoc($pengajuan)): ?>
        <tr>
          <td><?= nomor_pengajuan($p['id'], $p['tanggal_pengajuan']) ?></td>
          <td><?= bersihkan($p['nama']) ?></td>
          <td><?= tanggal_indonesia($p['tanggal_pengajuan']) ?></td>
          <td><?= badge_status($p['status']) ?></td>
          <td><a class="btn btn-gray btn-sm" href="detail_pengajuan.php?id=<?= $p['id'] ?>">Lihat</a></td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </table>
</div>

<?php require 'templates/footer.php'; ?>
