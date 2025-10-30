// DashboardUser/js/order.js
const cartKey = "royalBakeryCart";
let cart = JSON.parse(localStorage.getItem(cartKey)) || [];

const orderList = document.getElementById("orderList");
const totalHargaDisplay = document.getElementById("totalHarga");
const checkoutForm = document.getElementById("checkoutForm");
const cartDataInput = document.getElementById("cartData");

function renderCart() {
  orderList.innerHTML = "";
  let total = 0;

  if (!cart.length) {
    orderList.innerHTML = "<p>Keranjang Anda kosong 😢</p>";
    totalHargaDisplay.textContent = "0";
    return;
  }

  cart.forEach((item, index) => {
    const subtotal = item.harga * item.qty;
    total += subtotal;

    const div = document.createElement("div");
    div.classList.add("order-item");
    div.innerHTML = `
      <div class="item-info">
        <h4>${item.nama}</h4>
        <p>Rp ${item.harga.toLocaleString("id-ID")} × ${item.qty} = Rp ${subtotal.toLocaleString("id-ID")}</p>
      </div>
      <div class="item-actions">
        <button class="qty-btn minus" data-index="${index}">-</button>
        <span class="qty-display">${item.qty}</span>
        <button class="qty-btn plus" data-index="${index}">+</button>
        <button class="remove-btn" data-index="${index}">🗑️</button>
      </div>
    `;
    orderList.appendChild(div);
  });

  totalHargaDisplay.textContent = total.toLocaleString("id-ID");
  cartDataInput.value = JSON.stringify(cart);
}

orderList.addEventListener("click", e => {
  const idx = e.target.dataset.index;
  if (idx === undefined) return;

  if (e.target.classList.contains("plus")) {
    cart[idx].qty++;
  } else if (e.target.classList.contains("minus")) {
    cart[idx].qty--;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
  } else if (e.target.classList.contains("remove-btn")) {
    cart.splice(idx, 1);
  }

  localStorage.setItem(cartKey, JSON.stringify(cart));
  renderCart();
});

checkoutForm.addEventListener("submit", e => {
  if (!cart.length) {
    e.preventDefault();
    alert("Keranjang Anda kosong!");
    return;
  }

  const alamat = document.getElementById("alamat").value.trim();
  const metode = document.getElementById("metode_pembayaran").value;

  if (!alamat || !metode) {
    e.preventDefault();
    alert("Isi semua data pengiriman dan metode pembayaran.");
    return;
  }

  cartDataInput.value = JSON.stringify(cart);
});

renderCart();
