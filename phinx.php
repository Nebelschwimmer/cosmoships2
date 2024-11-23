<?php

use Symfony\Component\Dotenv\Dotenv;

$dotenv = new Dotenv();
$dotenv->load(__DIR__.'/.env');

if (file_exists(__DIR__ . DIRECTORY_SEPARATOR . '.env.local')) {
    $dotenv->load(__DIR__.'/.env.local');
}

return
    [
        'paths' => [
            'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
            'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
        ],
        'environments' => [
            'default_migration_table' => 'phinxlog',
            'default_environment' => 'development',
            'development' => [
                'dsn' => $_ENV['DATABASE_URL'],
                'adapter' => 'pgsql',
                'charset' => 'utf8',
            ],
            'production' => [
                'dsn' => $_ENV['DATABASE_URL'],
                'adapter' => 'pgsql',
                'charset' => 'utf8',
                
            ],
        ],
        'version_order' => 'creation',
    ];
