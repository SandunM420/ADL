-- Abeywardana Distributors — Database Schema
--
-- Run this once against a fresh database to create the tables the
-- site and admin portal depend on. See api/config.example.php for
-- connection settings.

CREATE TABLE IF NOT EXISTS products (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  name          VARCHAR(255)  NOT NULL,
  category      VARCHAR(100)  NOT NULL,
  subcategory   VARCHAR(100)  NULL,
  country       VARCHAR(100)  NULL,
  description   TEXT          NOT NULL,
  image         VARCHAR(255)  NOT NULL,
  visible       TINYINT(1)    NOT NULL DEFAULT 1,
  created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
