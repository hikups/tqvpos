<?php
/**
 * Te Quiero Verde POS - Main POS Interface
 * File: pos.php
 */

require_once 'config/database.php';

// Require login
requireLogin();

$user = getCurrentUser();
if (!$user) {
    logout();
}

// Handle logout
if (isset($_GET['logout'])) {
    logout();
}

$db = new Database();

// Get sample categories and products for now (we'll expand this later)
$categories = [
    ['id' => 1, 'name' => 'Desayunos', 'color_class' => 'p1', 'count' => 13],
    ['id' => 2, 'name' => 'Soups', 'color_class' => 'p2', 'count' => 8],
    ['id' => 3, 'name' => 'Pastas', 'color_class' => 'p3', 'count' => 10],
    ['id' => 4, 'name' => 'Hamburguesas', 'color_class' => 'p4', 'count' => 9],
    ['id' => 5, 'name' => 'Platos fuertes', 'color_class' => 'p5', 'count' => 12],
    ['id' => 6, 'name' => 'Postres', 'color_class' => 'p6', 'count' => 7],
    ['id' => 7, 'name' => 'Extras', 'color_class' => 'p7', 'count' => 14],
    ['id' => 8, 'name' => 'Bebidas', 'color_class' => 'p8', 'count' => 18],
];

$sample_products = [
    ['name' => 'Chilaquiles', 'price' => 95.00, 'category' => 'p1', 'printer' => 'Cocina'],
    ['name' => 'Sopa Azteca', 'price' => 85.00, 'category' => 'p2', 'printer' => 'Cocina'],
    ['name' => 'Pasta Alfredo', 'price' => 130.00, 'category' => 'p3', 'printer' => 'Cocina'],
    ['name' => 'Hamburguesa Verde', 'price' => 145.00, 'category' => 'p4', 'printer' => 'Cocina'],
    ['name' => 'Pechuga Asada', 'price' => 155.00, 'category' => 'p5', 'printer' => 'Cocina'],
    ['name' => 'Cheesecake', 'price' => 90.00, 'category' => 'p6', 'printer' => 'Cocina'],
    ['name' => 'Papas Gajo', 'price' => 60.00, 'category' => 'p7', 'printer' => 'Cocina'],
    ['name' => 'Jugo Verde', 'price' => 55.00, 'category' => 'p8', 'printer' => 'Jugos'],
];

// Sample active tables (we'll make this dynamic later)
$active_tables = [
    ['id' => 'M4', 'name' => 'Mesa 4', 'status' => 'Enviado a cocina', 'time_mins' => 8, 'color' => 'p1'],
    ['id' => 'M3', 'name' => 'Mesa 3', 'status' => 'Enviado a jugos', 'time_mins' => 4, 'color' => 'p2'],
    ['id' => 'J1', 'name' => 'Jardín 1', 'status' => 'Enviado a cocina', 'time_mins' => 13, 'color' => 'p3'],
];
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>POS — Menú - <?= htmlspecialchars($user['full_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/pos.css">
</head>
<body>
  <div class="stage">
    <main class="app">
      <!-- Sidebar -->
      <aside class="side">
        <div class="brand"><img src="https://www.tqverde.mx/images/logo.png" alt="Te quiero verde"></div>
        <nav class="nav">
          <a class="active" href="#">Menú</a>
          <a href="#">Mesas</a>
          <a href="#">Reservaciones</a>
          <a href="#">Chat</a>
          <a href="#">Dashboard</a>
          <a href="#">Contabilidad</a>
          <a href="#">Ajustes</a>
          <?php if ($user['role'] === 'admin'): ?>
          <a href="#">Admin Panel</a>
          <?php endif; ?>
          <a href="?logout=1" style="margin-top: 20px; color: var(--p5);">Cerrar Sesión</a>
        </nav>

        <div class="tables--side">
          <?php foreach ($active_tables as $table): ?>
          <div class="t-row" data-c="<?= $table['color'] ?>">
            <div class="badge"><?= $table['id'] ?></div>
            <div class="t-body">
              <div class="t-title"><?= $table['name'] ?></div>
              <div class="t-sub"><?= $table['status'] ?> · <span class="t-time" data-sent-mins="<?= $table['time_mins'] ?>">hace <?= $table['time_mins'] ?>:00</span></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </aside>

      <!-- Center -->
      <section class="center">
        <div class="topbar">
          <div class="search">
            <span class="loupe"></span>
            <input placeholder="Search" id="searchInput" />
          </div>
          <div class="user-info">
            <span><?= htmlspecialchars($user['full_name']) ?> (<?= ucfirst($user['role']) ?>)</span>
          </div>
        </div>

        <!-- Categories -->
        <div class="cats">
          <?php foreach ($categories as $category): ?>
          <article class="cat <?= $category['color_class'] ?>" data-category="<?= $category['id'] ?>">
            <div class="ico">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <?php
                // Different icons for each category
                $icons = [
                  1 => '<path d="M5 7h11v6a4 4 0 0 1-4 4H9a4 4 0 0 1-4-4V7Z"/><path d="M16 8h3a2 2 0 1 1 0 4h-3"/>',
                  2 => '<path d="M3 12h18"/><path d="M5 12a7 7 0 0 0 14 0"/>',
                  3 => '<path d="M3 8h18M6 12h12M8 16h8"/>',
                  4 => '<path d="M5 12h14l-2 5H7l-2-5Z"/><path d="M8 9h8"/>',
                  5 => '<circle cx="12" cy="12" r="7"/><path d="M9 12h6"/>',
                  6 => '<path d="M4 12h16v6H4zM12 6a6 6 0 0 1 6 6H6a6 6 0 0 1 6-6z"/>',
                  7 => '<path d="M12 5v14M5 12h14"/>',
                  8 => '<path d="M10 2h4v4l2 2v12a3 3 0 0 1-3 3h-2a3 3 0 0 1-3-3V8l2-2z"/>',
                ];
                echo $icons[$category['id']] ?? '<circle cx="12" cy="12" r="10"/>';
                ?>
              </svg>
            </div>
            <h4><?= htmlspecialchars($category['name']) ?></h4>
            <div class="count"><?= $category['count'] ?> items</div>
          </article>
          <?php endforeach; ?>
        </div>

        <div class="hr"></div>

        <!-- Items -->
        <div class="shelf">
          <?php foreach ($sample_products as $product): ?>
          <article class="dish" data-name="<?= htmlspecialchars($product['name']) ?>" data-price="<?= $product['price'] ?>" data-c="<?= $product['category'] ?>">
            <div class="dish__meta">Orders — <?= $product['printer'] ?></div>
            <h5 class="dish__title"><?= htmlspecialchars($product['name']) ?></h5>
            <div class="dish__price">$<?= number_format($product['price'], 2) ?></div>
            <div class="dish__qty">
              <button class="btn minus">−</button>
              <div class="dish__count">0</div>
              <button class="btn plus">+</button>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- Right -->
      <aside class="order-col">
        <div class="order-head">
          <h3 id="tableTitle">Nueva Orden</h3>
          <button class="edit-btn" title="Seleccionar Mesa" id="selectTableBtn">🏷</button>
        </div>

        <div class="order">
          <div class="order-list" id="orderList"></div>
          <div class="calc">
            <div class="row items"><span>Artículos</span><span id="itemsCount">0</span></div>
            <div class="row total"><span>Total</span><span id="total">$0.00</span></div>
          </div>
          <div class="actions">
            <button class="btn-outline btn-outline--green" id="btnSend">Enviar a cocina</button>
            <button class="btn-outline btn-outline--red btn-disabled" id="btnPay" disabled>Cobrar</button>
          </div>
        </div>
      </aside>
    </main>
  </div>

  <!-- Table Selection Modal (we'll add this functionality later) -->
  <div id="tableModal" class="modal" style="display: none;">
    <div class="modal-content">
      <h3>Seleccionar Mesa</h3>
      <div class="table-grid">
        <!-- Table selection will go here -->
      </div>
    </div>
  </div>

  <script src="assets/js/pos.js"></script>
</body>
</html>