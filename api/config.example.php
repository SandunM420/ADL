<?php
/**
 * Database Configuration — Template
 *
 * Copy this file to config.php and fill in real credentials for the
 * current environment (local XAMPP, staging, production). config.php
 * is gitignored so real credentials never get committed.
 *
 * SECURITY: This file contains database credentials.
 * Direct HTTP access is blocked via api/.htaccess.
 * For production, consider moving this file above public_html and
 * referencing it with an absolute server path.
 */

define('DB_HOST',    'localhost');
define('DB_NAME',    'your_database_name');
define('DB_USER',    'your_db_username');
define('DB_PASS',    'your_db_password');
define('DB_CHARSET', 'utf8mb4');

/**
 * Admin portal credentials.
 *
 * Single fixed admin account — no admins table needed. The password is
 * stored as a password_hash() hash and checked with password_verify()
 * in admin/index.php, never compared as plain text.
 *
 * Generate a new hash with: php -r "echo password_hash('your-password', PASSWORD_DEFAULT);"
 */
define('ADMIN_USERNAME',      'your_admin_username');
define('ADMIN_PASSWORD_HASH', 'paste-a-password_hash-value-here');
