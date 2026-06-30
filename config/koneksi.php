<?php
/**
 * ============================================================
 *  FILE KONEKSI KE DATABASE
 * ============================================================
 *  Mendukung environment variable untuk deployment (Vercel/dll)
 *  maupun koneksi lokal XAMPP.
 *
 *  Di Vercel, setel Environment Variables berikut:
 *    DB_HOST, DB_USER, DB_PASS, DB_NAME
 *
 *  Default (kosong) = memakai kredensial XAMPP lokal.
 * ============================================================
 */

$db_host = getenv('DB_HOST') ?: "localhost";
$db_user = getenv('DB_USER') ?: "root";
$db_pass = getenv('DB_PASS') ?: "";
$db_name = getenv('DB_NAME') ?: "pengadaan_barang";
$db_port = getenv('DB_PORT') ?: "3306";

// Membuat koneksi dengan SSL untuk remote database
$conn = mysqli_init();
if ($db_host !== "localhost") {
    mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);
    mysqli_options($conn, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, 0);
    mysqli_real_connect($conn, $db_host, $db_user, $db_pass, $db_name, (int)$db_port, NULL, MYSQLI_CLIENT_SSL);
} else {
    mysqli_real_connect($conn, $db_host, $db_user, $db_pass, $db_name, (int)$db_port, NULL);
}

// Jika koneksi gagal, hentikan program dan tampilkan pesan
if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error()
        . "<br>Pastikan server database sudah berjalan dan kredensial sudah benar.");
}

// Mengatur agar karakter Indonesia tampil dengan benar
mysqli_set_charset($conn, "utf8mb4");
