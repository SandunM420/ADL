<?php
/**
 * Admin: Delete Product
 *
 * Protected, POST-only endpoint. Deletes a product row and its image
 * file, then redirects back to the dashboard with a flash message.
 */

session_start();

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/dashboard.php');
    exit;
}

require_once __DIR__ . '/../api/config.php';

/**
 * Create and return a PDO connection using constants from config.php.
 *
 * @return PDO
 * @throws PDOException
 */
function get_db_connection() {
    $dsn = 'mysql:host=' . DB_HOST
         . ';dbname=' . DB_NAME
         . ';charset=' . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, DB_USER, DB_PASS, $options);
}

if (
    empty($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
) {
    $_SESSION['flash_error'] = 'Invalid request token. Please try again.';
    header('Location: /admin/dashboard.php');
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/dashboard.php');
    exit;
}

try {
    $pdo  = get_db_connection();
    $stmt = $pdo->prepare('SELECT name, image FROM products WHERE id = ?');
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if ($product) {
        $delete_stmt = $pdo->prepare('DELETE FROM products WHERE id = ?');
        $delete_stmt->execute([$id]);

        $image_file = __DIR__ . '/../' . $product['image'];
        if (is_file($image_file)) {
            unlink($image_file);
        }

        $_SESSION['flash_success'] = '"' . $product['name'] . '" was deleted.';
    }
} catch (PDOException $e) {
    error_log('delete-product.php â€” PDO error: ' . $e->getMessage());
    $_SESSION['flash_error'] = 'A database error occurred while deleting the product.';
}

header('Location: /admin/dashboard.php');
exit;
