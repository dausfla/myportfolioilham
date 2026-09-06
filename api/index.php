<?php
/**
 * Vercel Serverless Front Controller
 * Ilham Ramadhan Setiawan Portfolio
 */
ini_set('display_errors', '0');
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);

define('BASE_DIR', dirname(__DIR__));

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = '/' . trim((string)$uri, '/');

try {
    if ($uri === '/' || $uri === '/index' || str_ends_with($uri, '/index.php')) {
        require_once BASE_DIR . '/index.php';
    } elseif ($uri === '/about' || str_ends_with($uri, '/about.php')) {
        require_once BASE_DIR . '/about.php';
    } elseif ($uri === '/work' || str_ends_with($uri, '/work.php')) {
        require_once BASE_DIR . '/work.php';
    } elseif ($uri === '/contact' || str_ends_with($uri, '/contact.php')) {
        require_once BASE_DIR . '/contact.php';
    } elseif (str_starts_with($uri, '/admin') || str_contains($uri, 'admin.php')) {
        require_once BASE_DIR . '/admin.php';
    } elseif (str_starts_with($uri, '/project/')) {
        $parts = explode('/', trim($uri, '/'));
        if (isset($parts[1]) && !empty($parts[1])) {
            $_GET['slug'] = urldecode($parts[1]);
        }
        require_once BASE_DIR . '/project.php';
    } elseif (str_ends_with($uri, '/project.php')) {
        require_once BASE_DIR . '/project.php';
    } else {
        http_response_code(404);
        require_once BASE_DIR . '/404.php';
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo '<h1>Server Error</h1><pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
}


