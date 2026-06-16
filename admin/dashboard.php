<?php
/**
 * Admin: Dashboard
 *
 * Protected page — redirects to login if no valid admin session.
 * Lists every product in the database (visible and hidden) with its
 * image, category, country, and visibility status.
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

$products  = [];
$db_error  = '';

try {
    $pdo  = get_db_connection();
    $stmt = $pdo->query(
        'SELECT id, name, category, subcategory, country, image, visible, created_at
           FROM products
          ORDER BY created_at DESC'
    );
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log('dashboard.php — PDO error: ' . $e->getMessage());
    $db_error = 'Could not load products. Please check the database connection.';
}

// One-time flash messages set by add-product.php / edit-product.php / delete-product.php
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error    = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Generate CSRF token once per session (used by the delete form)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$admin_username = htmlspecialchars($_SESSION['admin_username'] ?? 'Admin', ENT_QUOTES, 'UTF-8');

/**
 * Format a category/subcategory slug as a readable label.
 *
 * @param  string|null $slug
 * @return string
 */
function format_slug_label($slug) {
    if ($slug === null || $slug === '') {
        return '—';
    }
    return str_replace('-', ' ', ucwords($slug, '-'));
}

/**
 * Collect the distinct, non-empty values of a product field for use as
 * filter dropdown options, sorted alphabetically.
 *
 * @param  array  $products
 * @param  string $field
 * @return array
 */
function distinct_filter_values($products, $field) {
    $values = [];
    foreach ($products as $product) {
        if (!empty($product[$field])) {
            $values[$product[$field]] = true;
        }
    }
    $values = array_keys($values);
    sort($values);
    return $values;
}

$filter_categories    = distinct_filter_values($products, 'category');
$filter_subcategories = distinct_filter_values($products, 'subcategory');
$filter_countries     = distinct_filter_values($products, 'country');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard — Admin | Abeywardana Distributors</title>
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
      <img src="/assets/images/logo.png" alt="Abeywardana Distributors">
    </span>
    <span class="admin-topbar__badge">Admin Portal</span>
    <div class="admin-topbar__spacer"></div>
    <span class="admin-topbar__user"><?php echo $admin_username; ?></span>
    <a href="/admin/logout.php" class="admin-topbar__logout">Log out</a>
  </header>

  <div class="admin-body">

    <aside class="admin-sidebar">
      <nav class="admin-sidebar__nav" aria-label="Admin navigation">
        <a href="/admin/dashboard.php" class="admin-sidebar__link active" aria-current="page">
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
      </nav>
    </aside>

    <main class="admin-main admin-main--wide" id="main-content">

      <h1 class="admin-page-title">Products</h1>
      <p class="admin-page-subtitle"><?php echo count($products); ?> product<?php echo count($products) === 1 ? '' : 's'; ?> in the catalogue.</p>

      <?php if ($flash_success !== ''): ?>
        <div class="alert alert-success" role="status"><?php echo htmlspecialchars($flash_success, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if ($flash_error !== ''): ?>
        <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($flash_error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if ($db_error !== ''): ?>
        <div class="alert alert-error" role="alert"><?php echo htmlspecialchars($db_error, ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endif; ?>

      <?php if (empty($products) && $db_error === ''): ?>
        <p class="admin-empty">No products yet. <a href="/admin/add-product.php">Add your first product.</a></p>
      <?php elseif (!empty($products)): ?>
        <div class="admin-filter-bar">
          <div class="form-group">
            <label class="form-label" for="filter-category">Category</label>
            <select id="filter-category" class="form-control admin-filter">
              <option value="">All categories</option>
              <?php foreach ($filter_categories as $category): ?>
                <option value="<?php echo htmlspecialchars($category, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars(format_slug_label($category), ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="filter-subcategory">Subcategory</label>
            <select id="filter-subcategory" class="form-control admin-filter">
              <option value="">All subcategories</option>
              <?php foreach ($filter_subcategories as $subcategory): ?>
                <option value="<?php echo htmlspecialchars($subcategory, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars(format_slug_label($subcategory), ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="filter-country">Country</label>
            <select id="filter-country" class="form-control admin-filter">
              <option value="">All countries</option>
              <?php foreach ($filter_countries as $country): ?>
                <option value="<?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>">
                  <?php echo htmlspecialchars($country, ENT_QUOTES, 'UTF-8'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="filter-status">Status</label>
            <select id="filter-status" class="form-control admin-filter">
              <option value="">All statuses</option>
              <option value="1">Visible</option>
              <option value="0">Hidden</option>
            </select>
          </div>

          <button type="button" id="filter-reset" class="btn btn-ghost btn-sm">Reset filters</button>
        </div>

        <p class="admin-empty" id="filter-empty" hidden>No products match the selected filters.</p>

        <div class="admin-table-wrap">
          <table class="admin-table" id="products-table">
            <thead>
              <tr>
                <th>Image</th>
                <th>Name</th>
                <th>Category</th>
                <th>Subcategory</th>
                <th>Country</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($products as $product): ?>
                <tr
                  data-category="<?php echo htmlspecialchars($product['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                  data-subcategory="<?php echo htmlspecialchars($product['subcategory'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                  data-country="<?php echo htmlspecialchars($product['country'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                  data-visible="<?php echo (int) $product['visible']; ?>"
                >
                  <td>
                    <img
                      class="admin-table__thumb"
                      src="/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
                      alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                      loading="lazy"
                    >
                  </td>
                  <td><?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars(format_slug_label($product['category']), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars(format_slug_label($product['subcategory']), ENT_QUOTES, 'UTF-8'); ?></td>
                  <td><?php echo htmlspecialchars($product['country'] ?: '—', ENT_QUOTES, 'UTF-8'); ?></td>
                  <td>
                    <?php if ((int) $product['visible'] === 1): ?>
                      <span class="status-badge status-badge--visible">Visible</span>
                    <?php else: ?>
                      <span class="status-badge status-badge--hidden">Hidden</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="admin-table__actions">
                      <a class="btn btn-ghost btn-sm" href="/admin/edit-product.php?id=<?php echo (int) $product['id']; ?>">Edit</a>
                      <form
                        method="POST"
                        action="/admin/delete-product.php"
                        class="delete-form"
                        data-product-name="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
                      >
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?php echo (int) $product['id']; ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </main>
  </div>
</div>

<div class="modal-overlay" id="delete-modal" hidden>
  <div class="modal-dialog" role="alertdialog" aria-modal="true" aria-labelledby="delete-modal-title" aria-describedby="delete-modal-message">
    <h2 class="modal-dialog__title" id="delete-modal-title">Delete product?</h2>
    <p class="modal-dialog__message" id="delete-modal-message"></p>
    <div class="modal-dialog__actions">
      <button type="button" class="btn btn-ghost" id="delete-modal-cancel">Cancel</button>
      <button type="button" class="btn btn-danger" id="delete-modal-confirm">Delete</button>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  var table = document.getElementById('products-table');
  if (!table) {
    return;
  }

  var rows         = Array.prototype.slice.call(table.querySelectorAll('tbody tr'));
  var emptyMessage = document.getElementById('filter-empty');

  var filters = {
    category:    document.getElementById('filter-category'),
    subcategory: document.getElementById('filter-subcategory'),
    country:     document.getElementById('filter-country'),
    status:      document.getElementById('filter-status'),
  };

  function applyFilters() {
    var categoryValue    = filters.category.value;
    var subcategoryValue = filters.subcategory.value;
    var countryValue     = filters.country.value;
    var statusValue      = filters.status.value;
    var visibleCount     = 0;

    rows.forEach(function (row) {
      var matches =
        (categoryValue    === '' || row.dataset.category    === categoryValue) &&
        (subcategoryValue === '' || row.dataset.subcategory === subcategoryValue) &&
        (countryValue     === '' || row.dataset.country     === countryValue) &&
        (statusValue      === '' || row.dataset.visible     === statusValue);

      row.hidden = !matches;
      if (matches) {
        visibleCount++;
      }
    });

    emptyMessage.hidden = visibleCount !== 0;
  }

  Object.keys(filters).forEach(function (key) {
    filters[key].addEventListener('change', applyFilters);
  });

  document.getElementById('filter-reset').addEventListener('click', function () {
    Object.keys(filters).forEach(function (key) {
      filters[key].value = '';
    });
    applyFilters();
  });
}());

(function () {
  'use strict';

  var modal       = document.getElementById('delete-modal');
  var message     = document.getElementById('delete-modal-message');
  var cancelBtn   = document.getElementById('delete-modal-cancel');
  var confirmBtn  = document.getElementById('delete-modal-confirm');
  var pendingForm = null;

  if (!modal) {
    return;
  }

  function openModal(form) {
    pendingForm = form;
    message.textContent = 'Delete "' + form.dataset.productName + '"? This cannot be undone.';
    modal.hidden = false;
    confirmBtn.focus();
  }

  function closeModal() {
    modal.hidden = true;
    pendingForm  = null;
  }

  Array.prototype.forEach.call(document.querySelectorAll('.delete-form'), function (form) {
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      openModal(form);
    });
  });

  confirmBtn.addEventListener('click', function () {
    if (pendingForm) {
      pendingForm.submit();
    }
  });

  cancelBtn.addEventListener('click', closeModal);

  modal.addEventListener('click', function (event) {
    if (event.target === modal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape' && !modal.hidden) {
      closeModal();
    }
  });
}());
</script>

</body>
</html>
