<?php
/**
 * Te Quiero Verde POS - Login Page
 * File: login.php
 */

require_once 'config/database.php';

startSession();

// If already logged in, redirect to POS
if (isLoggedIn()) {
    header('Location: pos.php');
    exit();
}

$error = '';
$db = new Database();

// Handle PIN verification
if ($_POST && isset($_POST['user_id'], $_POST['pin'])) {
    $user_id = (int)$_POST['user_id'];
    $pin = $_POST['pin'];
    
    // Verify user and PIN
    $stmt = $db->pdo->prepare("SELECT * FROM users WHERE id = ? AND pin_code = ? AND is_active = 1");
    $stmt->execute([$user_id, $pin]);
    $user = $stmt->fetch();
    
    if ($user) {
        // Create session
        $session_token = bin2hex(random_bytes(32));
        
        // Deactivate old sessions for this user
        $stmt = $db->pdo->prepare("UPDATE user_sessions SET is_active = 0 WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        // Create new session
        $stmt = $db->pdo->prepare("INSERT INTO user_sessions (user_id, session_token) VALUES (?, ?)");
        $stmt->execute([$user_id, $session_token]);
        
        // Update last login
        $stmt = $db->pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['session_token'] = $session_token;
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect to POS
        header('Location: pos.php');
        exit();
    } else {
        $error = 'PIN incorrecto';
    }
}

// Get all active users for the staff list
$stmt = $db->pdo->prepare("SELECT id, full_name, avatar_color FROM users WHERE is_active = 1 ORDER BY full_name");
$stmt->execute();
$users = $stmt->fetchAll();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>POS — Login + PIN</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/login.css">
</head>
<body>
  <div class="stage" id="main">
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
        </nav>
      </aside>

      <!-- Center -->
      <section class="center">
        <div class="topbar"><div class="time" id="now"></div></div>

        <div class="staff-wrap">
          <div class="staff" id="staffList">
            <?php foreach ($users as $user): ?>
            <div class="staff-card" data-user-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['full_name']) ?>" style="--avc: <?= $user['avatar_color'] ?>">
              <div class="avatar"><?= strtoupper(substr($user['full_name'], 0, 1)) ?></div>
              <div>
                <div class="name"><?= htmlspecialchars($user['full_name']) ?></div>
                <div class="sub">Toca para ingresar</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- PIN Drawer -->
  <div class="pin-scrim" id="pinScrim"></div>
  <aside class="pin-wrap" id="pinPanel" aria-hidden="true">
    <div class="pin-top"><h3 class="pin-title">Enter your PIN</h3></div>
    <div class="pin-body">
      <div class="who" id="who"></div>
      <?php if ($error): ?>
        <div class="error-msg"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <div class="dots" id="dots">
        <span class="dot"></span><span class="dot"></span><span class="dot"></span><span class="dot"></span>
      </div>

      <form id="pinForm" method="POST" style="display: none;">
        <input type="hidden" id="selectedUserId" name="user_id">
        <input type="hidden" id="pinInput" name="pin">
      </form>

      <div class="keypad" id="pad" role="group" aria-label="Teclado PIN">
        <button type="button" class="key k1" data-k="1">1</button>
        <button type="button" class="key k2" data-k="2">2</button>
        <button type="button" class="key k3" data-k="3">3</button>

        <button type="button" class="key k4" data-k="4">4</button>
        <button type="button" class="key k5" data-k="5">5</button>
        <button type="button" class="key k6" data-k="6">6</button>

        <button type="button" class="key k7" data-k="7">7</button>
        <button type="button" class="key k8" data-k="8">8</button>
        <button type="button" class="key k9" data-k="9">9</button>

        <div class="ksp" aria-hidden="true"></div>
        <button type="button" class="key k0" data-k="0">0</button>
        <button type="button" class="back-key kback" data-k="back" aria-label="Borrar">←</button>
      </div>
    </div>
  </aside>

  <script src="assets/js/login.js"></script>
</body>
</html>