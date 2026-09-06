<?php
declare(strict_types=1);

/**
 * Work Portfolio Gallery Page
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();
$projects = $sheetsService->getProjects();

$pageTitle = "Karya — " . e($profile->name);
$metaDescription = "Koleksi proyek videografi, editing video, dan film komersial pilihan karya Ilham Ramadhan Setiawan.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<section class="section-padding" style="padding-top: calc(var(--header-height) + 60px);">
    <div class="container">
        <div style="margin-bottom: 3rem;">
            <span class="mono-tag">INDEKS PORTOFOLIO</span>
            <h1 class="hero-title" style="font-size: clamp(3rem, 8vw, 7.5rem);">SEMUA KARYA</h1>
        </div>

        <!-- Filter Bar -->
        <div class="filter-bar">
            <button class="filter-btn active" data-filter="ALL" data-cursor="FILTER">SEMUA</button>
            <button class="filter-btn" data-filter="CINEMATIC" data-cursor="FILTER">CINEMATIC</button>
            <button class="filter-btn" data-filter="DOKUMENTASI" data-cursor="FILTER">DOKUMENTASI</button>
            <button class="filter-btn" data-filter="BRAND CONTENT" data-cursor="FILTER">BRAND CONTENT</button>
            <button class="filter-btn" data-filter="CAMPAIGN" data-cursor="FILTER">CAMPAIGN</button>
        </div>

        <!-- Work Grid -->
        <div class="work-grid">
            <?php foreach ($projects as $project): ?>
                <a href="<?= project_url($project->slug); ?>" class="project-card" data-category="<?= e($project->category); ?>" data-cursor="LIHAT">
                    <div class="project-media-wrap">
                        <img src="<?= e($project->coverUrl); ?>" alt="<?= e($project->title); ?>" class="project-cover-img" loading="lazy">
                    </div>
                    <div class="project-meta">
                        <div>
                            <h2 class="project-title"><?= e($project->title); ?></h2>
                        </div>
                        <div class="project-details">
                            <div><?= e($project->category); ?></div>
                            <div><?= e($project->year); ?></div>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
