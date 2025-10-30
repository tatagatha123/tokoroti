<?php
include __DIR__ . '/../../koneksi/koneksi.php';

session_start();
date_default_timezone_set("Asia/Jakarta");

// Pastikan user login
if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('Silakan login terlebih dahulu.'); window.location.href='../login/login.php';</script>";
    exit;
}

// Hanya terima POST dan harus ada cartData
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['cartData'])) {
    echo "<script>alert('Akses tidak valid.'); window.location.href='../produk/produk.php';</script>";
    exit;
}

$cartDataJson = $_POST['cartData'];
$cartData = json_decode($cartDataJson, true);

// Validasi cartData
if (!is_array($cartData) || count($cartData) === 0) {
    echo "<script>alert('Data keranjang kosong.'); window.location.href='../produk/produk.php';</script>";
    exit;
}

// Ambil input lain
$alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
$metode = isset($_POST['metode_pembayaran']) ? trim($_POST['metode_pembayaran']) : '';
$catatan = isset($_POST['catatan']) ? trim($_POST['catatan']) : '';
$tanggal_pesan = date("Y-m-d H:i:s");

if (empty($alamat) || empty($metode)) {
    echo "<script>alert('Alamat dan Metode Pembayaran harus diisi!'); window.history.back();</script>";
    exit;
}

// Ambil user dari session
$user_id = (int) $_SESSION['user_id'];
$username = isset($_SESSION['username']) ? mysqli_real_escape_string($koneksi, $_SESSION['username']) : '';
$nama_user = isset($_SESSION['nama_user']) ? mysqli_real_escape_string($koneksi, $_SESSION['nama_user']) : '';

// Siapkan array untuk data produk
$nama_produk_arr = [];
$jumlah_arr = [];
$harga_arr = [];
$total = 0;

foreach ($cartData as $item) {
    $nama = isset($item['nama']) ? trim($item['nama']) : '';
    $harga_satuan = isset($item['harga']) ? (int)$item['harga'] : 0;
    $qty_item = isset($item['qty']) ? (int)$item['qty'] : 0;

    if ($nama === '' || $harga_satuan <= 0 || $qty_item <= 0) continue;

    $subtotal_produk = $harga_satuan * $qty_item;

    $nama_produk_arr[] = $nama;
    $jumlah_arr[] = $qty_item;
    $harga_arr[] = $subtotal_produk;
    $total += $subtotal_produk;
}

if (count($nama_produk_arr) === 0) {
    echo "<script>alert('Tidak ada item valid dalam keranjang.'); window.location.href='../produk/produk.php';</script>";
    exit;
}

// Gabungkan jadi string untuk disimpan
$nama_produk_str = mysqli_real_escape_string($koneksi, implode(",", $nama_produk_arr));
$jumlah_str = mysqli_real_escape_string($koneksi, implode(",", $jumlah_arr));
$harga_str = mysqli_real_escape_string($koneksi, implode(",", $harga_arr));
$total_db = (int)$total;
$alamat_db = mysqli_real_escape_string($koneksi, $alamat);
$metode_db = mysqli_real_escape_string($koneksi, $metode);
$catatan_db = mysqli_real_escape_string($koneksi, $catatan);

// INSERT ke tabel transaksi
$sql = "INSERT INTO transaksi 
        (user_id, username, nama_user, nama_produk, jumlah, harga, total, alamat, metode_pembayaran, catatan, tanggal_pesan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $sql);
if (!$stmt) {
    $err = mysqli_error($koneksi);
    echo "<script>alert('Gagal menyiapkan query: " . addslashes($err) . "'); window.location.href='../order/order.php';</script>";
    exit;
}

$bindTypes = "isssssissss";
if (!mysqli_stmt_bind_param($stmt, $bindTypes,
    $user_id, $username, $nama_user, $nama_produk_str, $jumlah_str, $harga_str,
    $total_db, $alamat_db, $metode_db, $catatan_db, $tanggal_pesan
)) {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    echo "<script>alert('Gagal bind param: " . addslashes($err) . "'); window.location.href='../order/order.php';</script>";
    exit;
}

if (mysqli_stmt_execute($stmt)) {
    $last_id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    // === Update stok produk ===
    foreach ($cartData as $item) {
        $nama_produk = isset($item['nama']) ? trim($item['nama']) : '';
        $qty_item = isset($item['qty']) ? (int)$item['qty'] : 0;

        if ($nama_produk === '' || $qty_item <= 0) continue;

        // Ambil stok
        $query_stok = "SELECT stok FROM produk WHERE nama_produk = ?";
        $stmt_stok = mysqli_prepare($koneksi, $query_stok);
        mysqli_stmt_bind_param($stmt_stok, "s", $nama_produk);
        mysqli_stmt_execute($stmt_stok);
        $result_stok = mysqli_stmt_get_result($stmt_stok);

        if ($row = mysqli_fetch_assoc($result_stok)) {
            $stok_sekarang = (int)$row['stok'];
            $stok_baru = $stok_sekarang - $qty_item;

            if ($stok_baru < 0) {
                echo "<script>alert('Stok produk \"$nama_produk\" tidak mencukupi.'); window.location.href='../produk/produk.php';</script>";
                exit;
            }

            // Update stok baru
            $update_stok = "UPDATE produk SET stok = ? WHERE nama_produk = ?";
            $stmt_update = mysqli_prepare($koneksi, $update_stok);
            mysqli_stmt_bind_param($stmt_update, "is", $stok_baru, $nama_produk);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        }
        mysqli_stmt_close($stmt_stok);
    }

    // === Update laporan penjualan ===
    $tanggal_hari_ini = date("Y-m-d");

    $sqlLaporan = "
        INSERT INTO laporan_penjualan (tanggal, total_transaksi, total_penjualan)
        VALUES ('$tanggal_hari_ini', 1, $total_db)
        ON DUPLICATE KEY UPDATE
            total_transaksi = total_transaksi + 1,
            total_penjualan = total_penjualan + VALUES(total_penjualan)
    ";

    if (!mysqli_query($koneksi, $sqlLaporan)) {
        error_log('Gagal update laporan: ' . mysqli_error($koneksi));
    }

    // Tandai transaksi selesai (setelah semuanya berhasil)
    $_SESSION['transaksi_selesai'] = true;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Transaksi Berhasil | Royal Bakery</title>
    <link rel="stylesheet" href="checkout.css">
</head>
<body>
<div class="box">
  <div class="success-icon">✅</div>
  <h1>Transaksi Berhasil!</h1>
  <p class="thanks">
    Terima kasih, <strong><?= htmlspecialchars($nama_user) ?></strong> 🍰<br>
    Pesananmu telah diterima dan sedang diproses.
  </p>

  <div class="detail">
    <p><strong>Nama:</strong> <?= htmlspecialchars($nama_user) ?> (<?= htmlspecialchars($username) ?>)</p>
    <p><strong>Produk:</strong> <?= htmlspecialchars(implode(', ', $nama_produk_arr)) ?></p>
    <p><strong>Total:</strong> Rp <?= number_format($total_db,0,',','.') ?></p>
    <p><strong>Alamat:</strong> <?= htmlspecialchars($alamat_db) ?></p>
    <p><strong>Pembayaran:</strong> <?= htmlspecialchars($metode_db) ?></p>
    <p><strong>Catatan:</strong> <?= $catatan_db ? htmlspecialchars($catatan_db) : '-' ?></p>
    <p><strong>Tanggal:</strong> <?= $tanggal_pesan ?></p>
  </div>

  <div class="btns">
    <a class="btn btn-primary" href="../Home/homepage.php">🏠 Kembali ke Home</a>
  </div>
</div>

<script>
  try { localStorage.removeItem('royalBakeryCart'); } catch(e){ console.log(e); }
</script>
</body>
</html>
<?php
    exit;
} else {
    $err = mysqli_stmt_error($stmt);
    mysqli_stmt_close($stmt);
    echo "<script>alert('Error menyimpan transaksi: " . addslashes($err) . "'); window.location.href='../order/order.php';</script>";
    exit;
}
?>
