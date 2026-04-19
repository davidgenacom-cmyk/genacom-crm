#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * CLI: Create database (if needed), apply schema.sql and seed.sql.
 * Usage: php install/setup-database.php
 * Requires config.php at project root (copy from config.sample.php).
 */

$root = dirname(__DIR__);
$configPath = $root . '/config.php';
if (!is_readable($configPath)) {
    fwrite(STDERR, "Missing config.php. Copy config.sample.php to config.php first.\n");
    exit(1);
}

/** @var array<string,mixed> $CONFIG */
$CONFIG = require $configPath;
$db = $CONFIG['db'] ?? [];
$host = (string)($db['host'] ?? '127.0.0.1');
$port = (int)($db['port'] ?? 3306);
$name = (string)($db['name'] ?? 'genacom_crm');
$user = (string)($db['user'] ?? 'root');
$pass = (string)($db['pass'] ?? '');
$charset = (string)($db['charset'] ?? 'utf8mb4');

$schemaFile = $root . '/sql/schema.sql';
$seedFile = $root . '/sql/seed.sql';
foreach ([$schemaFile, $seedFile] as $f) {
    if (!is_readable($f)) {
        fwrite(STDERR, "Missing file: {$f}\n");
        exit(1);
    }
}

/**
 * @return list<string>
 */
function split_sql_statements(string $sql): array
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? '';
    $parts = preg_split('/;\s*\n/', $sql) ?: [];
    $out = [];
    foreach ($parts as $p) {
        $t = trim($p);
        if ($t !== '') {
            $out[] = $t;
        }
    }
    return $out;
}

try {
    $pdoAdmin = new PDO(
        "mysql:host={$host};port={$port};charset={$charset}",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $pdoAdmin->exec(
        'CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '``', $name) . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
    );

    $pdo = new PDO(
        "mysql:host={$host};port={$port};dbname={$name};charset={$charset}",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    $schema = file_get_contents($schemaFile);
    $seed = file_get_contents($seedFile);
    if ($schema === false || $seed === false) {
        throw new RuntimeException('Could not read SQL files.');
    }

    foreach (split_sql_statements($schema) as $stmt) {
        $pdo->exec($stmt);
    }
    foreach (split_sql_statements($seed) as $stmt) {
        $pdo->exec($stmt);
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'Database setup failed: ' . $e->getMessage() . "\n");
    exit(1);
}

echo "OK — database `{$name}` ready. Default login: admin@genacom.com / password (change immediately).\n";
exit(0);
