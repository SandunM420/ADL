<?php
/**
 * Admin: Best Sellers
 *
 * Lets admins update the homepage Best Sellers title and image cards.
 */

session_start();

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$data_file = __DIR__ . '/../assets/data/best-sellers.json';
$upload_dir = __DIR__ . '/../assets/images/best-sellers/';

/**
 * Load best seller cards from the JSON data file.
 *
 * @param string $path
 * @return array
 */
function load_best_sellers($path) {
    if (!is_file($path)) {
        return [];
    }

    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

/**
 * Save best seller cards to the JSON data file.
 *
 * @param string $path
 * @param array  $items
 * @return void
 * @throws RuntimeException
 */
function save_best_sellers($path, $items) {
    $json = json_encode($items, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($path, $json . PHP_EOL) === false) {
        throw new RuntimeException('Could not save the best seller data file.');
    }
}

/**
 * Validate and store an uploaded best seller image.
 *
 * @param array  $file
 * @param string $upload_dir
 * @return string Relative image path
 * @throws RuntimeException
 */
function upload_best_seller_image($file, $upload_dir) {
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please choose the image again.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_mime_types, true)) {
        throw new RuntimeException('Unsupported image type. Please upload a JPEG, PNG, or WebP file.');
    }

    if (@getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('The selected file is not a valid image.');
    }

    $ext_map = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $filename = uniqid('best_seller_', true) . '.' . $ext_map[$mime];
    $target = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save the image. Please check file permissions.');
    }

    return 'assets/images/best-sellers/' . $filename;
}

/**
 * Return image metadata for the admin table.
 *
 * @param string $relative_path
 * @return array
 */
function image_meta($relative_path) {
    $path = __DIR__ . '/../' . $relative_path;
    if (!is_file($path)) {
        return ['exists' => false, 'mtime' => time()];
    }

    return ['exists' => true, 'mtime' => filemtime($path)];
}

$errors = [];
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$items = load_best_sellers($data_file);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $index = isset($_POST['index']) ? (int) $_POST['index'] : -1;

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid request token. Please reload the page and try again.';
    } elseif (!isset($items[$index])) {
        $errors[] = 'Unknown best seller card selected.';
    } else {
        $title = trim($_POST['title'] ?? '');

        if ($title === '') {
            $errors[] = 'Please enter a title.';
        } else {
            try {
                $items[$index]['title'] = $title;

                if (!empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                    $items[$index]['image'] = upload_best_seller_image($_FILES['image'], $upload_dir);
                }

                save_best_sellers($data_file, $items);
                $_SESSION['flash_success'] = 'Best seller card was updated successfully.';
                header('Location: /admin/best-sellers.php');
                exit;
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Best Sellers - Admin | Abeywardana Distributors</title>
  <link rel="icon" type="image/png" href="/assets/images/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/style.css">
  <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>

<div class="admin-layout">
  <header class="admin-topbar">
    <span class="admin-topbar__logo">
      <img src="/assets/images/logo-admin.png" alt="Abeywardana Distributors">
    </span>
    <span class="admin-topbar__badge">Admin Portal</span>
    <div class="admin-topbar__spacer"></div>
    <span class="admin-topbar__user"><?php echo $admin_username; ?></span>
    <a href="/admin/logout.php" class="admin-topbar__logout">Log out</a>
  </header>

  <div class="admin-body">
    <aside class="admin-sidebar">
      <nav class="admin-sidebar__nav" aria-label="Admin navigation">
        <a href="/admin/dashboard.php" class="admin-sidebar__link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="3" width="7" height="7"/>
            <rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/>
            <rect x="3" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </a>
        <a href="/admin/add-product.php" class="admin-sidebar__link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Product
        </a>
        <a href="/admin/site-images.php" class="admin-sidebar__link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2" ry="2"/>
            <circle cx="8.5" cy="10" r="1.5"/>
            <path d="M21 15l-5-5L5 19"/>
          </svg>
          Site Images
        </a>
        <a href="/admin/best-sellers.php" class="admin-sidebar__link active" aria-current="page">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
          Best Sellers
        </a>
      </nav>
    </aside>

    <main class="admin-main admin-main--wide" id="main-content">
      <h1 class="admin-page-title">Best Sellers</h1>
      <p class="admin-page-subtitle">Update the homepage Best Sellers cards. Each card uses only an image and a title.</p>

      <?php if ($flash_success !== ''): ?>
        <div class="alert alert-success" role="status"><?php echo htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <div class="admin-table-wrap">
        <table class="admin-table best-seller-table">
          <thead>
            <tr>
              <th>Preview</th>
              <th>Title</th>
              <th>Image</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($items as $index => $item): ?>
              <?php $meta = image_meta($item['image'] ?? ''); ?>
              <tr>
                <td>
                  <div class="site-image-thumb">
                    <?php if ($meta['exists']): ?>
                      <img src="/<?php echo htmlspecialchars($item['image'], ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo (int) $meta['mtime']; ?>" alt="<?php echo htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <?php else: ?>
                      <span>Missing</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <form id="best-seller-form-<?php echo (int) $index; ?>" class="best-seller-form" method="POST" action="/admin/best-sellers.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="index" value="<?php echo (int) $index; ?>">
                    <input class="form-control" type="text" name="title" value="<?php echo htmlspecialchars($item['title'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                  </form>
                </td>
                <td>
                    <input class="form-control" type="file" name="image" accept="image/jpeg,image/png,image/webp" form="best-seller-form-<?php echo (int) $index; ?>">
                    <span class="site-image-usage">JPEG, PNG, or WebP. Leave empty to keep current image.</span>
                </td>
                <td>
                    <button type="submit" class="btn btn-primary btn-sm" form="best-seller-form-<?php echo (int) $index; ?>">Save</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>
</div>

</body>
</html>
