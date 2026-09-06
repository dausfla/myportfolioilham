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
    public string $category; // Video, Photography, Commercial, Personal, etc.
    public string $year;
    public string $client;
    public string $description;
    public string $coverUrl;
    public bool $featured;
    public bool $published;
    public int $order;

    /** @var Media[] */
    public array $media = [];

    public function __construct(array $data = []) {
        $this->id = (string) ($data['id'] ?? '');
        $this->title = (string) ($data['title'] ?? '');
        $this->slug = (string) ($data['slug'] ?? slugify($this->title));
        $this->category = strtoupper((string) ($data['category'] ?? 'VIDEO'));
        $this->year = (string) ($data['year'] ?? date('Y'));
        $this->client = (string) ($data['client'] ?? '');
        $this->description = (string) ($data['description'] ?? '');
        $this->coverUrl = (string) ($data['cover_url'] ?? $data['coverUrl'] ?? '');
        
        $featuredRaw = $data['featured'] ?? false;
        $this->featured = filter_var($featuredRaw, FILTER_VALIDATE_BOOLEAN) || strtoupper((string)$featuredRaw) === 'TRUE' || $featuredRaw === '1';

        $publishedRaw = $data['published'] ?? true;
        $this->published = filter_var($publishedRaw, FILTER_VALIDATE_BOOLEAN) || strtoupper((string)$publishedRaw) === 'TRUE' || $publishedRaw === '1';

        $this->order = (int) ($data['order'] ?? 99);
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'category' => $this->category,
            'year' => $this->year,
            'client' => $this->client,
            'description' => $this->description,
            'cover_url' => $this->coverUrl,
            'featured' => $this->featured,
            'published' => $this->published,
            'order' => $this->order,
        ];
    }
}
