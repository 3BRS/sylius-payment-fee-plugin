<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpKernel\Kernel;

require dirname(__DIR__).'../../../vendor/autoload.php';

ini_set('memory_limit', $_SERVER['APP_MEMORY_LIMIT'] ?? ($_ENV['APP_MEMORY_LIMIT'] ?? '512M'));


if (($_SERVER['APP_ENV'] ?? '') === 'test'
    && (!empty($_ENV['APP_SUPPRESS_DEPRECATED_ERRORS'])
        || PHP_MAJOR_VERSION >= 8
    )
) {
    // Suppress E_DEPRECATED errors during Behat tests to avoid vendor-related deprecations
    // that we cannot fix directly. This includes:
    // - PHP 8.1+: strtolower(null) deprecations in symfony/css-selector (NodeExtension.php:163)
    // - PHP 8.0+: libxml_disable_entity_loader() deprecations in symfony/dom-crawler
    //
    // This suppression only affects the test environment and can be disabled by setting
    // APP_SUPPRESS_DEPRECATED_ERRORS=0 in .env.test.local if needed.
    //
    // TODO: Remove this when Symfony fixes the underlying deprecations in css-selector
    error_reporting(E_ALL ^ E_DEPRECATED);
}

// Load cached env vars if the .env.local.php file exists
// Run "composer dump-env prod" to create it (requires symfony/flex >=1.2)
if (is_readable(dirname(__DIR__) . '/.env.local.php') && is_array($env = @include dirname(__DIR__) . '/.env.local.php')) {
    $_SERVER += $env;
    $_ENV += $env;
} elseif (!class_exists(Dotenv::class)) {
    throw new RuntimeException('Please run "composer require symfony/dotenv" to load the ".env" files configuring the application.');
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');

    return;
} else {
    // load all the .env files
    (new Dotenv())->loadEnv(dirname(__DIR__) . '/.env');
}

$_SERVER['APP_ENV']   = $_ENV['APP_ENV'] = ($_SERVER['APP_ENV'] ?? $_ENV['APP_ENV'] ?? null) ?: 'dev';
$_SERVER['APP_DEBUG'] ??= $_ENV['APP_DEBUG'] ?? 'prod' !== $_SERVER['APP_ENV'];
$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = (int) $_SERVER['APP_DEBUG'] || filter_var($_SERVER['APP_DEBUG'], \FILTER_VALIDATE_BOOLEAN) ? '1' : '0';
