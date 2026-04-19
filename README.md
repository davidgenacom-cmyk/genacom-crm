# Genacom CRM

Lightweight PHP + MySQL CRM branded for **Genacom** using the **Genacom M38** design language: light surfaces, Fraunces + DM Sans typography, navy accent (`#0a1628` / `#0a4a7a`), Genacom favicon and logo from the official CDN, and footer contact aligned with outreach standards (`david@genacom.com`).

## Requirements

- PHP 8.1+ with PDO MySQL
- MySQL 8+ or MariaDB 10.3+
- Web server (Apache with `mod_rewrite` optional, or nginx / PHP built-in server for demos)

## Quick setup

1. Copy configuration:

   ```bash
   cp config.sample.php config.php
   ```

   Edit `config.php` with your MySQL host, database name, user, and password.

2. Create tables and seed data (PHP CLI):

   ```bash
   php install/setup-database.php
   ```

   This creates the `genacom_crm` database if missing, runs `sql/schema.sql`, then `sql/seed.sql`.

   **Alternative:** use the MySQL client:

   ```bash
   chmod +x scripts/import-mysql-cli.sh
   ./scripts/import-mysql-cli.sh
   ```

   Or run `sql/schema.sql` and `sql/seed.sql` manually in your SQL admin tool after creating database `genacom_crm`.

3. Serve the app from this directory (example):

   ```bash
   php -S localhost:8080 -t .
   ```

   Open `http://localhost:8080/login.php`.

## Default credentials

After seeding:

- **Email:** `admin@genacom.com`
- **Password:** `password`

Change this password in production (update the `users` table with a new `password_hash` from `password_hash('your-secret', PASSWORD_DEFAULT)` in PHP).

## Features

- Session-based authentication
- **Companies** — accounts with location and notes
- **Contacts** — people with lifecycle status and company link
- **Deals** — stages, value, expected close date
- **Activities** — calls, emails, meetings, notes, tasks with optional due date

## Security notes

- Keep `config.php` out of version control (see `.gitignore`).
- Use HTTPS in production.
- Restrict file permissions on the server (`chmod 600 config.php`).
- Delete or protect `install/` after first deploy if exposed to the web.

## Genacom M38 alignment

- Light theme, hairline borders, accessible contrast
- Flat single-color UI chrome (no multi-color icons in navigation)
- Preview URL pattern for hosted Genacom builds remains documented in the upstream skill: `https://<DOMAIN_SLUG>.genacom.cloud/`

## License

Proprietary / internal Genacom use unless otherwise specified.
