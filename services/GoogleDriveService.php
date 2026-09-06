<?php
declare(strict_types=1);

namespace App\Services;

/**
 * Google Drive URL Normalizer & Fallback Service
 */
class GoogleDriveService {
    /**
     * Extract Google Drive File ID from various URL formats
     */
    public static function getDriveFileId(?string $url): ?string {
        if (empty($url)) {
            return null;
        }

        $url = trim($url);

        // Format 1: /file/d/FILE_ID (matches /view, /edit, /preview, etc.)
        if (preg_match('/\/file\/d\/([a-zA-Z0-9_-]{20,})/i', $url, $matches)) {
            return $matches[1];
        }

        // Format 2: ?id=FILE_ID or &id=FILE_ID (matches open?id=..., uc?id=...)
        if (preg_match('/[?&]id=([a-zA-Z0-9_-]{20,})/i', $url, $matches)) {
            return $matches[1];
        }

        // Format 3: /d/FILE_ID (matches lh3.googleusercontent.com/d/FILE_ID)
        if (preg_match('/\/d\/([a-zA-Z0-9_-]{20,})/i', $url, $matches)) {
            return $matches[1];
        }

        // Format 4: /folders/FOLDER_ID (matches drive/folders/...)
        if (preg_match('/\/folders\/([a-zA-Z0-9_-]{20,})/i', $url, $matches)) {
            return $matches[1];
        }

        // Format 5: Direct string File ID
        if (preg_match('/^([a-zA-Z0-9_-]{20,})$/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Normalize any Google Drive URL into an accessible direct view link
     */
    public static function normalizeDriveUrl(?string $url): string {
        if (empty($url)) {
            return static::getPlaceholderUrl();
        }

        $fileId = static::getDriveFileId($url);
        if ($fileId) {
            return "https://lh3.googleusercontent.com/d/{$fileId}";
        }

        // Return original if it's already a direct HTTP/HTTPS web image or video URL
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return static::getPlaceholderUrl();
    }

    /**
     * Get direct Google Drive image display URL
     */
    public static function getDriveImageUrl(?string $url, int $width = 1920): string {
        if (empty($url)) {
            return static::getPlaceholderUrl();
        }

        $fileId = static::getDriveFileId($url);
        if ($fileId) {
            return "https://lh3.googleusercontent.com/d/{$fileId}=w{$width}";
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return static::getPlaceholderUrl();
    }

    /**
     * Get lightweight thumbnail URL for grid views
     */
    public static function getDriveThumbnailUrl(?string $url, int $width = 800): string {
        if (empty($url)) {
            return static::getPlaceholderUrl();
        }

        $fileId = static::getDriveFileId($url);
        if ($fileId) {
            return "https://lh3.googleusercontent.com/d/{$fileId}=w{$width}";
        }

        if (filter_var($url, FILTER_VALIDATE_URL)) {
            return $url;
        }

        return static::getPlaceholderUrl();
    }

    /**
     * Elegant dark monochrome SVG placeholder generator
     */
    public static function getPlaceholderUrl(string $text = 'MEDIA PREVIEW'): string {
        $encodedText = rawurlencode($text);
        return "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='1200' height='800' viewBox='0 0 1200 800'><rect width='100%' height='100%' fill='%230a0a0a'/><path d='M0 0l1200 800M1200 0L0 800' stroke='%231f1f1f' stroke-width='1'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='%23555555' font-family='sans-serif' font-size='18' letter-spacing='4'>{$encodedText}</text></svg>";
    }
}
