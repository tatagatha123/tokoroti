<?php
// koneksi.php
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "dbproject_royalbakery"; // sesuaikan nama database kamu

$koneksi = mysqli_connect($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}

// set charset
mysqli_set_charset($koneksi, "utf8mb4");
