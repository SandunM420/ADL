<?php
/**
 * Products API Endpoint
 *
 * Returns a JSON array of visible products filtered by category and
 * optionally by subcategory.
 *
 * GET /api/products.php?category=wines&subcategory=chile
 * GET /api/products.php?category=sparkling-wine   (no subcategory)
 */

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

require_once __DIR__ . '/config.php';

/**
 * Create and return a PDO connection using the constants from config.php.
 *
 * @return PDO
 * @throws PDOException if the connection cannot be established
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

/**
 * Output a JSON error response and halt execution.
 *
 * @param string $message Error message shown in the JSON body
 * @param int    $status  HTTP status code to send
 * @return void
 */
function send_error($message, $status = 500) {
    http_response_code($status);
    echo json_encode(['error' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

// Only accept GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_error('Method not allowed.', 405);
}

// Read and trim query parameters
$category    = isset($_GET['category'])    ? trim($_GET['category'])    : '';
$subcategory = isset($_GET['subcategory']) ? trim($_GET['subcategory']) : null;

if ($category === '') {
    send_error('Missing required parameter: category.', 400);
}

try {
    $pdo = get_db_connection();

    if ($subcategory !== null && $subcategory !== '') {
        // Filter by both category and subcategory
        $stmt = $pdo->prepare(
            'SELECT id, name, category, subcategory, country, description, image, visible, created_at
               FROM products
              WHERE category = ?
                AND subcategory = ?
                AND visible = 1
              ORDER BY name ASC'
        );
        $stmt->execute([$category, $subcategory]);
    } else {
        // Sparkling Wine and any category without a subcategory filter
        $stmt = $pdo->prepare(
            'SELECT id, name, category, subcategory, country, description, image, visible, created_at
               FROM products
              WHERE category = ?
                AND visible = 1
              ORDER BY name ASC'
        );
        $stmt->execute([$category]);
    }

    $products = $stmt->fetchAll();

    echo json_encode($products, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log('products.php — PDO error: ' . $e->getMessage());
    send_error('Database error. Please try again later.', 500);
} catch (Exception $e) {
    error_log('products.php — unexpected error: ' . $e->getMessage());
    send_error('An unexpected error occurred.', 500);
}
