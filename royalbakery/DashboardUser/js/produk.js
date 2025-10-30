// PRODUK.JS — Menu & Keranjang (Produk Page)

const cartKey = "royalBakeryCart";
let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

const cartCount = document.getElementById("cartCount");
const cartTotal = document.getElementById("cartTotal");
const menuCards = document.querySelectorAll(".menu-card");
const searchBar = document.getElementById("searchBar");
const cartIcon = document.getElementById("cartIcon");

// ---------------------------------
// Utility: simpan cart & update tampilan
// ---------------------------------
function saveCart() {
  localStorage.setItem(cartKey, JSON.stringify(cart));
}

function updateCartDisplay() {
  let totalItems = 0;
  let totalPrice = 0;

  cart.forEach(item => {
    totalItems += item.qty;
    totalPrice += item.harga * item.qty;
  });

  cartCount.textContent = totalItems;
  cartTotal.textContent = totalPrice.toLocaleString("id-ID");
  saveCart();
}

// ---------------------------------
// Inisialisasi tombol + / - di setiap kartu
// ---------------------------------
menuCards.forEach(card => {
  const name = card.dataset.name;
  // baca harga angka dari atribut data-price jika ada, fallback parsing teks
  const priceAttr = card.dataset.price;
  const price = priceAttr ? parseInt(priceAttr, 10) : parseInt(card.querySelector("p").textContent.replace(/\D/g, ''), 10);

  const qtyDisplay = card.querySelector(".qty");
  const plusBtn = card.querySelector(".plus");
  const minusBtn = card.querySelector(".minus");

  // tampilkan jumlah awal (jika sudah ada di cart)
  const existing = cart.find(item => item.nama === name);
  qtyDisplay.textContent = existing ? existing.qty : 0;

  plusBtn.addEventListener("click", () => {
    let item = cart.find(i => i.nama === name);
    if (item) {
      item.qty++;
    } else {
      cart.push({ nama: name, harga: price, qty: 1 });
    }
    qtyDisplay.textContent = cart.find(i => i.nama === name).qty;
    updateCartDisplay();
  });

  minusBtn.addEventListener("click", () => {
    let item = cart.find(i => i.nama === name);
    if (!item) return;
    item.qty--;
    if (item.qty <= 0) {
      cart = cart.filter(i => i.nama !== name);
      qtyDisplay.textContent = 0;
    } else {
      qtyDisplay.textContent = item.qty;
    }
    updateCartDisplay();
  });
});

// ---------------------------------
// Klik ikon keranjang → buka pesanan
// ---------------------------------
if (cartIcon) {
  cartIcon.addEventListener("click", () => {
    if (cart.length === 0) {
      alert("Keranjang Anda kosong 😢");
      return;
    }
    // pindah ke halaman checkout (pesan.php)
    window.location.href = "../../DashboardUser/order/order.php";
  });
}

// ---------------------------------
// Pencarian produk (filter)
// ---------------------------------
if (searchBar) {
  searchBar.addEventListener("input", e => {
    const searchTerm = e.target.value.toLowerCase();
    menuCards.forEach(card => {
      const menuName = card.dataset.name.toLowerCase();
      card.style.display = menuName.includes(searchTerm) ? "block" : "none";
    });
  });
}

// Jalankan pertama kali
updateCartDisplay();