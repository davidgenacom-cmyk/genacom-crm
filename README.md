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

3. Serve the app from this directory.

   If your shell says `command not found: php`, Homebrew PHP is usually at `/opt/homebrew/bin/php`. Either add Homebrew to your PATH (`eval "$(/opt/homebrew/bin/brew shellenv)"` in `~/.zprofile`) or run:

   ```bash
   ./serve.sh 8080
   ```

   Manual equivalent:

   ```bash
   /opt/homebrew/bin/php -S localhost:8080 -t /Users/genacom13/Projects/genacom-crm
   ```

   Open `http://localhost:8080/login.php`.

   **Login requires MySQL running.** If you see “Cannot connect to database”, see **Database troubleshooting** below.

## Database troubleshooting (“Cannot connect to database”)

That message means PHP cannot open a TCP connection to MySQL (wrong host/port, server stopped, or wrong user/password).

### Option A — Homebrew MySQL (native on Mac)

Install and start the service:

```bash
brew install mysql
brew services start mysql
```

Wait a few seconds, then from the project folder:

```bash
cp config.sample.php config.php   # if you do not have config.php yet
/opt/homebrew/bin/php install/setup-database.php
```

If your `root` user has a password, put it in `config.php` under `db.pass`. On some installs you must connect as your macOS user first; adjust `db.user` / `db.pass` to match.

### Option B — Docker (no local MySQL install)

From the project folder:

```bash
docker compose up -d
cp config.docker.sample.php config.php
/opt/homebrew/bin/php install/setup-database.php
```

MySQL listens on `127.0.0.1:3306` with `root` / `genacomlocal` (see `docker-compose.yml`). Stop later with `docker compose down`.

### Option C — MAMP / hosting

Point `config.php` at the host, port, and credentials your panel shows (often a non-`root` user and a specific database name). Run `install/setup-database.php` once against that server.

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
