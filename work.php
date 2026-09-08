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
                <button class="filter-pill-btn" data-filter="VIDEOGRAFI" data-cursor="FILTER">VIDEOGRAFI</button>
                <button class="filter-pill-btn" data-filter="VIDEO CONTENT" data-cursor="FILTER">VIDEO CONTENT</button>
                <button class="filter-pill-btn" data-filter="FOTO WEDDING" data-cursor="FILTER">FOTO WEDDING</button>
                <button class="filter-pill-btn" data-filter="DOKUMENTASI" data-cursor="FILTER">DOKUMENTASI</button>
            </div>
        </div>

        <!-- 3-Column Work Grid (Static High-Res Cover Image with Video Indicator Badge) -->
        <div class="work-grid">
            <?php foreach ($projects as $project): 
                $isVideo = $project->isVideo();
            ?>
                <a href="<?= project_url($project->slug); ?>" class="project-card" data-category="<?= e($project->category); ?>" data-cursor="LIHAT DETAIL">
                    <div class="project-media-wrap">
                        <!-- High-Res Custom Thumbnail Image -->
                        <img src="<?= e($project->getThumbnailUrl()); ?>" alt="<?= e($project->title); ?>" class="project-cover-img" loading="lazy">

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
