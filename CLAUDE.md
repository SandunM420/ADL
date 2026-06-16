# Abeywardana Distributors — Project Context

## What We Are Building
A premium luxury light themed website for Abeywardana 
Distributors (Pvt) Ltd — a wine, spirits and beverage 
importer and distributor in Sri Lanka.

## Tech Stack
- Frontend: HTML5, CSS3, Vanilla JavaScript (no frameworks)
- Backend: PHP
- Database: MySQL
- Hosting: Namecheap Shared Hosting
- No build tools, no npm, no React

## Design System
- Theme: Luxury Light
- Background: #FAFAF7
- Surface: #FFFFFF
- Surface 2: #F5F2EC
- Border: #E8E2D9
- Gold Accent: #C9A84C
- Gold Dark: #A8862E
- Text Primary: #1A1714
- Text Secondary: #6B6560
- Text Muted: #A09890
- Font: Inter (Google Fonts)
- Spacing base unit: 8px

## Navigation Structure
Home | Wines ▾ | Champagne ▾ | Sparkling Wine | Spirits ▾ | About Us | Contact Us

Wines dropdown: Chile, Australia, South Africa, Spain
Champagne dropdown: France
Sparkling Wine: Single page, no dropdown
Spirits dropdown: Whiskey, Rum, Gin, Vodka, Brandy, Liquor

## Product Filtering Logic
- Category + Subcategory = determines which page a product appears on
- Country field = informational only (shown on product detail page)
- Sparkling Wine has NO subcategory (subcategory = NULL)
- Products load dynamically via JS fetching from api/products.php

## Database Table — products
- id (INT, PK, auto-increment)
- name (VARCHAR 255)
- category (VARCHAR 100) — wines/champagne/sparkling-wine/spirits
- subcategory (VARCHAR 100) — chile/australia/south-africa/spain/
                               france/whiskey/rum/gin/vodka/brandy/liquor/NULL
- country (VARCHAR 100) — optional, informational only
- description (TEXT)
- image (VARCHAR 255) — file path
- visible (TINYINT 1) — 1=show, 0=hide
- created_at (TIMESTAMP)

## File Structure
public_html/
├── index.html
├── about.html
├── contact.html
├── assets/css/ assets/js/ assets/images/products/
├── wines/ chile.html australia.html south-africa.html spain.html
├── wines/products/product.html (reusable template)
├── champagne/ france.html
├── champagne/products/product.html
├── sparkling-wine/ index.html
├── sparkling-wine/products/product.html
├── spirits/ whiskey.html rum.html gin.html vodka.html brandy.html liquor.html
├── spirits/products/product.html
├── api/ products.php config.php
└── admin/ index.php dashboard.php add-product.php 
          edit-product.php delete-product.php logout.php

## Admin Portal Rules
- URL: /admin
- Auth: PHP Sessions
- Passwords: password_hash()
- Cascading dropdown: subcategory options change based on category
- Sparkling Wine hides subcategory field entirely

## Coding Rules
- Always use consistent CSS variable names
- Mobile first responsive design
- No inline styles — all styles in CSS files
- Comments on every PHP function
- Prepared statements for all MySQL queries (security)
- Never expose DB credentials in public files
