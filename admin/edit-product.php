<?php
/**
 * Admin: Edit Product
 *
 * Protected page â€” redirects to login if no valid admin session.
 * Loads an existing product by id, renders a pre-filled form, and
 * updates the record on submission. The image is only replaced if a
 * new file is uploaded; otherwise the existing image is kept.
 */

session_start();

if (empty($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /admin/index.php');
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

/**
 * Validate, move, and return the relative path of an uploaded product image.
 * Checks MIME type via finfo (not file extension).
 *
 * @param  array  $file  Single entry from $_FILES (e.g. $_FILES['image'])
 * @return string        Relative path suitable for storing in the database
 * @throws RuntimeException on validation or filesystem failure
 */
function upload_product_image($file) {
    $allowed_mime_types = ['image/jpeg', 'image/png', 'image/webp'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error'] . '.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime, $allowed_mime_types, true)) {
        throw new RuntimeException('Unsupported image type. Please upload a JPEG, PNG, or WebP file.');
    }

    $ext_map = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
    ];
    $ext        = $ext_map[$mime];
    $filename   = uniqid('product_', true) . '.' . $ext;
    $upload_dir = __DIR__ . '/../assets/images/products/';
    $dest       = $upload_dir . $filename;

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Could not save uploaded image. Check server write permissions.');
    }

    return 'assets/images/products/' . $filename;
}

/* â”€â”€â”€ Valid options (used for server-side validation) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

$valid_categories    = ['wines', 'champagne', 'sparkling-wine', 'spirits'];
$valid_subcategories = [
    'chile', 'australia', 'south-africa', 'spain',
    'france',
    'whiskey', 'rum', 'gin', 'vodka', 'brandy', 'liquor',
];

/* â”€â”€â”€ Load the product â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/dashboard.php');
    exit;
}

try {
    $pdo  = get_db_connection();
    $stmt = $pdo->prepare(
        'SELECT id, name, item_code, grape_type, alcohol, pack_size, category, subcategory, country, description, image, visible
           FROM products
          WHERE id = ?'
    );
    $stmt->execute([$id]);
    $product = $stmt->fetch();
} catch (PDOException $e) {
    error_log('edit-product.php â€” PDO error: ' . $e->getMessage());
    $product = false;
}

if (!$product) {
    header('Location: /admin/dashboard.php');
    exit;
}

/* â”€â”€â”€ State â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

$errors      = [];
$form_values = [
    'name'        => $product['name'],
    'item_code'   => $product['item_code'] ?? '',
    'grape_type'  => $product['grape_type'] ?? '',
    'alcohol'     => $product['alcohol'] ?? '',
    'pack_size'   => $product['pack_size'] ?? '',
    'category'    => $product['category'],
    'subcategory' => $product['subcategory'] ?? '',
    'country'     => $product['country'] ?? '',
    'description' => $product['description'],
    'visible'     => (string) (int) $product['visible'],
];
$current_image = $product['image'];

/* â”€â”€â”€ Handle POST â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (
        empty($_POST['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])
    ) {
        $errors[] = 'Invalid request token. Please reload the page and try again.';
    } else {

        // Read inputs
        $form_values['name']        = trim($_POST['name']        ?? '');
        $form_values['item_code']   = trim($_POST['item_code']   ?? '');
        $form_values['grape_type']  = trim($_POST['grape_type']  ?? '');
        $form_values['alcohol']     = trim($_POST['alcohol']     ?? '');
        $form_values['pack_size']   = trim($_POST['pack_size']   ?? '');
        $form_values['category']    = trim($_POST['category']    ?? '');
        $form_values['subcategory'] = trim($_POST['subcategory'] ?? '');
        $form_values['country']     = trim($_POST['country']     ?? '');
        $form_values['description'] = trim($_POST['description'] ?? '');
        $form_values['visible']     = isset($_POST['visible']) ? '1' : '0';

        // Validate
        if ($form_values['name'] === '') {
            $errors[] = 'Product name is required.';
        }

        if (!in_array($form_values['category'], $valid_categories, true)) {
            $errors[] = 'Please select a valid category.';
        }

        if ($form_values['category'] !== 'sparkling-wine') {
            if (
                $form_values['subcategory'] === '' ||
                !in_array($form_values['subcategory'], $valid_subcategories, true)
            ) {
                $errors[] = 'Please select a valid subcategory for the chosen category.';
            }
        } else {
            $form_values['subcategory'] = null; // sparkling-wine has no subcategory
        }

        if ($form_values['description'] === '') {
            $errors[] = 'Tasting note is required.';
        }

        // Handle optional image replacement
        $image_path = $current_image;
        $has_file   = !empty($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE;

        if ($has_file) {
            try {
                $image_path = upload_product_image($_FILES['image']);
            } catch (RuntimeException $e) {
                $errors[] = $e->getMessage();
            }
        }

        // Update if no errors
        if (empty($errors)) {
            try {
                $pdo  = get_db_connection();
                $stmt = $pdo->prepare(
                    'UPDATE products
                        SET name = ?, item_code = ?, grape_type = ?, alcohol = ?, pack_size = ?, category = ?, subcategory = ?, country = ?,
                            description = ?, image = ?, visible = ?
                      WHERE id = ?'
                );
                $stmt->execute([
                    $form_values['name'],
                    $form_values['item_code'] !== '' ? $form_values['item_code'] : null,
                    $form_values['grape_type'] !== '' ? $form_values['grape_type'] : null,
                    $form_values['alcohol'] !== '' ? $form_values['alcohol'] : null,
                    $form_values['pack_size'] !== '' ? $form_values['pack_size'] : null,
                    $form_values['category'],
                    $form_values['subcategory'],
                    $form_values['country'] !== '' ? $form_values['country'] : null,
                    $form_values['description'],
                    $image_path,
                    (int) $form_values['visible'],
                    $id,
                ]);

                // Remove the old image file if it was replaced
                if ($image_path !== $current_image) {
                    $old_file = __DIR__ . '/../' . $current_image;
                    if (is_file($old_file)) {
                        unlink($old_file);
                    }
                }

                $_SESSION['flash_success'] = '"' . $form_values['name'] . '" was updated successfully.';
                header('Location: /admin/dashboard.php');
                exit;

            } catch (PDOException $e) {
                error_log('edit-product.php â€” PDO error: ' . $e->getMessage());
                $errors[] = 'A database error occurred. Please try again.';
            }
        }
    }
}

// Generate CSRF token once per session
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Product â€” Admin | Abeywardana Distributors</title>
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
        <a href="/admin/site-images.php" class="admin-sidebar__link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <circle cx="8.5" cy="10" r="1.5"/>
            <path d="M21 15l-5-5L5 19"/>
          </svg>
          Site Images
        </a>
        <a href="/admin/best-sellers.php" class="admin-sidebar__link">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
          Best Sellers
        </a>
      </nav>
    </aside>

    <main class="admin-main" id="main-content">

      <h1 class="admin-page-title">Edit Product</h1>
      <p class="admin-page-subtitle">Update the fields below and save your changes.</p>

      <?php if (!empty($errors)): ?>
        <div class="alert alert-error" role="alert">
          <?php if (count($errors) === 1): ?>
            <?php echo htmlspecialchars($errors[0], ENT_QUOTES, 'UTF-8'); ?>
          <?php else: ?>
            <ul>
              <?php foreach ($errors as $error): ?>
                <li><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <form
        class="admin-form"
        method="POST"
        action="/admin/edit-product.php?id=<?php echo (int) $id; ?>"
        enctype="multipart/form-data"
        novalidate
      >
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

        <!-- Product Name -->
        <div class="form-group">
          <label class="form-label" for="name">
            Product Name <span class="required" aria-label="required">*</span>
          </label>
          <input
            type="text"
            id="name"
            name="name"
            class="form-control"
            value="<?php echo htmlspecialchars($form_values['name'], ENT_QUOTES, 'UTF-8'); ?>"
            placeholder="e.g. Concha y Toro Casillero del Diablo Reserva"
            required
            maxlength="255"
            autocomplete="off"
          >
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="grape_type">Grape Type</label>
            <input
              type="text"
              id="grape_type"
              name="grape_type"
              class="form-control"
              value="<?php echo htmlspecialchars($form_values['grape_type'], ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="e.g. Chardonnay, Premium"
              maxlength="150"
              autocomplete="off"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="country">Origin</label>
            <input
              type="text"
              id="country"
              name="country"
              class="form-control"
              value="<?php echo htmlspecialchars($form_values['country'], ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="e.g. Chile, Scotland, France"
              maxlength="100"
              autocomplete="off"
            >
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="alcohol">Alcohol</label>
            <input
              type="text"
              id="alcohol"
              name="alcohol"
              class="form-control"
              value="<?php echo htmlspecialchars($form_values['alcohol'], ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="e.g. 13.5%"
              maxlength="100"
              autocomplete="off"
            >
          </div>

          <div class="form-group">
            <label class="form-label" for="pack_size">Pack Size</label>
            <input
              type="text"
              id="pack_size"
              name="pack_size"
              class="form-control"
              value="<?php echo htmlspecialchars($form_values['pack_size'], ENT_QUOTES, 'UTF-8'); ?>"
              placeholder="e.g. 750ml bottle, 12 bottles per case"
              maxlength="100"
              autocomplete="off"
            >
          </div>
        </div>

        <!-- Category & Subcategory -->
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="category">
              Category <span class="required" aria-label="required">*</span>
            </label>
            <select id="category" name="category" class="form-control" required>
              <option value="">â€” Select category â€”</option>
              <option value="wines"
                <?php echo $form_values['category'] === 'wines' ? 'selected' : ''; ?>>
                Wines
              </option>
              <option value="champagne"
                <?php echo $form_values['category'] === 'champagne' ? 'selected' : ''; ?>>
                Champagne
              </option>
              <option value="sparkling-wine"
                <?php echo $form_values['category'] === 'sparkling-wine' ? 'selected' : ''; ?>>
                Sparkling Wine
              </option>
              <option value="spirits"
                <?php echo $form_values['category'] === 'spirits' ? 'selected' : ''; ?>>
                Spirits
              </option>
            </select>
          </div>

          <div class="form-group" id="subcategory-group">
            <label class="form-label" for="subcategory">
              Subcategory <span class="required" aria-label="required" id="subcategory-req">*</span>
            </label>
            <select id="subcategory" name="subcategory" class="form-control">
              <option value="">â€” Select category first â€”</option>
            </select>
          </div>
        </div>

        <!-- Tasting Note -->
        <div class="form-group">
          <label class="form-label" for="description">
            Tasting Note <span class="required" aria-label="required">*</span>
          </label>
          <textarea
            id="description"
            name="description"
            class="form-control"
            rows="5"
            placeholder="Enter tasting notes..."
            required
          ><?php echo htmlspecialchars($form_values['description'], ENT_QUOTES, 'UTF-8'); ?></textarea>
        </div>

        <!-- Image Upload -->
        <div class="form-group">
          <label class="form-label">Product Image</label>
          <div class="image-preview" id="current-image-preview">
            <img src="/<?php echo htmlspecialchars($current_image, ENT_QUOTES, 'UTF-8'); ?>" alt="Current product image">
          </div>
          <div class="image-upload" id="image-drop-zone">
            <label class="image-upload__label" for="image">
              <svg class="image-upload__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                <circle cx="8.5" cy="8.5" r="1.5"/>
                <polyline points="21 15 16 10 5 21"/>
              </svg>
              <span class="image-upload__text">Click to replace image</span>
              <span class="image-upload__hint">JPEG, PNG, or WebP. Leave empty to keep the current image.</span>
            </label>
            <input
              type="file"
              id="image"
              name="image"
              accept="image/jpeg,image/png,image/webp"
            >
          </div>
          <div class="image-preview" id="image-preview" hidden aria-live="polite"></div>
        </div>

        <!-- Visibility -->
        <div class="form-group">
          <label class="form-label">Visibility</label>
          <div class="toggle-group">
            <label class="toggle">
              <input
                type="checkbox"
                id="visible"
                name="visible"
                value="1"
                <?php echo $form_values['visible'] === '1' ? 'checked' : ''; ?>
                aria-describedby="visible-label"
              >
              <span class="toggle__slider"></span>
            </label>
            <span class="toggle__label" id="visible-label">
              <?php echo $form_values['visible'] === '1' ? 'Visible on website' : 'Hidden from website'; ?>
            </span>
          </div>
        </div>

        <!-- Actions -->
        <div class="form-actions">
          <button type="submit" class="btn btn-primary">Save Changes</button>
          <a href="/admin/dashboard.php" class="btn btn-ghost">Cancel</a>
        </div>

      </form>

    </main>
  </div>
</div>

<script>
(function () {
  'use strict';

  /* â”€â”€ Cascading subcategory dropdown â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

  var SUBCATEGORY_MAP = {
    wines: [
      { value: 'chile',        label: 'Chile' },
      { value: 'australia',    label: 'Australia' },
      { value: 'south-africa', label: 'South Africa' },
      { value: 'spain',        label: 'Spain' },
    ],
    champagne: [
      { value: 'france', label: 'France' },
    ],
    'sparkling-wine': [],
    spirits: [
      { value: 'whiskey', label: 'Whiskey' },
      { value: 'rum',     label: 'Rum' },
      { value: 'gin',     label: 'Gin' },
      { value: 'vodka',   label: 'Vodka' },
      { value: 'brandy',  label: 'Brandy' },
      { value: 'liquor',  label: 'Liquor' },
    ],
  };

  // Preserved value on initial load (current product) and after a validation error
  var savedSubcategory = <?php echo json_encode($form_values['subcategory'] ?? ''); ?>;

  var categoryEl        = document.getElementById('category');
  var subcategoryEl     = document.getElementById('subcategory');
  var subcategoryGroup  = document.getElementById('subcategory-group');

  function updateSubcategoryOptions(category) {
    var options = SUBCATEGORY_MAP[category] || [];

    if (category === 'sparkling-wine') {
      subcategoryGroup.style.display = 'none';
      subcategoryEl.removeAttribute('required');
      subcategoryEl.value = '';
      return;
    }

    subcategoryGroup.style.display = '';
    subcategoryEl.setAttribute('required', '');
    subcategoryEl.innerHTML = '';

    var placeholder = document.createElement('option');
    placeholder.value       = '';
    placeholder.textContent = 'â€” Select subcategory â€”';
    subcategoryEl.appendChild(placeholder);

    for (var i = 0; i < options.length; i++) {
      var opt       = document.createElement('option');
      opt.value     = options[i].value;
      opt.textContent = options[i].label;
      if (options[i].value === savedSubcategory) {
        opt.selected = true;
      }
      subcategoryEl.appendChild(opt);
    }
  }

  if (categoryEl.value) {
    updateSubcategoryOptions(categoryEl.value);
  }

  categoryEl.addEventListener('change', function () {
    savedSubcategory = '';
    updateSubcategoryOptions(this.value);
  });

  /* â”€â”€ New image preview â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

  var imageInput   = document.getElementById('image');
  var imagePreview = document.getElementById('image-preview');

  imageInput.addEventListener('change', function () {
    var file = this.files[0];
    if (!file) {
      imagePreview.hidden = true;
      imagePreview.innerHTML = '';
      return;
    }
    var reader = new FileReader();
    reader.onload = function (e) {
      imagePreview.hidden = false;
      imagePreview.innerHTML = '<img src="' + e.target.result + '" alt="Selected image preview">';
    };
    reader.readAsDataURL(file);
  });

  /* â”€â”€ Visibility toggle label â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */

  var visibleInput = document.getElementById('visible');
  var visibleLabel = document.getElementById('visible-label');

  visibleInput.addEventListener('change', function () {
    visibleLabel.textContent = this.checked ? 'Visible on website' : 'Hidden from website';
  });
}());
</script>

</body>
</html>
