<?php
declare(strict_types=1);

/**
 * Main Application Configuration & Dynamic Base URL Auto-Detection
 * Ilham Ramadhan Setiawan Portfolio
 */

// BASE_DIR: defined by api/ entry points on Vercel, or here on XAMPP/local
if (!defined('BASE_DIR')) {
    define('BASE_DIR', dirname(__DIR__));
}

// Native PSR-4 Autoloader for App\Models and App\Services (No Composer required!)
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    
    if (str_starts_with($relativeClass, 'Models\\')) {
        $file = BASE_DIR . '/models/' . str_replace('\\', '/', substr($relativeClass, 7)) . '.php';
    } elseif (str_starts_with($relativeClass, 'Services\\')) {
        $file = BASE_DIR . '/services/' . str_replace('\\', '/', substr($relativeClass, 9)) . '.php';
    } else {
        $file = BASE_DIR . '/' . str_replace('\\', '/', $relativeClass) . '.php';
    }

    if (file_exists($file)) {
        require_once $file;
    }
});

// Simple .env parser fallback if composer dotenv is not loaded
if (!function_exists('env')) {
    function loadEnv(string $path): void {
        if (!file_exists($path)) {
            return;
        }
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value, " \t\n\r\0\x0B\"'");
                if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                    putenv("{$name}={$value}");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
    }

    loadEnv(BASE_DIR . '/.env');

    function env(string $key, mixed $default = null): mixed {
        $val = getenv($key);
        if ($val === false) {
            $val = $_ENV[$key] ?? $_SERVER[$key] ?? $default;
        }
        if ($val === 'true' || $val === '(true)') return true;
        if ($val === 'false' || $val === '(false)') return false;
        if ($val === 'empty' || $val === '(empty)') return '';
        if ($val === 'null' || $val === '(null)') return null;
        return $val;
    }
}

// Load composer autoloader if present
if (file_exists(BASE_DIR . '/vendor/autoload.php')) {
    require_once BASE_DIR . '/vendor/autoload.php';
}

// Always require helpers
require_once BASE_DIR . '/includes/helpers.php';

// App Settings
$appEnv = env('APP_ENV', 'development');
if ($appEnv === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(0);
    ini_set('display_errors', '0');
}

// Dynamic Base URL detection helper (Works on XAMPP subfolders, localhost:8000, and domain roots)
if (!function_exists('base_url')) {
    function base_url(string $path = ''): string {
        $envUrl = env('APP_URL');
        if (!empty($envUrl)) {
            $base = rtrim($envUrl, '/');
        } else {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            $dir = rtrim(dirname($scriptName), '/\\');
            if ($dir === '.' || $dir === '/') {
                $dir = '';
            }

            $base = $protocol . $host . $dir;
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

return [
    'app_name' => env('APP_NAME', 'Ilham Ramadhan Setiawan'),
    'app_env' => $appEnv,
    'spreadsheet_id' => env('GOOGLE_SPREADSHEET_ID', '1BW3oAgh8EtTBTalwIzRGISxh5nu0VFFTI3BmIHAfVus'),
    'google_api_key' => env('GOOGLE_API_KEY', ''),
    'google_service_account' => env('GOOGLE_SERVICE_ACCOUNT_JSON', ''),
    'cache_ttl' => (int) env('CACHE_TTL', 300),
];
