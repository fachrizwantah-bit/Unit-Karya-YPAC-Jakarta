<?php
/**
 * HALAMAN DETAIL PENGAJUAN
 * Menampilkan rincian satu pengajuan: data pengaju, daftar barang,
 * dan riwayat persetujuan (approval workflow).
 * Bisa diakses semua role (staff hanya boleh melihat miliknya).
 */
require_once 'config/fungsi.php';
wajib_login();

$id = (int)($_GET['id'] ?? 0);

// Ambil data pengajuan + nama pengaju
$q = mysqli_query($conn,
    "SELECT pb.*, u.nama, u.email FROM pengajuan_barang pb
     JOIN users u ON u.id = pb.user_id WHERE pb.id = $id");
$p = mysqli_fetch_assoc($q);

if (!$p) {
    $judul_halaman = "Detail"; $menu_aktif=""; require 'templates/header.php';
    echo "<div class='alert alert-danger'>Data pengajuan tidak ditemukan.</div>";
    require 'templates/footer.php'; exit;
}

// Staff hanya boleh melihat pengajuan miliknya sendiri
if ($_SESSION['role'] === 'staff' && $p['user_id'] != $_SESSION['user_id']) {
    $judul_halaman = "Detail"; $menu_aktif=""; require 'templates/header.php';
    echo "<div class='alert alert-danger'>Anda tidak berhak melihat pengajuan ini.</div>";
    require 'templates/footer.php'; exit;
}

// Ambil detail barang
$barang = mysqli_query($conn, "SELECT * FROM detail_barang WHERE pengajuan_id = $id");

// Ambil riwayat approval
$riwayat = mysqli_query($conn,
    "SELECT aw.*, u.nama AS nama_approver FROM approval_workflow aw
     JOIN users u ON u.id = aw.approver_id
     WHERE aw.pengajuan_id = $id ORDER BY aw.id ASC");

$judul_halaman = "Detail Pengajuan";
$menu_aktif    = "";
require 'templates/header.php';
?>

<h1 class="page-title">Detail Pengajuan</h1>

<div class="card-box">
  <div class="flex-between">
    <h3 style="margin:0"><?= nomor_pengajuan($p['id'], $p['tanggal_pengajuan']) ?></h3>
    <div><?= badge_status($p['status']) ?></div>
  </div>
  <div class="detail-list" style="margin-top:14px">
    <div><b>Nama Pengaju</b>: <?= bersihkan($p['nama']) ?></div>
    <div><b>Email</b>: <?= bersihkan($p['email']) ?></div>
    <div><b>Tanggal</b>: <?= tanggal_indonesia($p['tanggal_pengajuan']) ?></div>
    <div><b>Keterangan</b>: <?= bersihkan($p['keterangan']) ?: '-' ?></div>
  </div>
</div>

<div class="card-box">
  <h3 style="margin-top:0">Daftar Barang yang Diajukan</h3>
  <table class="tabel">
    <tr><th>No</th><th>Nama Barang</th><th>Jumlah</th><th>Satuan</th><th>Keterangan</th></tr>
    <?php $no=1; while ($b = mysqli_fetch_assoc($barang)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= bersihkan($b['nama_barang']) ?></td>
        <td><?= $b['jumlah'] ?></td>
        <td><?= bersihkan($b['satuan']) ?></td>
        <td><?= bersihkan($b['keterangan']) ?: '-' ?></td>
      </tr>
    <?php endwhile; ?>
  </table>
</div>

<div class="card-box">
  <h3 style="margin-top:0">Riwayat Persetujuan (Approval Workflow)</h3>
  <?php if (mysqli_num_rows($riwayat) === 0): ?>
    <p style="color:#9aa3b2">Belum ada keputusan. Pengajuan masih menunggu persetujuan.</p>
  <?php else: ?>
    <table class="tabel">
      <tr><th>Approver</th><th>Keputusan</th><th>Catatan</th><th>Tanggal</th></tr>
      <?php while ($r = mysqli_fetch_assoc($riwayat)): ?>
        <tr>
          <td><?= bersihkan($r['nama_approver']) ?></td>
          <td><?= badge_status($r['status']) ?></td>
          <td><?= bersihkan($r['catatan']) ?: '-' ?></td>
          <td><?= tanggal_indonesia($r['tanggal_aksi']) ?></td>
        </tr>
      <?php endwhile; ?>
    </table>
  <?php endif; ?>
</div>

<a href="javascript:history.back()" class="btn btn-gray">&larr; Kembali</a>

<?php require 'templates/footer.php'; ?>
