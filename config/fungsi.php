<?php
/**
 * ============================================================
 *  KUMPULAN FUNGSI BANTU (HELPER)
 * ============================================================
 *  Berisi fungsi-fungsi yang dipakai berulang di banyak halaman,
 *  seperti cek login, cek hak akses, membuat notifikasi, dll.
 * ============================================================
 */

// Memulai session (untuk menyimpan data login). Wajib paling atas.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Memuat koneksi database
require_once __DIR__ . '/koneksi.php';

/**
 * Memastikan pengguna sudah login.
 * Jika belum, akan dialihkan ke halaman login.
 */
function wajib_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Memastikan pengguna memiliki role tertentu.
 * Contoh pemakaian: wajib_role(['admin']);
 */
function wajib_role($role_diizinkan = [])
{
    wajib_login();
    if (!in_array($_SESSION['role'], $role_diizinkan)) {
        echo "<h3 style='font-family:sans-serif;text-align:center;margin-top:50px'>
                Akses ditolak. Halaman ini tidak tersedia untuk akun Anda.
              </h3>";
        exit;
    }
}

/**
 * Membuat notifikasi baru untuk seorang pengguna.
 */
function buat_notifikasi($conn, $user_id, $pesan)
{
    $stmt = mysqli_prepare($conn, "INSERT INTO notifikasi (user_id, pesan) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "is", $user_id, $pesan);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

/**
 * Menghitung jumlah notifikasi yang BELUM dibaca milik pengguna.
 */
function jumlah_notif_belum_dibaca($conn, $user_id)
{
    $stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM notifikasi WHERE user_id = ? AND dibaca = 0");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    mysqli_stmt_close($stmt);
    return (int) $row['total'];
}

/**
 * Mengubah tanggal database menjadi format Indonesia (cth: 06 Mei 2026).
 */
function tanggal_indonesia($tanggal)
{
    if (empty($tanggal) || $tanggal === '0000-00-00') return '-';
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($tanggal);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Membuat nomor pengajuan yang rapi, cth: PB-2026-001
 */
function nomor_pengajuan($id, $tanggal)
{
    $tahun = date('Y', strtotime($tanggal));
    return 'PB-' . $tahun . '-' . str_pad($id, 3, '0', STR_PAD_LEFT);
}

/**
 * Menghasilkan badge (label warna) sesuai status pengajuan.
 */
function badge_status($status)
{
    switch ($status) {
        case 'disetujui':
            return '<span class="badge bg-success">Disetujui</span>';
        case 'ditolak':
            return '<span class="badge bg-danger">Ditolak</span>';
        default:
            return '<span class="badge bg-warning text-dark">Menunggu</span>';
    }
}

/**
 * Membersihkan input dari user agar aman ditampilkan di halaman.
 */
function bersihkan($teks)
{
    return htmlspecialchars(trim($teks), ENT_QUOTES, 'UTF-8');
}
