<?php
/**
 * HALAMAN KELOLA DATA BARANG (khusus Admin)
 * Admin dapat menambah dan menghapus master barang.
 */
require_once 'config/fungsi.php';
wajib_role(['admin']);

$pesan = "";

// ----- TAMBAH BARANG -----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'tambah') {
    $nama   = trim($_POST['nama_barang'] ?? '');
    $satuan = trim($_POST['satuan'] ?? '');
    $ket    = trim($_POST['keterangan'] ?? '');
    if ($nama !== '' && $satuan !== '') {
        $stmt = mysqli_prepare($conn,
            "INSERT INTO barang (nama_barang, satuan, keterangan) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sss", $nama, $satuan, $ket);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $pesan = "Barang '$nama' berhasil ditambahkan.";
    } else {
        $pesan = "Nama barang dan satuan wajib diisi.";
    }
}

// ----- HAPUS BARANG -----
if (isset($_GET['hapus'])) {
    $hid = (int)$_GET['hapus'];
    mysqli_query($conn, "DELETE FROM barang WHERE id = $hid");
    $pesan = "Barang berhasil dihapus.";
}

$data = mysqli_query($conn, "SELECT * FROM barang ORDER BY id DESC");

$judul_halaman = "Kelola Data Barang";
$menu_aktif    = "barang";
require 'templates/header.php';
?>

<h1 class="page-title">Kelola Data Barang</h1>

<?php if ($pesan): ?>
  <div class="alert alert-info"><?= bersihkan($pesan) ?></div>
<?php endif; ?>

<div class="card-box">
  <h3 style="margin-top:0">Tambah Barang Baru</h3>
  <form method="POST" action="data_barang.php">
    <input type="hidden" name="aksi" value="tambah">
    <div class="flex-between" style="gap:18px">
      <div style="flex:2">
        <label class="form-label">Nama Barang</label>
        <input class="form-input" type="text" name="nama_barang" placeholder="cth: Kain Katun Putih" required>
      </div>
      <div style="flex:1">
        <label class="form-label">Satuan</label>
        <input class="form-input" type="text" name="satuan" placeholder="cth: Meter" required>
      </div>
      <div style="flex:2">
        <label class="form-label">Keterangan</label>
        <input class="form-input" type="text" name="keterangan" placeholder="(opsional)">
      </div>
    </div>
    <div style="margin-top:16px">
      <button type="submit" class="btn btn-primary">+ Tambah Barang</button>
    </div>
  </form>
</div>

<div class="card-box">
  <h3 style="margin-top:0">Daftar Barang</h3>
  <table class="tabel">
    <tr><th>No</th><th>Nama Barang</th><th>Satuan</th><th>Keterangan</th><th style="width:90px">Aksi</th></tr>
    <?php if (mysqli_num_rows($data) === 0): ?>
      <tr><td colspan="5" style="text-align:center;color:#9aa3b2">Belum ada data barang.</td></tr>
    <?php else: $no=1; while ($b = mysqli_fetch_assoc($data)): ?>
      <tr>
        <td><?= $no++ ?></td>
        <td><?= bersihkan($b['nama_barang']) ?></td>
        <td><?= bersihkan($b['satuan']) ?></td>
        <td><?= bersihkan($b['keterangan']) ?: '-' ?></td>
        <td>
          <a class="btn btn-danger btn-sm" href="data_barang.php?hapus=<?= $b['id'] ?>"
             onclick="return confirm('Yakin ingin menghapus barang ini?')">Hapus</a>
        </td>
      </tr>
    <?php endwhile; endif; ?>
  </table>
</div>

<?php require 'templates/footer.php'; ?>
