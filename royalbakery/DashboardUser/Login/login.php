<?php
include __DIR__ . '/../../koneksi/koneksi.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Ambil user berdasarkan username
    $stmt = mysqli_prepare($koneksi, "SELECT user_id, username, password, nama_lengkap FROM users WHERE username = ?");
    if (!$stmt) {
        echo "Query error: " . mysqli_error($koneksi);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $user_id, $db_username, $db_password, $nama_lengkap);

    if (mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);

        // Cek password biasa (tanpa hash)
        if ($password === $db_password) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $db_username;
            $_SESSION['nama_user'] = $nama_lengkap;

            echo "<script>alert('Login berhasil!'); window.location='../produk/produk.php';</script>";
            exit;
        } else {
            echo "<script>alert('Password salah!'); window.location='login.php';</script>";
            exit;
        }
    } else {
        mysqli_stmt_close($stmt);
        echo "<script>alert('Username tidak ditemukan!'); window.location='login.php';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Royal Bakery</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>🍩 Login Akun</h2>
        <form method="POST" action="">
            <label>Username:</label>
            <input type="text" name="username" placeholder="Masukkan username" required>

            <label>Password:</label>
            <input type="password" name="password" placeholder="Masukkan password" required>

            <button type="submit">Masuk</button>
            <button type="button" class="back-btn" onclick="window.location.href='../../DashboardUser/Home/homepage.php'">Kembali ke Home</button>

            <div class="footer">
                Belum punya akun? <a href="../register/register.php">Daftar</a>
            </div>
        </form>
    </div>
</body>
</html>
