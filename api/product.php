<?php
/**
 * Single Product API Endpoint
 *
 * GET /api/product.php?id=123
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config.php';

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

function send_error($message, $status = 500) {
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_error('Method not allowed.', 405);
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    send_error('Missing required parameter: id.', 400);
}

try {
    $pdo = get_db_connection();
    $stmt = $pdo->prepare(
        'SELECT id, name, item_code, grape_type, alcohol, pack_size, category, subcategory, country, description, image, visible, created_at
           FROM products
          WHERE id = ?
            AND visible = 1
          LIMIT 1'
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    if (!$product) {
        send_error('Product not found.', 404);
    }

    echo json_encode($product, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log('product.php - PDO error: ' . $e->getMessage());
    send_error('Database error. Please try again later.', 500);
} catch (Exception $e) {
    error_log('product.php - unexpected error: ' . $e->getMessage());
    send_error('An unexpected error occurred.', 500);
}
