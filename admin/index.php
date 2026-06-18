<?php
/**
 * Admin: Login
 *
 * Authenticates against the single fixed admin account defined in
 * api/config.php (ADMIN_USERNAME / ADMIN_PASSWORD_HASH). Redirects to
 * the dashboard if a valid session already exists.
 */

session_start();

require_once __DIR__ . '/../api/config.php';

// Already logged in â€” go straight to the dashboard
if (!empty($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';

// Generate CSRF token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        $error = 'Invalid request token. Please reload the page and try again.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string) ($_POST['password'] ?? '');

        if (
            $username === ADMIN_USERNAME &&
            password_verify($password, ADMIN_PASSWORD_HASH)
        ) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_username']  = $username;
            header('Location: /admin/dashboard.php');
            exit;
        }

        $error = 'Invalid username or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login | Abeywardana Distributors</title>
  <link rel="icon" type="image/png" href="/assets/images/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<div class="admin-login">
  <div class="admin-login__card">
    <img class="admin-login__logo" src="/assets/images/logo.png" alt="Abeywardana Distributors">
    <p class="admin-login__subtitle">Admin Portal</p>

    <?php if ($error !== ''): ?>
      <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <form class="admin-login__form" method="POST" action="/admin/index.php" novalidate>
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

      <div class="form-group">
        <label class="form-label" for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          class="form-control"
          placeholder="Enter your username"
          required
          autocomplete="username"
          autofocus
        >
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          class="form-control"
          placeholder="Enter your password"
          required
          autocomplete="current-password"
        >
      </div>

      <button type="submit" class="btn btn-primary btn-full">Log In</button>
    </form>
  </div>
</div>

</body>
</html>
