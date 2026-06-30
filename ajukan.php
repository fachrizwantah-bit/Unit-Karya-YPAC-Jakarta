<?php
/**
 * HALAMAN AJUKAN BARANG (khusus Staff)
 * Staff mengisi formulir pengajuan beserta daftar barang.
 * Data disimpan ke tabel pengajuan_barang & detail_barang,
 * lalu notifikasi dikirim ke semua approver.
 */
require_once 'config/fungsi.php';
wajib_role(['staff']);

$pesan = "";
$tipe_pesan = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal    = $_POST['tanggal_pengajuan'] ?? date('Y-m-d');
    $keterangan = trim($_POST['keterangan'] ?? '');

    // Ambil array daftar barang dari form
    $nama_barang = $_POST['nama_barang'] ?? [];
    $jumlah      = $_POST['jumlah'] ?? [];
    $satuan      = $_POST['satuan'] ?? [];

    // Validasi sederhana: minimal ada 1 barang yang terisi
    $ada_barang = false;
    foreach ($nama_barang as $nb) { if (trim($nb) !== '') { $ada_barang = true; break; } }

    if (!$ada_barang) {
        $pesan = "Minimal harus ada 1 barang yang diisi.";
        $tipe_pesan = "danger";
    } else {
        // 1) Simpan header pengajuan
        $stmt = mysqli_prepare($conn,
            "INSERT INTO pengajuan_barang (user_id, tanggal_pengajuan, keterangan, status)
             VALUES (?, ?, ?, 'menunggu')");
        mysqli_stmt_bind_param($stmt, "iss", $_SESSION['user_id'], $tanggal, $keterangan);
        mysqli_stmt_execute($stmt);
        $pengajuan_id = mysqli_insert_id($conn);
        mysqli_stmt_close($stmt);

        // 2) Simpan setiap baris barang
        $stmt = mysqli_prepare($conn,
            "INSERT INTO detail_barang (pengajuan_id, nama_barang, jumlah, satuan)
             VALUES (?, ?, ?, ?)");
        for ($i = 0; $i < count($nama_barang); $i++) {
            $nb = trim($nama_barang[$i]);
            if ($nb === '') continue;                  // lewati baris kosong
            $jml = (int)($jumlah[$i] ?? 1);
            $sat = trim($satuan[$i] ?? '');
            mysqli_stmt_bind_param($stmt, "isis", $pengajuan_id, $nb, $jml, $sat);
            mysqli_stmt_execute($stmt);
        }
        mysqli_stmt_close($stmt);

        // 3) Kirim notifikasi ke SEMUA approver
        $no = nomor_pengajuan($pengajuan_id, $tanggal);
        $approvers = mysqli_query($conn, "SELECT id FROM users WHERE role='approver'");
        while ($a = mysqli_fetch_assoc($approvers)) {
            buat_notifikasi($conn, $a['id'],
                "Pengajuan baru $no dari " . $_SESSION['nama'] . " menunggu persetujuan.");
        }

        $pesan = "Pengajuan $no berhasil dikirim dan notifikasi telah dikirim ke Approver.";
        $tipe_pesan = "success";
    }
}

// Ambil master barang untuk bantuan pengisian (datalist)
$master = mysqli_query($conn, "SELECT nama_barang, satuan FROM barang ORDER BY nama_barang");

$judul_halaman = "Ajukan Barang";
$menu_aktif    = "ajukan";
require 'templates/header.php';
?>

<h1 class="page-title">Form Pengajuan Barang</h1>

<?php if ($pesan): ?>
  <div class="alert alert-<?= $tipe_pesan ?>"><?= bersihkan($pesan) ?></div>
<?php endif; ?>

<form method="POST" action="ajukan.php">
  <div class="card-box">
    <div class="flex-between" style="gap:24px">
      <div style="flex:1">
        <label class="form-label">Tanggal Pengajuan</label>
        <input class="form-input" type="date" name="tanggal_pengajuan" value="<?= date('Y-m-d') ?>" required>
      </div>
      <div style="flex:1">
        <label class="form-label">Nama Pengaju</label>
        <input class="form-input" type="text" value="<?= bersihkan($_SESSION['nama']) ?>" disabled>
      </div>
    </div>

    <label class="form-label">Keterangan Kebutuhan</label>
    <textarea class="form-input" name="keterangan" placeholder="Tuliskan alasan/keterangan kebutuhan..."></textarea>
  </div>

  <div class="card-box">
    <div class="flex-between">
      <h3 style="margin:0">Daftar Barang</h3>
      <button type="button" class="btn btn-gray btn-sm" onclick="tambahBaris()">+ Tambah Barang</button>
    </div>

    <table class="tabel" id="tabelBarang" style="margin-top:14px">
      <tr>
        <th style="width:50%">Nama Barang</th>
        <th>Jumlah</th>
        <th>Satuan</th>
        <th style="width:60px">Aksi</th>
      </tr>
      <tr>
        <td><input class="form-input" type="text" name="nama_barang[]" list="daftarBarang" placeholder="Nama barang"></td>
        <td><input class="form-input" type="number" name="jumlah[]" min="1" value="1"></td>
        <td><input class="form-input" type="text" name="satuan[]" placeholder="cth: Buah"></td>
        <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">×</button></td>
      </tr>
    </table>

    <!-- Bantuan autocomplete dari master barang -->
    <datalist id="daftarBarang">
      <?php while ($m = mysqli_fetch_assoc($master)): ?>
        <option value="<?= bersihkan($m['nama_barang']) ?>"></option>
      <?php endwhile; ?>
    </datalist>

    <div style="margin-top:18px; text-align:right">
      <a href="dashboard.php" class="btn btn-gray">Batal</a>
      <button type="submit" class="btn btn-primary">Kirim Pengajuan</button>
    </div>
  </div>
</form>

<script>
// Menambah baris barang baru
function tambahBaris() {
  const tabel = document.getElementById('tabelBarang');
  const baris = tabel.insertRow(-1);
  baris.innerHTML = `
    <td><input class="form-input" type="text" name="nama_barang[]" list="daftarBarang" placeholder="Nama barang"></td>
    <td><input class="form-input" type="number" name="jumlah[]" min="1" value="1"></td>
    <td><input class="form-input" type="text" name="satuan[]" placeholder="cth: Buah"></td>
    <td><button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">×</button></td>`;
}
// Menghapus baris (minimal sisakan 1 baris)
function hapusBaris(tombol) {
  const tabel = document.getElementById('tabelBarang');
  if (tabel.rows.length > 2) {
    tombol.closest('tr').remove();
  } else {
    alert('Minimal harus ada 1 baris barang.');
  }
}
</script>

<?php require 'templates/footer.php'; ?>
