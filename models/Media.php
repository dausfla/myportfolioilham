<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Media Item Data Model (Image or Video)
 */
class Media {
    public string $id;
    public string $projectId;
    public string $type; // 'image' or 'video'
    public string $title;
    public string $url;
    public string $thumbnailUrl;
    public string $caption;
    public int $order;
    public bool $published;

    public function __construct(array $data = []) {
        $this->id = (string) ($data['id'] ?? '');
        $this->projectId = (string) ($data['project_id'] ?? $data['projectId'] ?? '');
        $this->type = strtolower((string) ($data['type'] ?? 'image'));
        $this->title = (string) ($data['title'] ?? '');
        $this->url = (string) ($data['url'] ?? '');
        $this->thumbnailUrl = (string) ($data['thumbnail_url'] ?? $data['thumbnailUrl'] ?? $this->url);
        $this->caption = (string) ($data['caption'] ?? '');
        $this->order = (int) ($data['order'] ?? 99);

        $publishedRaw = $data['published'] ?? true;
        $this->published = filter_var($publishedRaw, FILTER_VALIDATE_BOOLEAN) || strtoupper((string)$publishedRaw) === 'TRUE' || $publishedRaw === '1';
    }

    public function isVideo(): bool {
        if ($this->type === 'video') {
            return true;
        }
        if (!empty($this->url)) {
            $urlLower = strtolower($this->url);
            if (str_contains($urlLower, 'drive.google.com') || str_contains($urlLower, '.mp4') || str_contains($urlLower, 'youtube.com') || str_contains($urlLower, 'vimeo.com')) {
                return true;
            }
        }
        return false;
    }

    public function isImage(): bool {
        return !$this->isVideo();
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'project_id' => $this->projectId,
            'type' => $this->type,
            'title' => $this->title,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnailUrl,
            'caption' => $this->caption,
            'order' => $this->order,
            'published' => $this->published,
        ];
    }
}
