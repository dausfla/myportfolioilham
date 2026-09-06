<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Server-side JSON Cache Service
 * 300s TTL with Stale Cache Fallback capability
 */
class CacheService {
    private string $cacheDir;
    private int $ttl;

    public function __construct(string $cacheDir = null, int $ttl = 300) {
        if ($cacheDir === null) {
            $isVercel = !empty($_SERVER['VERCEL']) || !empty(getenv('VERCEL')) || (isset($_SERVER['SCRIPT_NAME']) && str_contains($_SERVER['SCRIPT_NAME'], '/api/'));
            $cacheDir = $isVercel ? '/tmp/cache' : (BASE_DIR . '/cache');
        }
        $this->cacheDir = $cacheDir;
        $this->ttl = $ttl;

        if (!is_dir($this->cacheDir)) {
            @mkdir($this->cacheDir, 0777, true);
        }
    }

    private function getFilePath(string $key): string {
        return $this->cacheDir . '/' . preg_replace('/[^a-zA-Z0-9_-]/', '', $key) . '.json';
    }

    /**
     * Read cached data if valid within TTL
     */
    public function get(string $key): ?array {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return null;
        }

        $modified = @filemtime($file);
        if ($modified && (time() - $modified) > $this->ttl) {
            // Expired cache, return null so caller re-fetches
            return null;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Read stale cache regardless of TTL (useful when external API fails)
     */
    public function getStale(string $key): ?array {
        $file = $this->getFilePath($key);
        if (!file_exists($file)) {
            return null;
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Save data to cache file gracefully
     */
    public function set(string $key, array $data): bool {
        $file = $this->getFilePath($key);
        $dir = dirname($file);

        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }

        if (file_exists($file) && !is_writable($file)) {
            @chmod($file, 0666);
        }

        $encoded = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $result = @file_put_contents($file, $encoded, LOCK_EX);
        return $result !== false;
    }

    /**
     * Clear all cached json files
     */
    public function clear(): void {
        $files = glob($this->cacheDir . '/*.json');
        if (is_array($files)) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
    }
}
