/**
 * Te Quiero Verde POS - Main POS JavaScript
 * File: assets/js/pos.js
 */

// Utility functions
const currency = v => `$${(+v).toFixed(2)}`;

// Global state
const order = new Map();
let sentToKitchen = false;
let currentTable = null;

// DOM elements
const listEl = document.getElementById('orderList');
const totalEl = document.getElementById('total');
const itemsEl = document.getElementById('itemsCount');
const btnSend = document.getElementById('btnSend');
const btnPay = document.getElementById('btnPay');
const tableTitleEl = document.getElementById('tableTitle');
const searchInput = document.getElementById('searchInput');

// Order management functions
const totalItems = () => { 
  let n = 0; 
  order.forEach(it => n += it.qty); 
  return n; 
};

const refreshPayState = () => {
  const hasItems = order.size > 0;
  const canPay = hasItems && sentToKitchen;
  btnPay.disabled = !canPay;
  btnPay.classList.toggle('btn-disabled', !canPay);
};

const renderOrder = (appearName = null) => {
  listEl.innerHTML = '';
  let sum = 0, i = 1;
  order.forEach((it, name) => {
    const d = document.createElement('div');
    d.className = 'line' + (name === appearName ? ' appear' : '');
    d.innerHTML = `
      <div class="n">${i++}</div>
      <div class="name">${name} ×${it.qty}</div>
      <div class="price">${currency(it.qty * it.price)}</div>
    `;
    listEl.appendChild(d); 
    sum += it.qty * it.price;
  });
  totalEl.textContent = currency(sum);
  itemsEl.textContent = totalItems();
  refreshPayState();
};

const setSelected = (card, on) => card.classList.toggle('selected', on);

// Initialize dish interactions
document.addEventListener('DOMContentLoaded', function() {
  initializeDishes();
  initializeTimers();
  initializeSearch();
  initializeButtons();
});

function initializeDishes() {
  document.querySelectorAll('.dish').forEach(card => {
    const name = card.dataset.name;
    const price = parseFloat(card.dataset.price);
    const count = card.querySelector('.dish__count');
    const plus = card.querySelector('.plus');
    const minus = card.querySelector('.minus');

    const getQ = () => parseInt(count.textContent, 10);
    const setQ = q => { count.textContent = q; };

    plus.addEventListener('click', () => {
      const q = getQ() + 1; 
      setQ(q); 
      setSelected(card, q > 0);
      order.set(name, {qty: q, price}); 
      if (sentToKitchen) sentToKitchen = false;
      renderOrder(name);
    });

    minus.addEventListener('click', () => {
      const q = Math.max(0, getQ() - 1); 
      setQ(q);
      if (q === 0) { 
        order.delete(name); 
      } else { 
        order.set(name, {qty: q, price}); 
      }
      if (sentToKitchen) sentToKitchen = false; 
      setSelected(card, q > 0);
      renderOrder(name);
    });
  });
}

function initializeTimers() {
  function tickTimers() {
    document.querySelectorAll('.t-time').forEach(el => {
      const mins = +el.dataset.sentMins || 0;
      if (!el.dataset._t) { 
        el.dataset._t = Date.now() - mins * 60000; 
      }
      const diff = Date.now() - (+el.dataset._t);
      const mm = Math.floor(diff / 60000); 
      const ss = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
      el.textContent = `hace ${mm}:${ss}`;
      
      // Color coding for time
      const parentRow = el.closest('.t-row');
      if (parentRow) {
        if (mm >= 15) {
          parentRow.style.backgroundColor = 'rgba(241, 200, 208, 0.1)'; // Red tint
        } else if (mm >= 10) {
          parentRow.style.backgroundColor = 'rgba(243, 225, 195, 0.1)'; // Orange tint
        } else {
          parentRow.style.backgroundColor = '';
        }
      }
    });
  }
  tickTimers(); 
  setInterval(tickTimers, 1000);
}

function initializeSearch() {
  searchInput.addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase().trim();
    const dishes = document.querySelectorAll('.dish');
    
    dishes.forEach(dish => {
      const name = dish.dataset.name.toLowerCase();
      const shouldShow = searchTerm === '' || name.includes(searchTerm);
      dish.style.display = shouldShow ? 'flex' : 'none';
    });
    
    // Also filter categories based on visible items
    if (searchTerm) {
      document.querySelector('.cats').style.display = 'none';
      document.querySelector('.hr').style.display = 'none';
    } else {
      document.querySelector('.cats').style.display = 'grid';
      document.querySelector('.hr').style.display = 'block';
    }
  });
}

function initializeButtons() {
  btnSend.addEventListener('click', () => {
    if (order.size === 0) { 
      showAlert('Agrega productos antes de enviar a cocina.'); 
      return; 
    }
    
    if (!currentTable) {
      showAlert('Selecciona una mesa primero.');
      return;
    }
    
    sentToKitchen = true; 
    refreshPayState(); 
    showAlert('Enviado a cocina.');
    
    // Here you would normally send to server
    // sendOrderToKitchen();
  });

  btnPay.addEventListener('click', () => { 
    if (btnPay.disabled) return; 
    
    // Here you would normally process payment
    showAlert('Procesando pago...');
    // processPayment();
  });
  
  // Table selection button
  document.getElementById('selectTableBtn').addEventListener('click', () => {
    showTableSelector();
  });
}

// Category filtering
document.addEventListener('DOMContentLoaded', function() {
  document.querySelectorAll('.cat').forEach(cat => {
    cat.addEventListener('click', function() {
      const categoryId = this.dataset.category;
      filterByCategory(categoryId);
    });
  });
});

function filterByCategory(categoryId) {
  const dishes = document.querySelectorAll('.dish');
  const categoryClass = `p${categoryId}`;
  
  dishes.forEach(dish => {
    const shouldShow = dish.dataset.c === categoryClass;
    dish.style.display = shouldShow ? 'flex' : 'none';
  });
  
  // Hide categories and search when filtering
  document.querySelector('.cats').style.display = 'none';
  document.querySelector('.hr').style.display = 'none';
  searchInput.value = '';
  
  // Add a "Show All" option or reset button
  showBackToMenuButton();
}

function showBackToMenuButton() {
  // Check if button already exists
  let backBtn = document.getElementById('backToMenu');
  if (!backBtn) {
    backBtn = document.createElement('button');
    backBtn.id = 'backToMenu';
    backBtn.textContent = '← Ver todo el menú';
    backBtn.style.cssText = `
      position: absolute;
      top: -40px;
      left: 0;
      background: var(--panel);
      color: var(--white);
      border: 1px solid var(--line);
      padding: 8px 16px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 14px;
      z-index: 10;
    `;
    
    backBtn.addEventListener('click', resetMenuView);
    document.querySelector('.shelf').style.position = 'relative';
    document.querySelector('.shelf').appendChild(backBtn);
  }
}

function resetMenuView() {
  // Show all dishes
  document.querySelectorAll('.dish').forEach(dish => {
    dish.style.display = 'flex';
  });
  
  // Show categories and HR
  document.querySelector('.cats').style.display = 'grid';
  document.querySelector('.hr').style.display = 'block';
  
  // Remove back button
  const backBtn = document.getElementById('backToMenu');
  if (backBtn) {
    backBtn.remove();
  }
}

// Table selection functionality
function showTableSelector() {
  // This is a simple prompt for now - you can enhance this later with a proper modal
  const tableOptions = [
    'Mesa 1', 'Mesa 2', 'Mesa 3', 'Mesa 4', 'Mesa 5',
    'Mesa 6', 'Mesa 7', 'Mesa 8', 'Mesa 9', 'Mesa 10',
    'Jardín 1', 'Jardín 2', 'Jardín 3', 'Terraza 1', 'Barra 1'
  ];
  
  let tableList = 'Selecciona una mesa:\\n';
  tableOptions.forEach((table, index) => {
    tableList += `${index + 1}. ${table}\\n`;
  });
  
  const selection = prompt(tableList + '\\nEscribe el número:');
  const tableIndex = parseInt(selection) - 1;
  
  if (tableIndex >= 0 && tableIndex < tableOptions.length) {
    currentTable = tableOptions[tableIndex];
    tableTitleEl.textContent = currentTable;
    showAlert(`Mesa seleccionada: ${currentTable}`);
  }
}

// Simple alert function (you can enhance this with better UI later)
function showAlert(message) {
  // Create a temporary alert div
  const alertDiv = document.createElement('div');
  alertDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: var(--panel);
    color: var(--white);
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid var(--line);
    z-index: 1000;
    animation: slideInAlert 0.3s ease-out;
  `;
  
  // Add animation CSS if not exists
  if (!document.getElementById('alertStyles')) {
    const style = document.createElement('style');
    style.id = 'alertStyles';
    style.textContent = `
      @keyframes slideInAlert {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
    `;
    document.head.appendChild(style);
  }
  
  alertDiv.textContent = message;
  document.body.appendChild(alertDiv);
  
  // Remove after 3 seconds
  setTimeout(() => {
    alertDiv.style.animation = 'slideInAlert 0.3s ease-out reverse';
    setTimeout(() => alertDiv.remove(), 300);
  }, 3000);
}

// Initialize everything when the page loads
renderOrder();

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
  // ESC to reset menu view
  if (e.key === 'Escape') {
    resetMenuView();
  }
  
  // F1 to select table
  if (e.key === 'F1') {
    e.preventDefault();
    showTableSelector();
  }
  
  // F2 to send to kitchen
  if (e.key === 'F2') {
    e.preventDefault();
    btnSend.click();
  }
  
  // F3 to process payment
  if (e.key === 'F3') {
    e.preventDefault();
    if (!btnPay.disabled) btnPay.click();
  }
});
