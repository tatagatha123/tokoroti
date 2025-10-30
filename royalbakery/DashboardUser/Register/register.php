<?php
include __DIR__ . '/../../koneksi/koneksi.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $no_hp = trim($_POST['no_hp']);
    $alamat = trim($_POST['alamat']);

    // Validasi 
    if ($username === "" || $email === "" || $password === "") {
        echo "<script>alert('Username, email, dan password wajib diisi.'); window.location='register.php';</script>";
        exit;
    }

    // Cek apakah username sudah digunakan
    $cek = mysqli_prepare($koneksi, "SELECT user_id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($cek, "s", $username);
    mysqli_stmt_execute($cek);
    mysqli_stmt_store_result($cek);

    if (mysqli_stmt_num_rows($cek) > 0) {
        mysqli_stmt_close($cek);
        echo "<script>alert('Username sudah digunakan, silakan pilih yang lain.'); window.location='register.php';</script>";
        exit;
    }
    mysqli_stmt_close($cek);

    // Simpan data 
    $stmt = mysqli_prepare($koneksi, "INSERT INTO users (username, password, email, nama_lengkap, no_hp, alamat, tanggal_daftar) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    if (!$stmt) {
        echo "Gagal menyiapkan query: " . mysqli_error($koneksi);
        exit;
    }
    mysqli_stmt_bind_param($stmt, "ssssss", $username, $password, $email, $nama_lengkap, $no_hp, $alamat);

    if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='../login/login.php';</script>";
        exit;
    } else {
        $err = mysqli_error($koneksi);
        mysqli_stmt_close($stmt);
        echo "<script>alert('Terjadi kesalahan saat menyimpan data: " . addslashes($err) . "'); window.location='register.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Royal Bakery</title>
    <link rel="stylesheet" href="/royalbakery/DashboardUser/Register/register.css">

</head>
<body>
    <div class="register-container">
        <h2>🍰 Registrasi Akun</h2>
        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" placeholder="Masukkan username" required>

            <label>Email:</label>
            <input type="email" name="email" placeholder="Masukkan email" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Masukkan password" required>

            <label>Nama Lengkap:</label>
            <input type="text" name="nama_lengkap" placeholder="Masukkan nama lengkap">

            <label>No. HP:</label>
            <input type="text" name="no_hp" placeholder="Masukkan nomor HP">

            <label>Alamat:</label>
            <textarea name="alamat" rows="3" placeholder="Masukkan alamat"></textarea>

            <button type="submit">Daftar Sekarang</button>

            <div class="footer">
                Sudah punya akun? <a href="../login/login.php">Login</a>
            </div>
        </form>
    </div>
</body>
</html>
