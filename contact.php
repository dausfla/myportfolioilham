<?php
declare(strict_types=1);

/**
 * Contact Page
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();

$pageTitle = "Kontak — " . e($profile->name);
$metaDescription = "Hubungi Ilham Ramadhan Setiawan untuk proyek videografi, editing video, dan produksi film komersial.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<section class="section-padding" style="padding-top: calc(var(--header-height) + 60px);">
    <div class="container">
        <span class="mono-tag">MULAI DISKUSI</span>
        <div class="contact-hero-text">
            MARI BUAT<br>
            SESUATU YANG<br>
            BERMAKNA.
        </div>

        <div style="margin-bottom: 5rem;">
            <a href="mailto:<?= e($profile->email); ?>" class="magnetic-btn" style="display: inline-block; padding: 1.2rem 3rem; background-color: #ffffff; color: #000000; font-family: var(--font-display); font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase; font-size: 0.9rem;" data-cursor="MULAI">
                MULAI PROYEK &rarr;
            </a>
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
