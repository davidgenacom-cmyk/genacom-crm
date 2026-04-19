<?php
declare(strict_types=1);

$configPath = dirname(__DIR__) . '/config.php';
if (!is_readable($configPath)) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo "Configuration missing. Copy config.sample.php to config.php and run the database installer.\n";
    exit(1);
}

/** @var array<string,mixed> $CONFIG */
$CONFIG = require $configPath;

$app = $CONFIG['app'] ?? [];
$sessionName = is_string($app['session_name'] ?? null) ? $app['session_name'] : 'genacom_crm_sess';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name($sessionName);
    session_start();
}
