<?php
declare(strict_types=1);

/**
 * Site Navigation Header & Mobile Overlay Menu
 * Ilham Ramadhan Setiawan Portfolio
 */

$profile = $profile ?? (new \App\Services\GoogleSheetsService())->getProfile();
$waUrl = !empty($profile->whatsapp) ? $profile->whatsapp : 'https://wa.me/6289626376804?text=Halo%20Ilham,%20saya%20tertarik%20untuk%20diskusi%20proyek';
?>
<!-- Navigation Header -->
<header class="site-header">
    <div class="container">
        <div class="nav-wrapper">
            <a href="<?= route_url('/'); ?>" class="brand-logo" data-cursor="BERANDA">
                ARCHIVE
            </a>

            <nav class="nav-links">
                <a href="<?= route_url('/work'); ?>" class="nav-link <?= is_active_route('/work') ? 'active' : ''; ?>" data-cursor="JELAJAH">KARYA</a>
                <a href="<?= route_url('/about'); ?>" class="nav-link <?= is_active_route('/about') ? 'active' : ''; ?>" data-cursor="PROFIL">TENTANG</a>
                <a href="<?= route_url('/contact'); ?>" class="nav-link <?= is_active_route('/contact') ? 'active' : ''; ?>" data-cursor="HUBUNGI">KONTAK</a>
                <a href="<?= e($waUrl); ?>" target="_blank" rel="noopener" class="nav-btn-wa" data-cursor="CHAT">
                    WHATSAPP &nearr;
                </a>
            </nav>

            <button id="mobile-menu-toggle" class="mobile-menu-toggle" aria-label="Buka Menu Navigation">
                <span class="hamburger-bar"></span>
                <span class="hamburger-bar"></span>
            </button>
        </div>
    </div>
</header>

<!-- Mobile Fullscreen Overlay -->
<div id="mobile-overlay" class="mobile-overlay">
    <div class="mobile-overlay-header">
        <span class="mobile-overlay-title">ARCHIVE &mdash; NAVIGASI</span>
        <button id="mobile-overlay-close" class="mobile-overlay-close-btn" aria-label="Tutup Menu">
            TUTUP <span>&times;</span>
        </button>
    </div>

    <nav class="mobile-nav-links">
        <a href="<?= route_url('/'); ?>" class="mobile-nav-link <?= is_active_route('/') ? 'active' : ''; ?>">BERANDA</a>
        <a href="<?= route_url('/work'); ?>" class="mobile-nav-link <?= is_active_route('/work') ? 'active' : ''; ?>">KARYA</a>
        <a href="<?= route_url('/about'); ?>" class="mobile-nav-link <?= is_active_route('/about') ? 'active' : ''; ?>">TENTANG</a>
        <a href="<?= route_url('/contact'); ?>" class="mobile-nav-link <?= is_active_route('/contact') ? 'active' : ''; ?>">KONTAK</a>
        <a href="<?= e($waUrl); ?>" target="_blank" rel="noopener" class="mobile-nav-link mobile-wa-link">
            WHATSAPP &nearr;
        </a>
    </nav>
</div>
