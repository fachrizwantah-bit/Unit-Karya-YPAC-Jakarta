<?php

require_once 'config/fungsi.php';
wajib_role(['staff']);

$user_id = $_SESSION['user_id'];
$stmt = mysqli_prepare($conn,
    "SELECT pb.*, (SELECT COUNT(*) FROM detail_barang d WHERE d.pengajuan_id = pb.id) AS jml_item
     FROM pengajuan_barang pb WHERE pb.user_id = ? ORDER BY pb.id DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);

$judul_halaman = "Riwayat Pengajuan";
$menu_aktif    = "riwayat";
require 'templates/header.php';
?>

<h1 class="page-title">Riwayat Pengajuan</h1>

<div class="card-box">
  <table class="tabel">
    <tr>
      <th>No. Pengajuan</th>
      <th>Tanggal</th>
      <th>Keterangan</th>
      <th>Jumlah Item</th>
      <th>Status</th>
      <th>Aksi</th>
    </tr>
    <?php if (mysqli_num_rows($data) === 0): ?>
      <tr><td colspan="6" style="text-align:center;color:#9aa3b2">Belum ada pengajuan. Silakan buat di menu Ajukan Barang.</td></tr>
    <?php else: ?>
      <?php while ($p = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= nomor_pengajuan($p['id'], $p['tanggal_pengajuan']) ?></td>
          <td><?= tanggal_indonesia($p['tanggal_pengajuan']) ?></td>
          <td><?= bersihkan($p['keterangan']) ?: '-' ?></td>
          <td><?= $p['jml_item'] ?> item</td>
          <td><?= badge_status($p['status']) ?></td>
          <td><a class="btn btn-gray btn-sm" href="detail_pengajuan.php?id=<?= $p['id'] ?>">Lihat Detail</a></td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </table>
</div>

<?php require 'templates/footer.php'; ?>
