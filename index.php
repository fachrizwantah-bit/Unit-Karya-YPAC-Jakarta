<?php
/**
 * Halaman pembuka. Jika sudah login -> ke dashboard.
 * Jika belum -> ke halaman login.
 */
require_once 'config/fungsi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
} else {
    header("Location: login.php");
}
exit;
