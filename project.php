<?php
declare(strict_types=1);

/**
 * Project Detail Page
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

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
            <?php if (empty($project->media)): ?>
                <!-- Fallback to cover image if no separate media entries exist -->
                <div class="media-item-wrap">
                    <div class="gallery-image-frame" data-full-src="<?= e($project->coverUrl); ?>" data-cursor="LIHAT FOTO">
                        <img src="<?= e($project->coverUrl); ?>" alt="<?= e($project->title); ?>" loading="lazy">
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($project->media as $media): ?>
                    <div class="media-item-wrap">
                        <?php if ($media->isVideo()): 
                            $isDriveEmbed = str_contains($media->url, 'drive.google.com') && str_contains($media->url, '/preview');
                        ?>
                            <!-- Video Player — Google Drive or Native MP4 -->
                            <div class="video-player-container">
                                <?php if ($isDriveEmbed): ?>
                                    <!-- Google Drive Embed Player with Clean Top-Crop -->
                                    <div class="drive-video-wrapper">
                                        <iframe
                                            src="<?= e($media->url); ?>"
                                            class="drive-video-iframe"
                                            allow="autoplay; encrypted-media; fullscreen"
                                            allowfullscreen
                                            loading="lazy"
                                            title="<?= e($media->title ?: $project->title); ?>">
                                        </iframe>
                                    </div>
                                    <div class="video-touch-toolbar">
                                        <span class="video-touch-label">&#9658; TEKAN UTK PUTAR / HENTIKAN</span>
                                        <a href="<?= e($media->url); ?>" target="_blank" rel="noopener" class="video-fullscreen-btn">
                                            LAYAR PENUH &nearr;
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <!-- Direct MP4 Video Player -->
                                    <div class="custom-mp4-wrapper">
                                        <video poster="<?= e($media->thumbnailUrl); ?>" preload="metadata" controls playsinline>
                                            <source src="<?= e($media->url); ?>" type="video/mp4">
                                            Browser Anda tidak mendukung pemutar video ini.
                                        </video>
                                        <div class="video-controls-overlay">
                                            <button class="play-trigger-btn" aria-label="Putar Video">&#9658;</button>
                                        </div>
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
            <?php endif; ?>
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
