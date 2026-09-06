<?php
/**
 * Vercel Serverless Entry Point — Homepage
 */
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

define('BASE_DIR', dirname(__DIR__));

try {
    require_once BASE_DIR . '/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}

