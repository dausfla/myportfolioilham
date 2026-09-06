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

if (!function_exists('is_vercel')) {
    /**
     * Detect if running on Vercel serverless environment
     */
    function is_vercel(): bool {
        // Vercel sets these env vars; also check APP_URL for vercel.app domain
        if (!empty($_SERVER['VERCEL']) || !empty(getenv('VERCEL'))) {
            return true;
        }
        $appUrl = env('APP_URL', '');
        if (!empty($appUrl) && (str_contains($appUrl, 'vercel.app') || str_contains($appUrl, 'vercel.com'))) {
            return true;
        }
        // Check if script is served from /api/ directory (Vercel entry points)
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        return str_contains($scriptName, '/api/');
    }
}

if (!function_exists('project_url')) {
    /**
     * Generate project detail URL
     */
    function project_url(string $slug): string {
        if (is_vercel()) {
            return base_url('project/' . urlencode($slug));
        }
        return base_url('project.php?slug=' . urlencode($slug));
    }
}

if (!function_exists('route_url')) {
    /**
     * Generate route URL — clean URLs on Vercel, .php URLs on XAMPP
     */
    function route_url(string $route): string {
        $route = trim($route, '/');

        // Extract path part only (before ? query string)
        $pathPart = explode('?', $route, 2)[0];
        $queryPart = isset(explode('?', $route, 2)[1]) ? '?' . explode('?', $route, 2)[1] : '';

        // Strip .php from path for clean URL on Vercel
        $cleanPath = str_ends_with($pathPart, '.php') ? substr($pathPart, 0, -4) : $pathPart;

        if (empty($cleanPath) || $cleanPath === 'index') {
            return base_url('');
        }

        if (is_vercel()) {
            // On Vercel: generate clean URLs (/about, /work, /admin?action=logout)
            return base_url($cleanPath . $queryPart);
        }

        // On XAMPP: generate .php URLs (about.php, work.php)
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


