<?php
/**
 * ============================================================
 *  FILE KONEKSI KE DATABASE
 * ============================================================
 *  File ini menghubungkan aplikasi dengan database MySQL.
 *  Jika suatu saat Anda memakai username/password MySQL yang
 *  berbeda, cukup ubah baris di bawah ini saja.
 *
 *  Pengaturan default XAMPP (tidak perlu diubah):
 *    - host      : localhost
 *    - username  : root
 *    - password  : (kosong)
 *    - database  : pengadaan_barang
 * ============================================================
 */

$db_host = "localhost";          // alamat server database
$db_user = "root";               // username MySQL (default XAMPP = root)
$db_pass = "";                   // password MySQL (default XAMPP = kosong)
$db_name = "pengadaan_barang";   // nama database yang sudah diimport

// Membuat koneksi
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name);

// Jika koneksi gagal, hentikan program dan tampilkan pesan
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error()
        . "<br>Pastikan XAMPP (Apache & MySQL) sudah berjalan dan database sudah diimport.");
}

// Mengatur agar karakter Indonesia tampil dengan benar
mysqli_set_charset($conn, "utf8mb4");
