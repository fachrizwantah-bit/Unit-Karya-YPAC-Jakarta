<?php
/** Mengakhiri sesi login pengguna. */
require_once 'config/fungsi.php';
session_unset();
session_destroy();
header("Location: login.php");
exit;
