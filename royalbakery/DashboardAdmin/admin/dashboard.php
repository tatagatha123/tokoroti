<?php
include('../../koneksi/koneksi.php');
session_start();

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: ../login/login.php");
    exit();
}

// ===================== CRUD PRODUK =====================
if (isset($_POST['tambah_produk'])) {
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];

    // CEK & SIAPKAN FOLDER UPLOAD
    $folder = "../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    // CEK APA ADA FILE GAMBAR YANG DIUPLOAD
    if (!empty($_FILES['gambar']['name'])) {
        $gambar = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];

        // CEK EKSTENSI FILE (HANYA JPG, JPEG, PNG)
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $ext = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_extensions)) {
            echo "<script>alert('Format file tidak diizinkan! Hanya boleh JPG, JPEG, atau PNG.'); window.history.back();</script>";
            exit;
        }

        // BIKIN NAMA FILE UNIK DAN AMAN
        $namaFileBaru = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($gambar));

        // PINDAHKAN FILE KE FOLDER UPLOAD
        move_uploaded_file($tmp, $folder . $namaFileBaru);
        } else {
        $namaFileBaru = ''; // kalau gak upload gambar
    }

         // SIMPAN DATA KE DATABASE
        $query = "INSERT INTO produk (nama_produk, harga, stok, gambar)
        VALUES ('$nama', '$harga', '$stok', '$namaFileBaru')";
        mysqli_query($koneksi, $query);
    echo "<script>alert(' Produk berhasil ditambahkan!'); window.location='dashboard.php';</script>";
    }
    
    // ===================== HAPUS PRODUK =====================
    if (isset($_GET['hapus_produk'])) {
    $id = $_GET['hapus_produk'];

    // HAPUS FILE GAMBAR DARI FOLDER JUGA (BIAR GAK NUMPUK)
    $result = mysqli_query($koneksi, "SELECT gambar FROM produk WHERE produk_id='$id'");
    $data = mysqli_fetch_assoc($result);
    if (!empty($data['gambar']) && file_exists("../../uploads/" . $data['gambar'])) {
        unlink("../../uploads/" . $data['gambar']); 
    }

    mysqli_query($koneksi, "DELETE FROM produk WHERE produk_id='$id'");
    echo "<script>alert('Produk berhasil dihapus!'); window.location='dashboard.php';</script>";
}

// ===================== EDIT PRODUK =====================
// TAMBAHKAN BAGIAN INI BIAR EDIT PRODUK BISA JALAN
if (isset($_POST['edit_produk'])) {
    $id = $_POST['produk_id'];
    $nama = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $gambarLama = $_POST['gambar_lama'];

    $folder = "../../uploads/";
    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    //CEK APAKAH ADA GAMBAR BARU
    if (!empty($_FILES['gambar']['name'])) {
        $gambarBaru = $_FILES['gambar']['name'];
        $tmp = $_FILES['gambar']['tmp_name'];
        $namaFileBaru = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($gambarBaru));
        move_uploaded_file($tmp, $folder . $namaFileBaru);

        // HAPUS GAMBAR LAMA
        if (!empty($gambarLama) && file_exists($folder . $gambarLama)) {
            unlink($folder . $gambarLama);
        }
    } else {
        $namaFileBaru = $gambarLama; 
    }

    //UPDATE DATA KE DATABASE
    $query = "UPDATE produk 
              SET nama_produk='$nama', harga='$harga', stok='$stok', gambar='$namaFileBaru'
              WHERE produk_id='$id'";
    mysqli_query($koneksi, $query);

    echo "<script>alert('Produk berhasil diperbarui!'); window.location='dashboard.php';</script>";
}

// ===================== QUERY DATA =====================
$produk = mysqli_query($koneksi, "SELECT * FROM produk ORDER BY produk_id DESC");
$transaksi = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY tanggal_pesan DESC");
$users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY user_id DESC");

// Laporan penjualan
$laporan_penjualan = mysqli_query($koneksi, "SELECT * FROM laporan_penjualan");

if(!$laporan_penjualan){
    die("Query Error: ".mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Admin - Royal Bakery</title>
<link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="dashboard">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <h1> Royal <br>Bakery 🍞</h1>
        <nav>
            <ul>
                <li onclick="showSection('home')">Home</li>
                <li onclick="showSection('produk')">Produk</li>
                <li onclick="showSection('transaksi')">Transaksi</li>
                <li onclick="showSection('user')">User</li>
                <li onclick="showSection('laporan')">Laporan</li>
            </ul>
        </nav>
        <button class="logout-btn" onclick="window.location.href='../logout/logout.php'">Logout</button>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- HOME -->
        <section id="home" class="section active">
            <div class="welcome-card">
                <h1>Selamat Datang, Admin!👋</h1>
                <p>Semangat mengelola toko hari ini 🍞✨  
                Pantau penjualan, atur produk, dan terus kembangkan Royal Bakery!</p>
            </div>
        </section>

        <!-- PRODUK -->
        <section id="produk" class="section" style="display:none;">
            <h2>Kelola Produk</h2>
            <button class="btn" onclick="openModal('modalTambah')">+ Tambah Produk</button>

            <table class="report-table" style="margin-top:20px;">
                <thead>
                    <tr>
                        <th>ID</th><th>Nama</th><th>Harga</th><th>Stok</th><th>Gambar</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($produk)): ?>
                    <tr>
                        <td><?= $p['produk_id'] ?></td>
                        <td><?= $p['nama_produk'] ?></td>
                        <td>Rp <?= number_format($p['harga'],0,',','.') ?></td>
                        <td><?= $p['stok'] ?></td>
                        <td>
                           <?php if(!empty($p['gambar']) && file_exists('../../uploads/'.$p['gambar'])): ?>
                        <img src="../../uploads/<?= $p['gambar'] ?>" width="60" alt="Gambar Produk">
                            <?php else: ?>
                         <span>Gambar belum ada</span>
                        <?php endif; ?>

                        </td>
                        <td>
                            <button class="btn btn-edit" onclick="editProduk(<?= htmlspecialchars(json_encode($p)) ?>)">Edit</button>
                           <form method="GET" style="display:inline;">
                                <input type="hidden" name="hapus_produk" value="<?= $p['produk_id'] ?>">
                                <button type="submit" class="btn btn-delete" onclick="return confirm('Yakin mau hapus produk ini?')">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- TRANSAKSI -->
        <section id="transaksi" class="section" style="display:none;">
            <h2>Data Transaksi</h2>
            <table class="report-table">
                <thead>
                    <tr><th>ID</th><th>User</th><th>Tanggal</th><th>Total</th><th>Metode</th></tr>
                </thead>
                <tbody>
                    <?php while($t = mysqli_fetch_assoc($transaksi)): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= $t['user_id'] ?></td>
                        <td><?= $t['tanggal_pesan'] ?></td>
                        <td>Rp <?= number_format($t['total'],0,',','.') ?></td>
                        <td><?= $t['metode_pembayaran'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>

        <!-- USER -->
        <section id="user" class="section" style="display:none;">
            <h2>Data User</h2>
            <table class="report-table">
                <thead>
                    <tr><th>ID</th><th>Nama</th><th>Email</th><th>Username</th><th>No. Telp</th></tr>
                </thead>
                <tbody>
                    <?php while($u = mysqli_fetch_assoc($users)): ?>
                    <tr>
                        <td><?= $u['user_id'] ?></td>
                        <td><?= $u['nama_lengkap'] ?></td>
                        <td><?= $u['email'] ?></td>
                        <td><?= $u['username'] ?></td>
                        <td><?= $u['no_hp'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </section>
<!-- LAPORAN -->
<section id="laporan" class="section">
  <h2 class="judul-laporan">Laporan Penjualan</h2>
  <div class="laporan-container">
    <table class="laporan-table">
      <thead>
        <tr>
          <th>Tanggal</th>
          <th>Total Transaksi</th>
          <th>Total Penjualan</th>
        </tr>
      </thead>
      <tbody>
        <?php while($row = mysqli_fetch_assoc($laporan_penjualan)): ?>
        <tr>
          <td><?= $row['tanggal'] ?></td>
          <td><?= $row['total_transaksi'] ?></td>
          <td>Rp <?= number_format($row['total_penjualan'],0,',','.') ?></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- MODAL TAMBAH PRODUK -->
<div id="modalTambah" class="crud-modal" style="display:none;">
    <h2>Tambah Produk</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="nama_produk" placeholder="Nama Produk" required>
        <input type="number" name="harga" placeholder="Harga" required>
        <input type="number" name="stok" placeholder="Stok" required>
        <input type="file" name="gambar" required>
        <button type="submit" name="tambah_produk">Tambahkan</button>
        <button type="button" onclick="closeModal('modalTambah')">Batal</button>
    </form>
</div>

<!-- MODAL EDIT PRODUK -->
<div id="modalEdit" class="crud-modal" style="display:none;">
    <h2>Edit Produk</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="produk_id" id="edit_id">
        <input type="hidden" name="gambar_lama" id="edit_gambar_lama">
        <input type="text" name="nama_produk" id="edit_nama" required>
        <input type="number" name="harga" id="edit_harga" required>
        <input type="number" name="stok" id="edit_stok" required>
        <input type="file" name="gambar" id="edit_gambar">
        <div id="preview_container" style="margin-top:10px; text-align:center;">
            <img id="preview_gambar" src="" alt="Gambar Lama" width="100" style="display:none; border-radius:8px;">
        </div>
        <button type="submit" name="edit_produk">Simpan Perubahan</button>
        <button type="button" onclick="closeModal('modalEdit')">Batal</button>
    </form>
</div>
<script>
// navigasi dinamis
function showSection(id) {
    console.log("Membuka section:", id);
    document.querySelectorAll('.section').forEach(s => s.style.display = 'none');
    document.getElementById(id).style.display = 'block';
}

// modal CRUD
function openModal(id) {
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}

function editProduk(data) {
    openModal('modalEdit');
    document.getElementById('edit_id').value = data.produk_id;
    document.getElementById('edit_nama').value = data.nama_produk;
    document.getElementById('edit_harga').value = data.harga;
    document.getElementById('edit_stok').value = data.stok;
    document.getElementById('edit_gambar_lama').value = data.gambar;

    const preview = document.getElementById('preview_gambar');
    if (data.gambar && data.gambar !== '') {
        preview.src = '../../uploads/' + data.gambar;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}

// cegah kembali ke dashboard setelah logout
window.history.pushState(null, "", window.location.href);
window.onpopstate = function() {
    window.history.pushState(null, "", window.location.href);
}

document.addEventListener("DOMContentLoaded", function() {
    showSection('home');
});
</script>
