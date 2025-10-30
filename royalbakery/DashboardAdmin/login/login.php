<?php
session_start();
include('../../koneksi/koneksi.php');

// Cegah halaman login disimpan di cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    // Cek data admin berdasarkan username
    $sql = "SELECT * FROM admin WHERE username = '$username'";
    $result = mysqli_query($koneksi, $sql);
    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
          
        // Jika password tidak di-hash
        if ($password === $row['password']) {
            // Simpan data ke session
            $_SESSION['admin_id'] = $row['admin_id'];
            $_SESSION['username'] = $row['username'];
            
            // Redirect ke dashboard
            header("Location: ../admin/dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Royal Bakery</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="login-container">
        <h2>Login Admin Royal Bakery</h2>
        <?php if (!empty($error)): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST" action="">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
