<?php
declare(strict_types=1);

/**
 * Work Portfolio Gallery Page
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;
use App\Services\GoogleDriveService;

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();
$projects = $sheetsService->getProjects();

$pageTitle = "Karya — " . e($profile->name);
$metaDescription = "Koleksi proyek fotografi dan videografi karya Ilham Ramadhan Setiawan.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<section class="section-padding" style="padding-top: calc(var(--header-height) + 40px);">
    <div class="container">
        <!-- Header & Filter Bar -->
        <div class="works-page-header">
            <div class="works-header-text">
                <h1 class="works-title">KARYA PILIHAN</h1>
                <p class="works-subtitle">Koleksi proyek pilihan yang mewakili kampanye fotografi & produksi video.</p>
            </div>

            <!-- Filter Pills Bar -->
            <div class="filter-pill-container">
                <button class="filter-pill-btn active" data-filter="ALL" data-cursor="FILTER">SEMUA</button>
                <button class="filter-pill-btn" data-filter="FOTOGRAFI" data-cursor="FILTER">FOTOGRAFI</button>
                <button class="filter-pill-btn" data-filter="VIDEOGRAFI" data-cursor="FILTER">VIDEOGRAFI</button>
            </div>
        </div>

        <!-- 3-Column Work Grid (Autoplay Video Preview Loop, Deep Cropped Timeline, Precise Text) -->
        <div class="work-grid">
            <?php foreach ($projects as $project): 
                $isDriveVideo = GoogleDriveService::isDriveUrl($project->coverUrl);
                $driveEmbedUrl = $isDriveVideo ? GoogleDriveService::getDriveVideoEmbedUrl($project->coverUrl) : null;
                $isMp4Video = str_ends_with(strtolower($project->coverUrl), '.mp4');
                $isVideo = $isDriveVideo || $isMp4Video;
            ?>
                <a href="<?= project_url($project->slug); ?>" class="project-card" data-category="<?= e($project->category); ?>" data-cursor="LIHAT DETAIL">
                    <div class="project-media-wrap">
                        <?php if ($isDriveVideo && $driveEmbedUrl): ?>
                            <!-- Google Drive Autoplay Muted Video Preview Loop (Deep Cropped, Zero Timeline Line) -->
                            <div class="card-video-container">
                                <iframe
                                    src="<?= e($driveEmbedUrl); ?>?autoplay=1&muted=1"
                                    class="card-drive-iframe"
                                    allow="autoplay; encrypted-media"
                                    loading="lazy"
                                    title="<?= e($project->title); ?>">
                                </iframe>
                                <div class="card-video-overlay-shield"></div>
                            </div>
                        <?php elseif ($isMp4Video): ?>
                            <!-- Direct MP4 Autoplay Muted Video Preview Loop -->
                            <div class="card-video-container">
                                <video autoplay loop muted playsinline class="card-mp4-video">
                                    <source src="<?= e($project->coverUrl); ?>" type="video/mp4">
                                </video>
                                <div class="card-video-overlay-shield"></div>
                            </div>
                        <?php else: ?>
                            <!-- High-Res Cover Image -->
                            <img src="<?= e($project->coverUrl); ?>" alt="<?= e($project->title); ?>" class="project-cover-img" loading="lazy">
                        <?php endif; ?>

                        <?php if ($isVideo): ?>
                            <div class="card-play-indicator">
                                <span class="play-icon">&#9658;</span>
                                <span class="play-text">VIDEO</span>
                            </div>
                        <?php endif; ?>

                        <span class="card-category-badge"><?= e($project->category); ?></span>
                    </div>

                    <div class="project-meta">
                        <div class="project-meta-sub">
                            <span class="meta-cat"><?= e($project->category); ?></span>
                            <span class="meta-year">&bull; <?= e($project->year); ?></span>
                        </div>
                        <h2 class="project-title"><?= e($project->title); ?></h2>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
