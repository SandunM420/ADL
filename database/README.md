# Database Setup

Use these files when another developer pulls the project and needs the same starter catalogue.

## Fresh setup

1. Create a MySQL database.
2. Copy `api/config.example.php` to `api/config.php`.
3. Fill `api/config.php` with local DB credentials and an admin password hash.
4. Import the schema:

```sql
SOURCE database/schema.sql;
```

5. Import the shared demo data:

```sql
SOURCE database/seed.sql;
```

The seed file resets the `products` table and inserts the shared starter products.

## Sharing product changes

Git does not store live MySQL rows automatically. If someone changes products in their local admin portal and the team should share those products, export the `products` table and update `database/seed.sql`.

With MySQL tools installed, the export can be done like this:

```bash
mysqldump -u DB_USER -p DB_NAME products --no-create-info --complete-insert > database/seed-products-export.sql
```

Then review the export, remove secrets if any are present, and convert or replace `database/seed.sql` with the updated product rows.

For live shared data, point each environment at the same staging or production MySQL database using its own uncommitted `api/config.php`.
