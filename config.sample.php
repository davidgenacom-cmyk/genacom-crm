<?php
/**
 * Copy this file to config.php and edit credentials.
 * chmod 600 config.php on production servers.
 */
declare(strict_types=1);

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'genacom_crm',
        'user' => 'root',
        'pass' => '',
        'charset' => 'utf8mb4',
    ],
    'app' => [
        'name' => 'Genacom CRM',
        'base_url' => '', // e.g. https://crm.example.com — leave empty for auto-detect
        'session_name' => 'genacom_crm_sess',
    ],
];
