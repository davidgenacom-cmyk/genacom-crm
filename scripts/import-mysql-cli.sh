#!/usr/bin/env bash
# Import schema + seed using the mysql client (alternative to php install/setup-database.php).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
read -r -p "MySQL user [root]: " DBUSER
DBUSER="${DBUSER:-root}"
read -r -s -p "MySQL password: " DBPASS
echo
mysql -h 127.0.0.1 -u "$DBUSER" -p"$DBPASS" -e "CREATE DATABASE IF NOT EXISTS genacom_crm CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -h 127.0.0.1 -u "$DBUSER" -p"$DBPASS" genacom_crm < "$ROOT/sql/schema.sql"
mysql -h 127.0.0.1 -u "$DBUSER" -p"$DBPASS" genacom_crm < "$ROOT/sql/seed.sql"
echo "Imported schema and seed into database genacom_crm."
