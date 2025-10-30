// Sidebar toggle (mobile)
const menuToggle = document.querySelector('.menu-toggle');
const sidebar = document.querySelector('.sidebar');
const overlay = document.querySelector('.overlay');
const cards = document.querySelectorAll('.card');

// Toggle menu
menuToggle.addEventListener('click', () => {
  sidebar.classList.toggle('active');
  overlay.classList.toggle('active');
});

overlay.addEventListener('click', () => {
  sidebar.classList.remove('active');
  overlay.classList.remove('active');
});

// Scroll animation (cards fade in)
window.addEventListener('scroll', () => {
  cards.forEach(card => {
    const cardTop = card.getBoundingClientRect().top;
    const trigger = window.innerHeight - 100;
    if (cardTop < trigger) {
      card.style.opacity = '1';
      card.classList.add('fade-in');
    }
  });
});

