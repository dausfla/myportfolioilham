<?php
/**
 * Vercel Serverless Entry Point — Homepage
 */
define('BASE_DIR', dirname(__DIR__));

try {
    require_once BASE_DIR . '/index.php';
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}
