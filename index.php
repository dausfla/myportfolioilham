<?php
declare(strict_types=1);

/**
 * Homepage
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();
$featuredProjects = $sheetsService->getFeaturedProjects();
$allProjects = $sheetsService->getProjects();

$pageTitle = e($profile->name) . " — " . e($profile->role);
$metaDescription = e($profile->tagline) . " " . e($profile->bio);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<!-- HERO SECTION -->
<section class="hero-section">
    <div class="container hero-main">
        <div class="hero-title-wrap">
            <h1 class="hero-title">
                <span class="title-line">ILHAM</span>
                <span class="title-line">RAMADHAN</span>
                <span class="title-line">SETIAWAN</span>
            </h1>
        </div>

        <div class="hero-subtitle-bar">
            <div class="hero-role"><?= e($profile->role); ?></div>
            <div class="hero-statement">
                "<?= e($profile->tagline); ?>"
            </div>
        </div>

        <?php if (!empty($featuredProjects)): 
            $heroCover = $featuredProjects[0]->coverUrl;
        ?>
        <div class="hero-media-preview" data-cursor="LIHAT">
            <a href="<?= project_url($featuredProjects[0]->slug); ?>">
                <img src="<?= e($heroCover); ?>" alt="Featured Portfolio Hero Preview" loading="lazy">
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- FEATURED WORK SECTION -->
<section class="section-padding">
    <div class="container">
        <div class="section-header">
            <div>
                <span class="mono-tag">KOLEKSI PILIHAN</span>
                <h2 class="section-title">KARYA UNGGULAN</h2>
            </div>
            <a href="<?= route_url('/work'); ?>" class="section-link" data-cursor="LIHAT SEMUA">
                LIHAT SEMUA KARYA &rarr;
            </a>
        </div>

        <div class="work-grid">
            <?php foreach (array_slice($featuredProjects, 0, 6) as $project): 
                $isDriveVideo = \App\Services\GoogleDriveService::isDriveUrl($project->coverUrl);
                $driveEmbedUrl = $isDriveVideo ? \App\Services\GoogleDriveService::getDriveVideoEmbedUrl($project->coverUrl) : null;
                $isMp4Video = str_ends_with(strtolower($project->coverUrl), '.mp4');
                $isVideo = $isDriveVideo || $isMp4Video;
            ?>
                <a href="<?= project_url($project->slug); ?>" class="project-card" data-category="<?= e($project->category); ?>" data-cursor="LIHAT DETAIL">
                    <div class="project-media-wrap">
                        <?php if ($isDriveVideo && $driveEmbedUrl): ?>
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
                            <div class="card-video-container">
                                <video autoplay loop muted playsinline class="card-mp4-video">
                                    <source src="<?= e($project->coverUrl); ?>" type="video/mp4">
                                </video>
                                <div class="card-video-overlay-shield"></div>
                            </div>
                        <?php else: ?>
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
                        <h3 class="project-title"><?= e($project->title); ?></h3>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ABOUT BRIEF SECTION -->
<section class="section-padding" style="background-color: var(--bg-secondary); border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color);">
    <div class="container">
        <div class="about-grid">
            <div class="about-portrait">
                <img src="<?= e($profile->profileImage); ?>" alt="<?= e($profile->name); ?>" loading="lazy" referrerpolicy="no-referrer">
            </div>
            <div>
                <span class="mono-tag">BIOGRAFI & VISI</span>
                <div class="about-bio">
                    <p><?= e($profile->bio); ?></p>
                </div>
                <div style="margin-top: 3rem;">
                    <a href="<?= route_url('/about'); ?>" class="section-link" data-cursor="BACA">
                        SELENGKAPNYA TENTANG ILHAM &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CAPABILITIES BRIEF -->
<section class="section-padding">
    <div class="container">
        <span class="mono-tag">LAYANAN & KEAHLIAN</span>
        <h2 class="section-title">LAYANAN UTAMA</h2>

        <div class="capabilities-grid">
            <div class="capability-item">
                <div class="capability-num">01</div>
                <div class="capability-title">PRODUKSI VIDEO CINEMATIC</div>
            </div>
            <div class="capability-item">
                <div class="capability-num">02</div>
                <div class="capability-title">DOKUMENTASI & FILM DOKUMENTER</div>
            </div>
            <div class="capability-item">
                <div class="capability-num">03</div>
                <div class="capability-title">KONTEN BRAND & IKLAN</div>
            </div>
            <div class="capability-item">
                <div class="capability-num">04</div>
                <div class="capability-title">KAMPANYE MEDIA SOSIAL</div>
            </div>
        </div>
    </div>
</section>

<!-- CONTACT CTA SECTION -->
<section class="section-padding" style="border-top: 1px solid var(--border-color);">
    <div class="container">
        <span class="mono-tag">HUBUNGI SAYA</span>
        <div class="contact-hero-text">
            MARI BUAT<br>
            SESUATU YANG<br>
            BERMAKNA.
        </div>

        <div class="contact-links-grid">
            <a href="mailto:<?= e($profile->email); ?>" class="contact-link-card" data-cursor="EMAIL">
                <div class="contact-link-label">EMAIL</div>
                <div class="contact-link-val"><?= e($profile->email); ?></div>
            </a>
            <a href="<?= e($profile->whatsapp); ?>" target="_blank" rel="noopener" class="contact-link-card" data-cursor="CHAT">
                <div class="contact-link-label">WHATSAPP</div>
                <div class="contact-link-val">MULAI CHAT</div>
            </a>
            <a href="<?= e($profile->instagram); ?>" target="_blank" rel="noopener" class="contact-link-card" data-cursor="INSTAGRAM">
                <div class="contact-link-label">INSTAGRAM</div>
                <div class="contact-link-val">@INSTAGRAM</div>
            </a>
            <a href="<?= e($profile->youtube); ?>" target="_blank" rel="noopener" class="contact-link-card" data-cursor="YOUTUBE">
                <div class="contact-link-label">YOUTUBE</div>
                <div class="contact-link-val">KANAL YOUTUBE</div>
            </a>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
