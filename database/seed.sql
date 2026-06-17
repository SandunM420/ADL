-- Abeywardana Distributors - Demo Product Seed Data
--
-- Import this after database/schema.sql to populate a fresh local database
-- with a shared starter catalogue. This file is intended for development
-- and demo environments.
--
-- WARNING: This resets the products table before inserting the seed rows.

SET NAMES utf8mb4;

START TRANSACTION;

DELETE FROM products;
ALTER TABLE products AUTO_INCREMENT = 1;

INSERT INTO products
  (id, name, category, subcategory, country, description, image, visible, created_at)
VALUES
  (
    1,
    'Diemersfontein The Original Pinotage',
    'wines',
    'south-africa',
    'South Africa',
    'A rich South African Pinotage with generous fruit character and a smooth finish, selected for premium hospitality and retail partners.',
    'assets/images/products/product_6a31aed23788f6.10859046.jpg',
    1,
    '2026-06-17 01:45:14'
  ),
  (
    2,
    'Diemersdal The Journal Pinotage',
    'wines',
    'south-africa',
    'South Africa',
    'A refined Pinotage from South Africa with layered dark fruit notes, crafted for memorable dining and gifting occasions.',
    'assets/images/products/product_6a31b73e795482.31320824.jpg',
    1,
    '2026-06-17 02:21:10'
  ),
  (
    3,
    'Casillero del Diablo Cabernet Sauvignon',
    'wines',
    'chile',
    'Chile',
    'A classic Chilean Cabernet Sauvignon with bold structure, ripe fruit and a dependable profile for wine lists and retail shelves.',
    'assets/images/products/sample_wine_seed.png',
    1,
    '2026-06-17 01:55:43'
  ),
  (
    4,
    'Moet Chandon Imperial',
    'champagne',
    'france',
    'France',
    'An iconic French champagne known for elegance, freshness and celebration-ready appeal.',
    'assets/images/products/sample_champagne_seed.png',
    1,
    '2026-06-17 01:55:43'
  );

COMMIT;
