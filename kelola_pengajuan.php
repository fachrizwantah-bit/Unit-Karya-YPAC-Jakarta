<?php
/**
 * HALAMAN KELOLA PENGAJUAN (Approver & Admin)
 * Menampilkan daftar pengajuan masuk. Approver/Admin dapat
 * menyetujui atau menolak disertai catatan. Keputusan dicatat
 * di tabel approval_workflow dan notifikasi dikirim ke staff.
 */
require_once 'config/fungsi.php';
wajib_role(['approver', 'admin']);

$pesan = "";

// ----- PROSES SETUJU / TOLAK -----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pengajuan_id = (int)($_POST['pengajuan_id'] ?? 0);
    $aksi         = $_POST['aksi'] ?? '';                 // 'disetujui' atau 'ditolak'
    $catatan      = trim($_POST['catatan'] ?? '');

    if ($pengajuan_id > 0 && in_array($aksi, ['disetujui', 'ditolak'])) {
        // 1) Update status pengajuan
        $stmt = mysqli_prepare($conn, "UPDATE pengajuan_barang SET status = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "si", $aksi, $pengajuan_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // 2) Catat ke approval_workflow
        $stmt = mysqli_prepare($conn,
            "INSERT INTO approval_workflow (pengajuan_id, approver_id, status, catatan)
             VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "iiss", $pengajuan_id, $_SESSION['user_id'], $aksi, $catatan);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // 3) Kirim notifikasi ke staff pembuat pengajuan
        $q = mysqli_query($conn, "SELECT user_id, tanggal_pengajuan FROM pengajuan_barang WHERE id = $pengajuan_id");
        $pb = mysqli_fetch_assoc($q);
        $no = nomor_pengajuan($pengajuan_id, $pb['tanggal_pengajuan']);
        buat_notifikasi($conn, $pb['user_id'],
            "Pengajuan $no telah " . strtoupper($aksi) . " oleh " . $_SESSION['nama'] .
            ($catatan ? ". Catatan: $catatan" : "."));

        $pesan = "Pengajuan $no berhasil ditandai sebagai " . strtoupper($aksi) . ".";
    }
}

// ----- FILTER STATUS -----
$filter = $_GET['status'] ?? 'semua';
$kondisi = "";
if (in_array($filter, ['menunggu','disetujui','ditolak'])) {
    $kondisi = "WHERE pb.status = '$filter'";
}

$sql = "SELECT pb.*, u.nama,
        (SELECT COUNT(*) FROM detail_barang d WHERE d.pengajuan_id = pb.id) AS jml_item
        FROM pengajuan_barang pb
        JOIN users u ON u.id = pb.user_id
        $kondisi
        ORDER BY (pb.status='menunggu') DESC, pb.id DESC";
$data = mysqli_query($conn, $sql);

$judul_halaman = "Kelola Pengajuan";
$menu_aktif    = "kelola";
require 'templates/header.php';
?>

<h1 class="page-title">Daftar Pengajuan Masuk</h1>

<?php if ($pesan): ?>
  <div class="alert alert-success"><?= bersihkan($pesan) ?></div>
<?php endif; ?>

<div class="card-box">
  <div class="flex-between" style="margin-bottom:14px">
    <h3 style="margin:0">Pengajuan</h3>
    <div>
      <a class="btn btn-sm <?= $filter==='semua'?'btn-primary':'btn-gray' ?>" href="?status=semua">Semua</a>
      <a class="btn btn-sm <?= $filter==='menunggu'?'btn-primary':'btn-gray' ?>" href="?status=menunggu">Menunggu</a>
      <a class="btn btn-sm <?= $filter==='disetujui'?'btn-primary':'btn-gray' ?>" href="?status=disetujui">Disetujui</a>
      <a class="btn btn-sm <?= $filter==='ditolak'?'btn-primary':'btn-gray' ?>" href="?status=ditolak">Ditolak</a>
    </div>
  </div>

  <table class="tabel">
    <tr>
      <th>No. Pengajuan</th>
      <th>Pengaju</th>
      <th>Tanggal</th>
      <th>Item</th>
      <th>Status</th>
      <th style="width:240px">Aksi</th>
    </tr>
    <?php if (mysqli_num_rows($data) === 0): ?>
      <tr><td colspan="6" style="text-align:center;color:#9aa3b2">Tidak ada pengajuan.</td></tr>
    <?php else: ?>
      <?php while ($p = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= nomor_pengajuan($p['id'], $p['tanggal_pengajuan']) ?></td>
          <td><?= bersihkan($p['nama']) ?></td>
          <td><?= tanggal_indonesia($p['tanggal_pengajuan']) ?></td>
          <td><?= $p['jml_item'] ?> item</td>
          <td><?= badge_status($p['status']) ?></td>
          <td>
            <a class="btn btn-gray btn-sm" href="detail_pengajuan.php?id=<?= $p['id'] ?>">Detail</a>
            <?php if ($p['status'] === 'menunggu'): ?>
              <button class="btn btn-success btn-sm"
                      onclick="bukaForm(<?= $p['id'] ?>,'disetujui')">Setujui</button>
              <button class="btn btn-danger btn-sm"
                      onclick="bukaForm(<?= $p['id'] ?>,'ditolak')">Tolak</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php endif; ?>
  </table>
</div>

<!-- Form tersembunyi untuk konfirmasi setuju/tolak -->
<div id="kotakForm" class="card-box" style="display:none; border:2px solid #1f4e9c">
  <h3 style="margin-top:0" id="judulForm">Keputusan Pengajuan</h3>
  <form method="POST" action="kelola_pengajuan.php">
    <input type="hidden" name="pengajuan_id" id="inp_id">
    <input type="hidden" name="aksi" id="inp_aksi">
    <label class="form-label">Catatan / Alasan (opsional untuk setuju, wajib untuk tolak)</label>
    <textarea class="form-input" name="catatan" id="inp_catatan" placeholder="Tuliskan catatan..."></textarea>
    <div style="margin-top:14px">
      <button type="submit" class="btn btn-primary" id="btnSubmit">Simpan Keputusan</button>
      <button type="button" class="btn btn-gray" onclick="tutupForm()">Batal</button>
    </div>
  </form>
</div>

<script>
function bukaForm(id, aksi) {
  document.getElementById('inp_id').value = id;
  document.getElementById('inp_aksi').value = aksi;
  document.getElementById('judulForm').innerText =
      (aksi === 'disetujui' ? 'Menyetujui' : 'Menolak') + ' Pengajuan';
  document.getElementById('kotakForm').style.display = 'block';
  document.getElementById('kotakForm').scrollIntoView({behavior:'smooth'});
}
function tutupForm() {
  document.getElementById('kotakForm').style.display = 'none';
  document.getElementById('inp_catatan').value = '';
}
// Jika menolak, catatan wajib diisi
document.querySelector('#kotakForm form').addEventListener('submit', function(e){
  if (document.getElementById('inp_aksi').value === 'ditolak'
      && document.getElementById('inp_catatan').value.trim() === '') {
    e.preventDefault();
    alert('Mohon isi alasan penolakan.');
  }
});
</script>

<?php require 'templates/footer.php'; ?>
