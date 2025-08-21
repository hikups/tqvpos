/**
 * Te Quiero Verde POS - Login JavaScript
 * File: assets/js/login.js
 */

// Clock functionality (centered)
const nowEl = document.getElementById('now');
const tick = () => nowEl.textContent = new Date().toLocaleString('es-MX', {
  weekday: 'long', 
  year: 'numeric', 
  month: 'short', 
  day: 'numeric',
  hour: '2-digit', 
  minute: '2-digit'
});
tick(); 
setInterval(tick, 1000);

// DOM elements
const pinPanel = document.getElementById('pinPanel');
const pinScrim = document.getElementById('pinScrim');
const whoEl = document.getElementById('who');
const pinForm = document.getElementById('pinForm');
const selectedUserIdInput = document.getElementById('selectedUserId');
const pinInput = document.getElementById('pinInput');

// PIN and state management
const dots = Array.from(document.querySelectorAll('#dots .dot'));
const pad = document.getElementById('pad');
let pin = '';
let selectedCard = null;

// Update visual dots based on PIN length
function paintDots() { 
  dots.forEach((dot, index) => {
    dot.classList.toggle('active', index < pin.length);
  });
}

// Staff card selection
document.addEventListener('DOMContentLoaded', function() {
  const staffCards = document.querySelectorAll('.staff-card');
  
  staffCards.forEach(card => {
    card.addEventListener('click', function() {
      // Remove previous selection
      if (selectedCard) {
        selectedCard.classList.remove('selected');
      }
      
      // Select current card
      card.classList.add('selected');
      selectedCard = card;
      
      // Get user data
      const userId = card.dataset.userId;
      const userName = card.dataset.name;
      
      // Set hidden form field
      selectedUserIdInput.value = userId;
      
      // Open PIN drawer
      openPin(userName);
    });
  });
});

// PIN keypad functionality
pad.addEventListener('click', (e) => {
  const k = e.target.dataset.k;
  if (!k) return;
  
  if (k === 'back') {
    // Remove last digit
    pin = pin.slice(0, -1);
  } else if (pin.length < 4) {
    // Add digit (max 4 digits)
    pin += k;
  }
  
  paintDots();
  
  // Auto-submit when PIN is complete
  if (pin.length === 4) {
    setTimeout(() => {
      submitPin();
    }, 200);
  }
});

// Submit PIN form
function submitPin() {
  pinInput.value = pin;
  pinForm.submit();
}

// Open PIN drawer
function openPin(userName) {
  whoEl.innerHTML = `<span class="chip">${userName}</span>`;
  pin = '';
  paintDots();
  pinPanel.classList.add('visible');
  pinPanel.setAttribute('aria-hidden', 'false');
  pinScrim.classList.add('show');
}

// Close PIN drawer
function closePin() {
  pinPanel.classList.remove('visible');
  pinPanel.setAttribute('aria-hidden', 'true');
  pinScrim.classList.remove('show');
  if (selectedCard) {
    selectedCard.classList.remove('selected');
    selectedCard = null;
  }
  pin = '';
  paintDots();
}

// Event listeners for closing drawer
pinScrim.addEventListener('click', closePin);
window.addEventListener('keydown', e => {
  if (e.key === 'Escape' && pinPanel.classList.contains('visible')) {
    closePin();
  }
});

// Handle keyboard input for PIN
window.addEventListener('keydown', e => {
  if (!pinPanel.classList.contains('visible')) return;
  
  if (e.key >= '0' && e.key <= '9') {
    e.preventDefault();
    if (pin.length < 4) {
      pin += e.key;
      paintDots();
      if (pin.length === 4) {
        setTimeout(() => submitPin(), 200);
      }
    }
  } else if (e.key === 'Backspace') {
    e.preventDefault();
    pin = pin.slice(0, -1);
    paintDots();
  } else if (e.key === 'Enter' && pin.length === 4) {
    e.preventDefault();
    submitPin();
  }
});
