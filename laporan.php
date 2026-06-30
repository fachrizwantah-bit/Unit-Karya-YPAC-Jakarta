<?php
/**
 * HALAMAN LAPORAN PENGADAAN (khusus Admin)
 * Menampilkan rekap pengadaan berdasarkan rentang tanggal.
 * Tombol "Ekspor PDF" memakai fitur cetak browser (Print -> Save as PDF).
 */
require_once 'config/fungsi.php';
wajib_role(['admin']);

// Rentang tanggal default: awal bulan ini s.d. hari ini
$dari   = $_GET['dari']   ?? date('Y-m-01');
$sampai = $_GET['sampai'] ?? date('Y-m-d');

// Hitung ringkasan dalam rentang tanggal
$where = "WHERE tanggal_pengajuan BETWEEN '$dari' AND '$sampai'";
$total     = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where"))['j'];
$disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where AND status='disetujui'"))['j'];
$ditolak   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS j FROM pengajuan_barang $where AND status='ditolak'"))['j'];

$persen_setuju = $total > 0 ? round($disetujui / $total * 100) : 0;
$persen_tolak  = $total > 0 ? round($ditolak / $total * 100) : 0;

// Daftar pengajuan dalam rentang
$sql = "SELECT pb.*, u.nama,
        (SELECT COUNT(*) FROM detail_barang d WHERE d.pengajuan_id = pb.id) AS jml_item
        FROM pengajuan_barang pb JOIN users u ON u.id = pb.user_id
        $where ORDER BY pb.tanggal_pengajuan DESC";
$data = mysqli_query($conn, $sql);

$judul_halaman = "Laporan Pengadaan";
$menu_aktif    = "laporan";
require 'templates/header.php';
?>

<style>
  /* Saat dicetak, sembunyikan menu & navbar agar laporan rapi */
  @media print {
    .topbar, .sidebar, .no-print { display: none !important; }
    .content { padding: 0 !important; }
    .card-box { border: none !important; box-shadow: none !important; }
  }
</style>

<h1 class="page-title">Laporan Pengadaan Barang</h1>

<!-- Filter periode -->
<div class="card-box no-print">
  <form method="GET" action="laporan.php" class="flex-between">
    <div style="flex:1">
      <label class="form-label">Dari Tanggal</label>
      <input class="form-input" type="date" name="dari" value="<?= bersihkan($dari) ?>">
    </div>
    <div style="flex:1">
      <label class="form-label">Sampai Tanggal</label>
      <input class="form-input" type="date" name="sampai" value="<?= bersihkan($sampai) ?>">
    </div>
    <div style="align-self:flex-end">
      <button type="submit" class="btn btn-primary">Tampilkan</button>
      <button type="button" class="btn btn-success" onclick="window.print()">Ekspor PDF</button>
    </div>
  </form>
</div>

<!-- Ringkasan -->
<div class="stat-row">
  <div class="stat-card">
    <div class="label">Total Pengajuan</div>
    <div class="value"><?= $total ?></div>
    <div class="helper"><?= tanggal_indonesia($dari) ?> &ndash; <?= tanggal_indonesia($sampai) ?></div>
  </div>
  <div class="stat-card green">
    <div class="label">Disetujui</div>
    <div class="value"><?= $disetujui ?></div>
    <div class="helper"><?= $persen_setuju ?>% dari total</div>
  </div>
  <div class="stat-card red">
    <div class="label">Ditolak</div>
    <div class="value"><?= $ditolak ?></div>
    <div class="helper"><?= $persen_tolak ?>% dari total</div>
  </div>
</div>

<!-- Tabel rincian -->
<div class="card-box">
  <table class="tabel">
    <tr>
      <th>No. Pengajuan</th>
      <th>Nama Pengaju</th>
      <th>Tanggal</th>
      <th>Item</th>
      <th>Status</th>
    </tr>
    <?php if (mysqli_num_rows($data) === 0): ?>
      <tr><td colspan="5" style="text-align:center;color:#9aa3b2">Tidak ada data pada periode ini.</td></tr>
    <?php else: ?>
      <?php while ($p = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= nomor_pengajuan($p['id'], $p['tanggal_pengajuan']) ?></td>
          <td><?= bersihkan($p['nama']) ?></td>
          <td><?= tanggal_indonesia($p['tanggal_pengajuan']) ?></td>
          <td><?= $p['jml_item'] ?> item</td>
          <td><?= badge_status($p['status']) ?></td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </table>
</div>

<?php require 'templates/footer.php'; ?>
