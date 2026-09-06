<?php
declare(strict_types=1);

/**
 * Custom 404 Page Not Found
 * Ilham Ramadhan Setiawan Portfolio
 */

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

$sheetsService = new GoogleSheetsService();
$profile = $sheetsService->getProfile();

$pageTitle = "404 Halaman Tidak Ditemukan — " . e($profile->name);
$metaDescription = "Halaman atau proyek yang Anda cari tidak ditemukan.";

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navigation.php';
?>

<section class="hero-section" style="min-height: 80vh; justify-content: center; align-items: center; text-align: center;">
    <div class="container">
        <span class="mono-tag" style="font-size: 1rem; color: #ff4d4d; letter-spacing: 0.3em;">KESALAHAN 404</span>
        <h1 class="hero-title" style="font-size: clamp(2.5rem, 7vw, 6rem); margin-top: 1rem; margin-bottom: 2rem;">
            PROYEK TIDAK DITEMUKAN
        </h1>
        <p style="color: var(--text-muted); max-width: 500px; margin: 0 auto 3rem; font-size: 1.1rem;">
            Karya visual atau URL yang Anda cari tidak ditemukan atau telah dipindahkan.
        </p>

        <a href="<?= route_url('/work'); ?>" class="magnetic-btn" style="display: inline-block; padding: 1rem 2.5rem; background-color: #ffffff; color: #000000; font-family: var(--font-display); font-weight: 800; letter-spacing: 0.2em; text-transform: uppercase;" data-cursor="KEMBALI">
            KEMBALI KE KARYA &rarr;
        </a>
    </div>
</section>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
