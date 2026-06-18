<?php
/**
 * Admin: Site Images
 *
 * Lets admins replace fixed website images without changing filenames.
 * Product images are intentionally excluded because products already have
 * their own add/edit image workflow.
 */

session_start();

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
    exit;
}

$managed_images = [
    'home_hero_1' => [
        'label' => 'Home Hero 1',
        'path' => 'assets/images/Home hero final 1.png',
        'usage' => 'Home page rotating hero banner',
        'slot' => '100vw x min 420px',
        'fit' => 'Background, auto 108%, right center',
        'guidance' => 'Keep important subjects away from the left text area.',
    ],
    'home_hero_2' => [
        'label' => 'Home Hero 2',
        'path' => 'assets/images/Home hero final 2.png',
        'usage' => 'Home page rotating hero banner',
        'slot' => '100vw x min 420px',
        'fit' => 'Background, auto 108%, right center',
        'guidance' => 'Keep important subjects away from the left text area.',
    ],
    'home_hero_3' => [
        'label' => 'Home Hero 3',
        'path' => 'assets/images/Home hero final 3.png',
        'usage' => 'Home page rotating hero banner',
        'slot' => '100vw x min 420px',
        'fit' => 'Background, auto 108%, right center',
        'guidance' => 'Keep important product details centered or right aligned.',
    ],
    'about_hero' => [
        'label' => 'About Us Hero',
        'path' => 'assets/images/About us hero final 1.png',
        'usage' => 'About Us page top hero banner',
        'slot' => '100vw x min 420px',
        'fit' => 'Background, auto 122%, right center',
        'guidance' => 'Keep important subjects away from the left text overlay.',
    ],
    'contact_hero' => [
        'label' => 'Contact Us Hero',
        'path' => 'assets/images/Contact us hero final banner 1.png',
        'usage' => 'Contact Us page top hero banner',
        'slot' => '100vw x min 420px',
        'fit' => 'Background, auto 122%, right center',
        'guidance' => 'Keep important subjects away from the left text overlay.',
    ],
    'about_showcase' => [
        'label' => 'About Showcase Banner',
        'path' => 'assets/images/about us banner 2.png',
        'usage' => 'About Us page showcase image',
        'slot' => 'Container width, 16:7 ratio',
        'fit' => 'Image, cover',
        'guidance' => 'Edges may crop. Keep key content centered.',
    ],
    'home_trust_1' => [
        'label' => 'Home Wide Banner 1',
        'path' => 'assets/images/trust-strip.png',
        'usage' => 'Home page wide rotating banner',
        'slot' => 'Container width, 16:7 ratio',
        'fit' => 'Background, contain, center',
        'guidance' => 'Full image is visible inside the rounded frame.',
    ],
    'home_trust_2' => [
        'label' => 'Home Wide Banner 2',
        'path' => 'assets/images/hero banner 4.png',
        'usage' => 'Home page wide rotating banner',
        'slot' => 'Container width, 16:7 ratio',
        'fit' => 'Background, contain, center',
        'guidance' => 'Full image is visible inside the rounded frame.',
    ],
    'gallery_wines' => [
        'label' => 'Gallery Wines',
        'path' => 'assets/images/gallery-wine.png',
        'usage' => 'Home page visual showcase',
        'slot' => 'Desktop: 1 column x 2 rows, about 600px tall',
        'fit' => 'Image, cover',
        'guidance' => 'Cover crops edges. Keep bottles centered with top/bottom padding.',
    ],
    'gallery_champagne' => [
        'label' => 'Gallery Champagne',
        'path' => 'assets/images/gallery-champagne.png',
        'usage' => 'Home page visual showcase',
        'slot' => 'Desktop tile: 1 column x 300px',
        'fit' => 'Image, cover',
        'guidance' => 'Cover crops edges. Keep important details centered.',
    ],
    'gallery_spirits' => [
        'label' => 'Gallery Spirits',
        'path' => 'assets/images/gallery-spirits.png',
        'usage' => 'Home page visual showcase',
        'slot' => 'Desktop tile: 1 column x 300px',
        'fit' => 'Image, cover',
        'guidance' => 'Cover crops edges. Keep important details centered.',
    ],
    'gallery_sparkling' => [
        'label' => 'Gallery Sparkling Wine',
        'path' => 'assets/images/gallery-sparkling.png',
        'usage' => 'Home page visual showcase',
        'slot' => 'Desktop tile: 1 column x 300px',
        'fit' => 'Image, cover',
        'guidance' => 'Cover crops edges. Keep important details centered.',
    ],
    'gallery_whiskey' => [
        'label' => 'Gallery Whiskey',
        'path' => 'assets/images/gallery-whiskey.jpg',
        'usage' => 'Home page visual showcase',
        'slot' => 'Desktop tile: 1 column x 300px',
        'fit' => 'Image, cover',
        'guidance' => 'Cover crops edges. Keep important details centered.',
    ],
    'category_wines' => [
        'label' => 'Category Wines',
        'path' => 'assets/images/cat-wines.jpg',
        'usage' => 'Home page collection card',
        'slot' => 'Collection card, min 300px tall',
        'fit' => 'Background, cover',
        'guidance' => 'Cover crops edges. Keep subject away from card edges.',
    ],
    'category_champagne' => [
        'label' => 'Category Champagne',
        'path' => 'assets/images/cat-champagne.jpg',
        'usage' => 'Home page collection card',
        'slot' => 'Collection card, min 300px tall',
        'fit' => 'Background, cover',
        'guidance' => 'Cover crops edges. Keep subject away from card edges.',
    ],
    'category_sparkling' => [
        'label' => 'Category Sparkling Wine',
        'path' => 'assets/images/cat-sparkling.jpg',
        'usage' => 'Home page collection card',
        'slot' => 'Collection card, min 300px tall',
        'fit' => 'Background, cover',
        'guidance' => 'Cover crops edges. Keep subject away from card edges.',
    ],
    'category_spirits' => [
        'label' => 'Category Spirits',
        'path' => 'assets/images/cat-spirits.jpg',
        'usage' => 'Home page collection card',
        'slot' => 'Collection card, min 300px tall',
        'fit' => 'Background, cover',
        'guidance' => 'Cover crops edges. Keep subject away from card edges.',
    ],
];

function image_abs_path($relative_path) {
    return realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative_path);
}

function format_bytes($bytes) {
    if ($bytes >= 1048576) {
        return round($bytes / 1048576, 2) . ' MB';
    }
    return round($bytes / 1024, 1) . ' KB';
}

function image_meta($relative_path) {
    $path = image_abs_path($relative_path);
    if (!is_file($path)) {
        return [
            'exists' => false,
            'width' => null,
            'height' => null,
            'size' => null,
            'mtime' => time(),
        ];
    }

    $size = @getimagesize($path);
    return [
        'exists' => true,
        'width' => $size ? $size[0] : null,
        'height' => $size ? $size[1] : null,
        'size' => filesize($path),
        'mtime' => filemtime($path),
    ];
}

function allowed_mime_for_path($relative_path) {
    $ext = strtolower(pathinfo($relative_path, PATHINFO_EXTENSION));
    if ($ext === 'jpg' || $ext === 'jpeg') {
        return ['image/jpeg'];
    }
    if ($ext === 'png') {
        return ['image/png'];
    }
    if ($ext === 'webp') {
        return ['image/webp'];
    }
    return [];
}

function slot_px_note($image_key) {
    if (in_array($image_key, ['home_hero_1', 'home_hero_2', 'home_hero_3', 'about_hero', 'contact_hero'], true)) {
        return 'Reference: 1366 x 420 px / 1920 x 420 px';
    }

    if (in_array($image_key, ['about_showcase', 'home_trust_1', 'home_trust_2'], true)) {
        return 'Max container: 1280 x 560 px';
    }

    if ($image_key === 'gallery_wines') {
        return 'Desktop block: about 411 x 612 px';
    }

    if (strpos($image_key, 'gallery_') === 0) {
        return 'Desktop block: about 411 x 300 px';
    }

    if (strpos($image_key, 'category_') === 0) {
        return 'Desktop card: about 302 x 340 px';
    }

    return 'Depends on viewport';
}

function replace_site_image($file, $target_relative_path) {
    $max_bytes = 12 * 1024 * 1024;

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed. Please choose the image again.');
    }

    if ($file['size'] > $max_bytes) {
        throw new RuntimeException('Image must be 12 MB or smaller.');
    }

    $allowed_mime_types = allowed_mime_for_path($target_relative_path);
    if (!$allowed_mime_types) {
        throw new RuntimeException('This image file type is not supported.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_mime_types, true)) {
        throw new RuntimeException('Please upload the same file type as the current image.');
    }

    if (@getimagesize($file['tmp_name']) === false) {
        throw new RuntimeException('The selected file is not a valid image.');
    }

    $target = image_abs_path($target_relative_path);
    $target_dir = dirname($target);

    if (!is_dir($target_dir)) {
        throw new RuntimeException('Image folder does not exist.');
    }

    if (is_file($target)) {
        if (!is_writable($target)) {
            throw new RuntimeException('Current image file is not writable.');
        }

        if (!copy($file['tmp_name'], $target)) {
            throw new RuntimeException('Could not replace the current image. Please check file permissions.');
        }

        clearstatcache(true, $target);
        return;
    }

    if (!is_writable($target_dir)) {
        throw new RuntimeException('Image folder is not writable.');
    }

    if (!move_uploaded_file($file['tmp_name'], $target)) {
        throw new RuntimeException('Could not save the image. Please check file permissions.');
    }
}

$errors = [];
$flash_success = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_success']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $image_key = $_POST['image_key'] ?? '';

    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid request token. Please reload the page and try again.';
    } elseif (!isset($managed_images[$image_key])) {
        $errors[] = 'Unknown website image selected.';
    } elseif (empty($_FILES['site_image']) || $_FILES['site_image']['error'] === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Please choose an image to upload.';
    } else {
        try {
            replace_site_image($_FILES['site_image'], $managed_images[$image_key]['path']);
            $_SESSION['flash_success'] = $managed_images[$image_key]['label'] . ' was updated successfully.';
            header('Location: /admin/site-images.php');
            exit;
        } catch (RuntimeException $e) {
            $errors[] = $e->getMessage();
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
  <title>Site Images - Admin | Abeywardana Distributors</title>
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
            <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
            <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
          </svg>
          Dashboard
        </a>
        <a href="/admin/add-product.php" class="admin-sidebar__link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Product
        </a>
        <a href="/admin/site-images.php" class="admin-sidebar__link active" aria-current="page">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <circle cx="8.5" cy="10" r="1.5"/>
            <path d="M21 15l-5-5L5 19"/>
          </svg>
          Site Images
        </a>
      </nav>
    </aside>

    <main class="admin-main admin-main--wide" id="main-content">

      <h1 class="admin-page-title">Site Images</h1>
      <p class="admin-page-subtitle">Compare the website slot size with your uploaded image size, then replace fixed website images without changing filenames.</p>

      <?php if ($flash_success !== ''): ?>
        <div class="alert alert-success" role="status"><?php echo htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
          <?php echo htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8'); ?>
        </div>
      <?php endif; ?>

      <div class="site-image-note">
        <strong>How to use this:</strong> design your image for the website slot shown below. After uploading, check the uploaded image size column to confirm the new file dimensions.
      </div>

      <div class="admin-table-wrap site-image-table-wrap">
        <table class="admin-table site-image-table">
          <thead>
            <tr>
              <th>Preview</th>
              <th>Website Image</th>
              <th>Website Slot / Container</th>
              <th>Uploaded Image Size</th>
              <th>Fit Mode</th>
              <th>Design Note</th>
              <th class="site-image-actions-column">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($managed_images as $key => $image): ?>
              <?php
                $meta = image_meta($image['path']);
                $accept = implode(',', allowed_mime_for_path($image['path']));
                $dimensions = ($meta['width'] && $meta['height'])
                    ? $meta['width'] . ' x ' . $meta['height'] . ' px'
                    : 'Unknown';
                $weight = $meta['size'] !== null ? format_bytes($meta['size']) : 'Unknown';
              ?>
              <tr>
                <td>
                  <div class="site-image-thumb">
                    <?php if ($meta['exists']): ?>
                      <img
                        src="/<?php echo htmlspecialchars($image['path'], ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo (int) $meta['mtime']; ?>"
                        alt="<?php echo htmlspecialchars($image['label'], ENT_QUOTES, 'UTF-8'); ?>"
                        loading="lazy"
                      >
                    <?php else: ?>
                      <span>Missing</span>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <strong class="site-image-name"><?php echo htmlspecialchars($image['label'], ENT_QUOTES, 'UTF-8'); ?></strong>
                  <span class="site-image-usage"><?php echo htmlspecialchars($image['usage'], ENT_QUOTES, 'UTF-8'); ?></span>
                  <code class="site-image-file"><?php echo htmlspecialchars(basename($image['path']), ENT_QUOTES, 'UTF-8'); ?></code>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($image['slot'], ENT_QUOTES, 'UTF-8'); ?></strong>
                  <span class="site-image-usage"><?php echo htmlspecialchars(slot_px_note($key), ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                <td>
                  <strong><?php echo htmlspecialchars($dimensions, ENT_QUOTES, 'UTF-8'); ?></strong>
                  <span class="site-image-usage"><?php echo htmlspecialchars($weight, ENT_QUOTES, 'UTF-8'); ?></span>
                </td>
                <td><?php echo htmlspecialchars($image['fit'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo htmlspecialchars($image['guidance'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="site-image-actions-column">
                  <form class="site-image-upload" method="POST" action="/admin/site-images.php" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="image_key" value="<?php echo htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>">
                    <label class="btn btn-outline btn-sm site-image-upload__button">
                      Choose
                      <input type="file" name="site_image" accept="<?php echo htmlspecialchars($accept, ENT_QUOTES, 'UTF-8'); ?>" required>
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                  </form>
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
