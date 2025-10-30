<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Checkout | Royal Bakery</title>
  <link rel="stylesheet" href="orderr.css">
</head>
<body>

  <header>
    <nav class="navbar">
      <div class="logo">🍞 ROYAL BAKERY</div>
      <ul class="nav-links">
        <li><a href="../home/home.php">Home</a></li>
        <li><a href="../about/about.php">About Us</a></li>
      </ul>
    </nav>
  </header>

  <section class="order-container">
    <h2>🧁 Detail Pesanan Anda</h2>
    <div id="orderList"></div>

    <div class="order-summary">
      <h3>Total: Rp <span id="totalHarga">0</span></h3>
    </div>

    <form id="checkoutForm" method="POST" action="../checkout/checkout.php">
      <input type="hidden" name="cartData" id="cartData">

      <div class="form-section">
        <h3>📍 Alamat Pengiriman</h3>
        <textarea name="alamat" id="alamat" required placeholder="Masukkan alamat lengkap Anda..."></textarea>
      </div>

      <div class="form-section">
        <h3>💳 Metode Pembayaran</h3>
        <select name="metode_pembayaran" id="metode_pembayaran" required>
          <option value="">-- Pilih Metode Pembayaran --</option>
          <option value="COD">Bayar di Tempat (COD)</option>
          <option value="Transfer">Transfer Bank</option>
          <option value="QRIS">QRIS</option>
          <option value="E-Wallet">E-Wallet (GoPay, OVO, DANA, dll)</option>
        </select>
      </div>

      <div class="form-section">
        <h3>📝 Catatan Tambahan</h3>
        <textarea name="catatan" id="catatan" placeholder="Contoh: Jangan terlalu manis, potong kecil, dsb..."></textarea>
      </div>

      <div class="checkout-actions">
        <button type="button" onclick="window.location.href='../produk/produk.php'" class="back-btn">Kembali ke Menu</button>
        <button type="submit" id="checkoutBtn" class="checkout-btn">Lanjut ke Pembayaran</button>

      </div>
    </form>
  </section>

  <script src="../js/order.js?v=3"></script>
</body>
</html>