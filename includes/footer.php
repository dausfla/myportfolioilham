<?php
declare(strict_types=1);

/**
 * Editorial Footer Template
 * Ilham Ramadhan Setiawan Portfolio
 */

$profile = $profile ?? (new \App\Services\GoogleSheetsService())->getProfile();
?>
<footer class="site-footer">
    <div class="container">
        <div class="footer-top">
            <div>
                <div class="footer-brand"><?= e($profile->name); ?></div>
                <div style="font-size: 0.75rem; letter-spacing: 0.2em; text-transform: uppercase; color: var(--text-muted); margin-top: 0.4rem;">
                    <?= e($profile->role); ?>
                </div>
            </div>

            <a href="#top" id="back-to-top" class="back-to-top" data-cursor="ATAS">
                KEMBALI KE ATAS &uarr;
            </a>
        </div>

        <div class="footer-bottom">
            <div>&copy; <?= date('Y'); ?> <?= e($profile->name); ?>. HAK CIPTA DILINDUNGI.</div>
            <div>
                <?= e($profile->location); ?> &nbsp;&bull;&nbsp; 
                <a href="<?= route_url('admin.php'); ?>" style="color: var(--text-muted); text-decoration: none;" data-cursor="ADMIN">ADMIN PANEL</a>
            </div>
        </div>
    </div>
</footer>

<!-- Lightbox Modal Container -->
<div id="lightbox-modal" class="lightbox-modal">
    <span id="lightbox-close" class="lightbox-close">&times;</span>
    <span id="lightbox-prev" class="lightbox-prev">&lsaquo;</span>
    <span id="lightbox-next" class="lightbox-next">&rsaquo;</span>
    <div class="lightbox-content">
        <img id="lightbox-image" class="lightbox-image" src="" alt="Enlarged media frame">
        <div id="lightbox-caption" class="media-caption" style="text-align: center; margin-top: 1rem;"></div>
    </div>
</div>

<!-- JavaScript Bundles -->
<script src="<?= asset_url('assets/js/cursor.js'); ?>"></script>
<script src="<?= asset_url('assets/js/animation.js'); ?>"></script>
<script src="<?= asset_url('assets/js/gallery.js'); ?>"></script>
<script src="<?= asset_url('assets/js/main.js'); ?>"></script>
</body>
</html>
