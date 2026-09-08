<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Project Data Model
 */
class Project {
    public string $id;
    public string $title;
    public string $slug;
    public string $category; // VIDEOGRAFI, VIDEO CONTENT, FOTO WEDDING, DOKUMENTASI
    public string $type; // 'video' or 'foto'
    public string $year;
    public string $client;
    public string $description;
    public string $coverUrl;
    public string $thumbnailUrl = '';
    public string $rawCoverUrl = '';
    public string $rawThumbnailUrl = '';
    public bool $featured;
    public bool $published;
    public int $order;

    /** @var Media[] */
    public array $media = [];

    public function __construct(array $data = []) {
        $this->id = (string) ($data['id'] ?? '');
        $this->title = (string) ($data['title'] ?? '');
        
        $slugRaw = (string) ($data['slug'] ?? '');
        if (empty($slugRaw)) {
            if (function_exists('slugify')) {
                $slugRaw = slugify($this->title);
            } else {
                $slugRaw = strtolower(trim((string)preg_replace('~[^-\w]+~', '', preg_replace('~[^\pL\d]+~u', '-', $this->title)), '-'));
            }
        }
        $this->slug = $slugRaw;
        $this->category = strtoupper((string) ($data['category'] ?? 'VIDEOGRAFI'));

        $typeRaw = strtolower((string) ($data['type'] ?? ''));
        if (empty($typeRaw)) {
            $cat = strtoupper($this->category);
            if (in_array($cat, ['VIDEOGRAFI', 'VIDEO CONTENT', 'VIDIO CONTENT', 'VIDEO', 'CINEMATIC', 'COMMERCIAL', 'CAMPAIGN', 'BRAND CONTENT'])) {
                $typeRaw = 'video';
            } else {
                $typeRaw = 'foto';
            }
        }
        $this->type = $typeRaw;

        $this->year = (string) ($data['year'] ?? date('Y'));
        $this->client = (string) ($data['client'] ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->coverUrl = (string) ($data['cover_url'] ?? $data['coverUrl'] ?? '');
        $this->rawCoverUrl = (string) ($data['raw_cover_url'] ?? $data['rawCoverUrl'] ?? $this->coverUrl);
        
        $this->thumbnailUrl = (string) ($data['thumbnail_url'] ?? $data['thumbnailUrl'] ?? '');
        $this->rawThumbnailUrl = (string) ($data['raw_thumbnail_url'] ?? $data['rawThumbnailUrl'] ?? $this->thumbnailUrl);

        $featuredRaw = $data['featured'] ?? false;
        $this->featured = filter_var($featuredRaw, FILTER_VALIDATE_BOOLEAN) || strtoupper((string)$featuredRaw) === 'TRUE' || $featuredRaw === '1';

        $publishedRaw = $data['published'] ?? true;
        $this->published = filter_var($publishedRaw, FILTER_VALIDATE_BOOLEAN) || strtoupper((string)$publishedRaw) === 'TRUE' || $publishedRaw === '1';

        $this->order = (int) ($data['order'] ?? 99);
    }

    public function isVideo(): bool {
        if (!empty($this->type)) {
            return strtolower($this->type) === 'video';
        }
        $cat = strtoupper($this->category);
        return in_array($cat, ['VIDEOGRAFI', 'VIDEO CONTENT', 'VIDIO CONTENT', 'VIDEO', 'CINEMATIC', 'COMMERCIAL', 'CAMPAIGN', 'BRAND CONTENT']);
    }

    public function getThumbnailUrl(): string {
        $target = !empty($this->thumbnailUrl) ? $this->thumbnailUrl : $this->coverUrl;
        if (class_exists('App\Services\GoogleDriveService')) {
            return \App\Services\GoogleDriveService::getDriveImageUrl($target);
        }
        return $target;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'type' => $this->type,
            'year' => $this->year,
            'client' => $this->client,
            'description' => $this->description,
            'cover_url' => $this->coverUrl,
            'thumbnail_url' => $this->thumbnailUrl,
            'featured' => $this->featured,
            'published' => $this->published,
            'order' => $this->order,
        ];
    }
}
