<?php
/**
 * Use with Docker MySQL from docker-compose.yml in this repo.
 *   cp config.docker.sample.php config.php
 *   docker compose up -d
 *   php install/setup-database.php
 */
declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'genacom_crm',
        'user' => 'root',
        'pass' => 'genacomlocal',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Genacom CRM',
        'base_url' => '',
        'session_name' => 'genacom_crm_sess',
    ],
];
