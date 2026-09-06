<?php
declare(strict_types=1);

/**
 * Helper Functions
 * Ilham Ramadhan Setiawan Portfolio
 */

if (!function_exists('e')) {
    /**
     * Escape HTML special characters for security
     */
    function e(?string $value): string {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('slugify')) {
    /**
     * Convert string to URL friendly slug
     */
    function slugify(string $text): string {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        return empty($text) ? 'n-a' : $text;
    }
}

if (!function_exists('project_url')) {
    /**
     * Generate project detail URL
     */
    function project_url(string $slug): string {
        return base_url('project.php?slug=' . urlencode($slug));
    }
}

if (!function_exists('route_url')) {
    /**
     * Generate route URL (/work, /about, /contact)
     */
    function route_url(string $route): string {
        $route = trim($route, '/');
        if (empty($route) || $route === 'index') {
            return base_url('index.php');
        }
        // Extract path part only (before ? query string) to check extension
        $pathPart = explode('?', $route, 2)[0];
        if (str_ends_with($pathPart, '.php')) {
            return base_url($route);
        }
        return base_url($route . '.php');
    }
}

if (!function_exists('is_active_route')) {
    /**
     * Check if current URI matches route for navigation highlighting
     */
    function is_active_route(string $path): bool {
        $currentUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $cleanPath = trim($path, '/');
        if (empty($cleanPath) || $cleanPath === 'index') {
            return str_contains($currentUri, 'index.php') || $currentUri === '/' || str_ends_with($currentUri, '/ilham-ramadhan-portfolio/');
        }
        return str_contains($currentUri, $cleanPath);
    }
}

if (!function_exists('asset_url')) {
    /**
     * Return asset URL with version query for cache busting
     */
    function asset_url(string $path): string {
        $fullPath = BASE_DIR . '/' . ltrim($path, '/');
        $ver = file_exists($fullPath) ? filemtime($fullPath) : '1.0.0';
        return base_url(ltrim($path, '/')) . '?v=' . $ver;
    }
}


