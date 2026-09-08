<?php
declare(strict_types=1);

/**
 * Project Detail Page
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;
use App\Services\GoogleDriveService;

$slug = trim((string) ($_GET['slug'] ?? ''));

if (empty($slug)) {
    header("Location: " . route_url('/work'));
    exit;
}

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();
$project = $sheetsService->getProjectBySlug($slug);

if ($project === null) {
    http_response_code(404);
    require_once __DIR__ . '/404.php';
    exit;
}

$pageTitle = e($project->title) . " — " . e($profile->name);
$metaDescription = e($project->description);
$ogImage = e($project->coverUrl);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<article class="project-header">
    <div class="container">
        <span class="mono-tag"><?= e($project->category); ?> &mdash; <?= e($project->year); ?></span>
        <h1 class="project-title-large"><?= e($project->title); ?></h1>

        <div class="project-info-grid">
            <div>
                <div class="info-item-label">KLIEN</div>
                <div class="info-item-val"><?= e($project->client ?: 'N/A'); ?></div>
            </div>
            <div>
                <div class="info-item-label">KATEGORI</div>
                <div class="info-item-val"><?= e($project->category); ?></div>
            </div>
            <div>
                <div class="info-item-label">TAHUN</div>
                <div class="info-item-val"><?= e($project->year); ?></div>
            </div>
            <div>
                <div class="info-item-label">PERAN</div>
                <div class="info-item-val"><?= e($profile->role); ?></div>
            </div>
        </div>

        <?php if (!empty($project->description)): ?>
            <div class="project-description-wrap">
                <p><?= nl2br(e($project->description)); ?></p>
            </div>
        <?php endif; ?>

        <!-- MEDIA GALLERY STACK -->
        <div class="media-gallery-stack">
            <?php 
            $mediaList = $project->media;
            if (empty($mediaList)) {
                $rawUrl = !empty($project->rawCoverUrl) ? $project->rawCoverUrl : $project->coverUrl;
                $isProjVideo = $project->isVideo();
                
                $fallbackMedia = new \App\Models\Media([
                    'id' => 'cover-' . $project->id,
                    'project_id' => $project->id,
                    'type' => $isProjVideo ? 'video' : 'image',
                    'title' => $project->title,
                    'url' => $rawUrl,
                    'thumbnail_url' => $project->coverUrl,
                    'caption' => '',
                    'published' => true,
                    'order' => 1
                ]);

                if ($isProjVideo) {
                    $embedUrl = GoogleDriveService::getDriveVideoEmbedUrl($rawUrl);
                    if ($embedUrl) {
                        $fallbackMedia->url = $embedUrl;
                    }
                } else {
                    $fallbackMedia->url = GoogleDriveService::getDriveImageUrl($rawUrl);
                }

                $mediaList = [$fallbackMedia];
            }
            ?>

            <?php foreach ($mediaList as $media): 
                $isItemVideo = $media->isVideo() || ($project->isVideo() && strtolower($media->type) !== 'image') || str_contains(strtolower($media->url), 'drive.google.com') || str_contains(strtolower($media->url), '.mp4');
                $isPortrait = false;
                $mediaText = strtolower($media->title . ' ' . $media->caption . ' ' . $project->title . ' ' . $project->category);
                if (str_contains($mediaText, 'portrait') || str_contains($mediaText, 'reels') || str_contains($mediaText, 'reel') || str_contains($mediaText, 'shorts') || str_contains($mediaText, 'tiktok') || str_contains($mediaText, 'vertical')) {
                    $isPortrait = true;
                }
            ?>
                <div class="media-item-wrap">
                    <?php if ($isItemVideo): 
                        $isDriveEmbed = str_contains($media->url, 'drive.google.com');
                        $embedUrl = GoogleDriveService::getDriveVideoEmbedUrl($media->url);
                    ?>
                        <!-- Sleek Video Player -->
                        <div class="video-player-card <?= $isPortrait ? 'portrait-mode' : ''; ?>" 
                             data-video-type="<?= $isDriveEmbed ? 'drive' : 'mp4'; ?>"
                             data-thumbnail-url="<?= e($media->thumbnailUrl ?: $media->url); ?>"
                             data-orientation="<?= $isPortrait ? 'portrait' : 'landscape'; ?>">
                            <?php if ($isDriveEmbed): ?>
                                <div class="spoiler-video-wrapper">
                                    <iframe
                                        src="<?= e($embedUrl); ?>"
                                        class="drive-cropped-iframe"
                                        allow="autoplay; encrypted-media; fullscreen"
                                        allowfullscreen
                                        title="<?= e($media->title ?: $project->title); ?>">
                                    </iframe>
                                    <div class="video-interactive-overlay">
                                        <div class="spoiler-badge">PREVIEW VIDEO</div>
                                        <button class="custom-play-pause-btn" aria-label="Putar / Hentikan Video">
                                            <span class="btn-icon">&#9658;</span>
                                            <span class="btn-text">PUTAR / JEDA VIDEO</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="video-control-bar">
                                    <div class="video-status-text">&bull; PREVIEW VIDEO KARYA</div>
                                    <a href="<?= e($embedUrl); ?>" target="_blank" rel="noopener" class="video-external-link">
                                        LAYAR PENUH &nearr;
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="spoiler-video-wrapper">
                                    <video poster="<?= e($media->thumbnailUrl); ?>" autoplay loop muted playsinline class="spoiler-mp4-video">
                                        <source src="<?= e($media->url); ?>" type="video/mp4">
                                    </video>
                                    <div class="video-interactive-overlay">
                                        <div class="spoiler-badge">MP4 VIDEO</div>
                                        <button class="custom-play-pause-btn" aria-label="Putar &amp; Unmute Video">
                                            <span class="btn-icon">&#9658;</span>
                                            <span class="btn-text">PUTAR &amp; UNMUTE</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="video-control-bar">
                                    <div class="video-status-text">&bull; DOKUMENTASI VISUAL</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <!-- Editorial Photo Gallery Item -->
                        <div class="gallery-image-frame" data-full-src="<?= e($media->url); ?>" data-caption="<?= e($media->caption); ?>" data-cursor="LIHAT FOTO">
                            <img src="<?= e($media->url); ?>" alt="<?= e($media->title ?: $project->title); ?>" loading="lazy">
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($media->caption)): ?>
                        <div class="media-caption"><?= e($media->caption); ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- NEXT PROJECT NAVIGATION -->
        <div style="margin-top: 6rem; border-top: 1px solid var(--border-color); padding-top: 3.5rem; text-align: center;">
            <a href="<?= route_url('/work'); ?>" class="section-link" data-cursor="KEMBALI">
                &larr; KEMBALI KE SEMUA KARYA
            </a>
        </div>
    </div>
</article>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
