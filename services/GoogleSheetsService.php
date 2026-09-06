<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\Media;
use App\Models\Profile;

/**
 * Google Sheets Service & CMS Reader
 */
class GoogleSheetsService {
    private string $spreadsheetId;
    private CacheService $cache;
    private int $cacheTtl;

    public function __construct(string $spreadsheetId = '', ?CacheService $cache = null) {
        $this->spreadsheetId = !empty($spreadsheetId) ? $spreadsheetId : env('GOOGLE_SPREADSHEET_ID', '1BW3oAgh8EtTBTalwIzRGISxh5nu0VFFTI3BmIHAfVus');
        $this->cacheTtl = (int) env('CACHE_TTL', 0);
        $this->cache = $cache ?? new CacheService(BASE_DIR . '/cache', $this->cacheTtl);

        // Check if manual cache refresh trigger is passed in URL (?refresh=1 or ?clear_cache=1)
        if (isset($_GET['refresh']) || isset($_GET['clear_cache']) || $this->cacheTtl <= 0) {
            $this->cache->clear();
        }
    }

    /**
     * Get all published projects sorted by order
     * @return Project[]
     */
    public function getProjects(): array {
        $data = $this->fetchSheetData('PROJECTS');
        $projects = [];

        foreach ($data as $row) {
            $project = new Project($row);
            if ($project->published) {
                $project->coverUrl = GoogleDriveService::getDriveImageUrl($project->coverUrl);
                $projects[] = $project;
            }
        }

        usort($projects, fn(Project $a, Project $b) => $a->order <=> $b->order);
        return $projects;
    }

    /**
     * Get featured published projects
     * @return Project[]
     */
    public function getFeaturedProjects(): array {
        $projects = $this->getProjects();
        return array_values(array_filter($projects, fn(Project $p) => $p->featured));
    }

    /**
     * Get single project by slug with attached media items
     */
    public function getProjectBySlug(string $slug): ?Project {
        $projects = $this->getProjects();
        foreach ($projects as $project) {
            if ($project->slug === $slug || slugify($project->title) === slugify($slug)) {
                $project->media = $this->getMediaByProject($project->id);
                return $project;
            }
        }
        return null;
    }

    /**
     * Get all published media items for a specific project
     * @return Media[]
     */
    public function getMediaByProject(string $projectId): array {
        $data = $this->fetchSheetData('MEDIA');
        $mediaList = [];

        foreach ($data as $row) {
            $media = new Media($row);
            if ($media->published && ($media->projectId === $projectId || empty($projectId))) {
                if ($media->isImage()) {
                    $media->url = GoogleDriveService::getDriveImageUrl($media->url);
                    $media->thumbnailUrl = GoogleDriveService::getDriveThumbnailUrl($media->thumbnailUrl ?: $media->url);
                } else {
                    // For videos: convert Drive share URL to embed/preview URL
                    $embedUrl = GoogleDriveService::getDriveVideoEmbedUrl($media->url);
                    if ($embedUrl) {
                        $media->url = $embedUrl;
                    }
                    // Thumbnail for video poster
                    if (!empty($media->thumbnailUrl)) {
                        $media->thumbnailUrl = GoogleDriveService::getDriveThumbnailUrl($media->thumbnailUrl);
                    } elseif (GoogleDriveService::getDriveFileId($media->url)) {
                        // Auto-generate thumbnail from Drive file if no explicit thumbnail
                        $media->thumbnailUrl = GoogleDriveService::getDriveThumbnailUrl($media->url);
                    }
                }
                $mediaList[] = $media;
            }
        }

        usort($mediaList, fn(Media $a, Media $b) => $a->order <=> $b->order);
        return $mediaList;
    }

    /**
     * Get artist profile information
     */
    public function getProfile(): Profile {
        $data = $this->fetchSheetData('PROFILE');
        $profile = new Profile($data);
        if (!empty($profile->profileImage)) {
            $profile->profileImage = GoogleDriveService::getDriveImageUrl($profile->profileImage);
        }
        return $profile;
    }

    /**
     * Fetch sheet data with caching & multi-tier fallback
     */
    private function fetchSheetData(string $sheetName): array {
        $cacheKey = strtolower($sheetName);

        $enableSheets = env('ENABLE_GOOGLE_SHEETS', false);

        // Tier 1: Check active cache if TTL > 0 and refresh not requested
        if ($this->cacheTtl > 0 && !isset($_GET['refresh']) && !isset($_GET['clear_cache'])) {
            $cached = $this->cache->get($cacheKey);
            if ($cached !== null) {
                return $cached;
            }
        }

        // Tier 2: Attempt live fetch from Google Sheets if enabled
        if ($enableSheets && !empty($this->spreadsheetId)) {
            $liveData = $this->fetchFromGoogleSheets($sheetName);
            if ($liveData !== null && !empty($liveData)) {
                if ($this->cacheTtl > 0) {
                    $this->cache->set($cacheKey, $liveData);
                }
                return $liveData;
            }
        }

        // Tier 3: Attempt stale cache (only when TTL is enabled)
        if ($this->cacheTtl > 0) {
            $stale = $this->cache->getStale($cacheKey);
            if ($stale !== null) {
                return $stale;
            }
        }

        // Tier 4: Load local JSON data file (always fresh, no caching)
        return $this->loadSeedFallback($sheetName);
    }

    /**
     * Fetch live CSV stream from Google Sheets export endpoint
     */
    private function fetchFromGoogleSheets(string $sheetName): ?array {
        if (empty($this->spreadsheetId)) {
            return null;
        }

        $url = "https://docs.google.com/spreadsheets/d/{$this->spreadsheetId}/gviz/tq?tqx=out:csv&sheet=" . urlencode($sheetName);
        
        $ctx = stream_context_create([
            'http' => [
                'method' => 'GET',
                'timeout' => 8,
                'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) PHP-Sheets-Client\r\n"
            ]
        ]);

        $csvString = @file_get_contents($url, false, $ctx);
        if ($csvString === false || empty($csvString)) {
            return null;
        }

        return $this->parseCsvToAssoc($csvString);
    }

    /**
     * Parse CSV string into associative array using native RFC 4180 stream parser
     */
    private function parseCsvToAssoc(string $csvString): array {
        $stream = fopen('php://memory', 'r+');
        if (!$stream) {
            return [];
        }

        fwrite($stream, $csvString);
        rewind($stream);

        $header = fgetcsv($stream);
        if ($header === false || empty($header)) {
            fclose($stream);
            return [];
        }

        $header = array_map(fn($h) => strtolower(trim((string)$h, " \"'\t\n\r\0\x0B")), $header);

        $results = [];
        while (($row = fgetcsv($stream)) !== false) {
            if (empty($row) || (count($row) === 1 && trim((string)$row[0]) === '')) {
                continue;
            }
            if (count($row) < count($header)) {
                $row = array_pad($row, count($header), '');
            }
            $item = [];
            foreach ($header as $index => $key) {
                if ($key !== '') {
                    $item[$key] = trim((string)($row[$index] ?? ''), " \"'\t\n\r\0\x0B");
                }
            }
            $results[] = $item;
        }

        fclose($stream);
        return $results;
    }

    /**
     * Load hardcoded JSON seed files when external network is unavailable
     */
    private function loadSeedFallback(string $sheetName): array {
        $dataFile = BASE_DIR . '/data/' . strtolower($sheetName) . '.json';
        if (file_exists($dataFile)) {
            $content = @file_get_contents($dataFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }

        $cacheFile = BASE_DIR . '/cache/' . strtolower($sheetName) . '.json';
        if (file_exists($cacheFile)) {
            $content = @file_get_contents($cacheFile);
            if ($content !== false) {
                $decoded = json_decode($content, true);
                if (is_array($decoded)) {
                    return $decoded;
                }
            }
        }
        return [];
    }
}
