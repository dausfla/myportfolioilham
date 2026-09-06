<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Project;
use App\Models\Media;
use App\Models\Profile;

/**
 * System Data Reader (Local CMS JSON Data)
 * Ilham Ramadhan Setiawan Portfolio
 */
class GoogleSheetsService {

    /**
     * Get all published projects sorted by order
     * @return Project[]
     */
    public function getProjects(): array {
        $data = $this->loadJsonData('projects');
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
        $data = $this->loadJsonData('media');
        $mediaList = [];

        foreach ($data as $row) {
            $media = new Media($row);
            if ($media->published && ($media->projectId === $projectId || empty($projectId))) {
                if ($media->isImage()) {
                    $media->url = GoogleDriveService::getDriveImageUrl($media->url);
                    $media->thumbnailUrl = GoogleDriveService::getDriveThumbnailUrl($media->thumbnailUrl ?: $media->url);
                } else {
                    $embedUrl = GoogleDriveService::getDriveVideoEmbedUrl($media->url);
                    if ($embedUrl) {
                        $media->url = $embedUrl;
                    }
                    if (!empty($media->thumbnailUrl)) {
                        $media->thumbnailUrl = GoogleDriveService::getDriveThumbnailUrl($media->thumbnailUrl);
                    } elseif (GoogleDriveService::getDriveFileId($media->url)) {
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
        $data = $this->loadJsonData('profile');
        $profile = new Profile($data);
        if (!empty($profile->profileImage)) {
            $profile->profileImage = GoogleDriveService::getDriveImageUrl($profile->profileImage);
        }
        return $profile;
    }

    /**
     * Load JSON data file directly from data/ folder
     */
    private function loadJsonData(string $filename): array {
        $dataFile = BASE_DIR . '/data/' . strtolower($filename) . '.json';
        if (file_exists($dataFile)) {
            $content = @file_get_contents($dataFile);
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
