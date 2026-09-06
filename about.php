<?php
declare(strict_types=1);

/**
 * About & Capabilities Page
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();

$pageTitle = "Tentang — " . e($profile->name);
$metaDescription = e($profile->bio);

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<section class="section-padding" style="padding-top: calc(var(--header-height) + 60px);">
    <div class="container">
        <div style="margin-bottom: 4rem;">
            <span class="mono-tag">BIOGRAFI & PROFIL</span>
            <h1 class="hero-title" style="font-size: clamp(3rem, 8vw, 7.5rem);">TENTANG SAYA</h1>
        </div>

        <div class="about-grid">
            <div class="about-portrait">
                <img src="<?= e($profile->profileImage); ?>" alt="<?= e($profile->name); ?>" loading="lazy" referrerpolicy="no-referrer">
            </div>
            <div>
                <span class="mono-tag">VISI</span>
                <div class="about-bio">
                    <p style="font-size: 1.25rem; line-height: 1.8; font-weight: 300; color: var(--text-primary);"><?= e($profile->bio); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CAPABILITIES SECTION -->
<section class="section-padding" style="border-top: 1px solid var(--border-color); background-color: var(--bg-secondary);">
    <div class="container">
        <span class="mono-tag">LAYANAN UTAMA</span>
        <h2 class="section-title">LAYANAN & SPESIALISASI</h2>

        <div class="capabilities-grid">
            <div class="capability-item">
                <div class="capability-num">01</div>
                <div class="capability-title">PRODUKSI VIDEO CINEMATIC</div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.8rem;">
                    Perencanaan konsep visual, pengarahan sinematografi, estetika film, bercerita emosional, dan color grading profesional.
                </p>
            </div>
            <div class="capability-item">
                <div class="capability-num">02</div>
                <div class="capability-title">EDITING & PASCA-PRODUKSI</div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.8rem;">
                    Pemotongan video akhir, penyusunan ritme narasi (storytelling), tata suara (sound design), serta efek visual.
                </p>
            </div>
            <div class="capability-item">
                <div class="capability-num">03</div>
                <div class="capability-title">KONTEN BRAND & IKLAN</div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.8rem;">
                    Kampanye video produk, identitas visual brand, commercial storytelling, dan promosi produk.
                </p>
            </div>
            <div class="capability-item">
                <div class="capability-num">04</div>
                <div class="capability-title">DOKUMENTASI & FILM DOKUMENTER</div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.8rem;">
                    Perekaman momen nyata, dokumentasi acara budaya, kisah personal, serta penyampaian cerita otentik.
                </p>
            </div>
            <div class="capability-item">
                <div class="capability-num">05</div>
                <div class="capability-title">KAMPANYE MEDIA SOSIAL</div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.8rem;">
                    Pembuatan konten video pendek & panjang yang kreatif, estetik, dan dioptimalkan untuk engagement media sosial.
                </p>
            </div>
            <div class="capability-item">
                <div class="capability-num">06</div>
                <div class="capability-title">PRODUKSI VIDEO ACARA & WEDDING</div>
                <p style="color: var(--text-muted); font-size: 0.9rem; margin-top: 0.8rem;">
                    Dokumentasi video cinematic untuk pernikahan, acara eksklusif, serta film kenangan bermakna.
                </p>
            </div>
        </div>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
