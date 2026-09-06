<?php
declare(strict_types=1);

/**
 * Web Admin Dashboard CMS
 * Ilham Ramadhan Setiawan Portfolio
 */

// Safe session start — serverless environments may not support sessions
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
require_once __DIR__ . '/config/config.php';

use App\Services\CacheService;

// Admin Password Config (Default: ilham123)
$adminPassword = env('ADMIN_PASSWORD', 'ilham123');
$authError = '';
$successMsg = '';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['admin_logged_in']);
    session_destroy();
    header('Location: ' . route_url('/'));
    exit;
}

// Handle Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $passInput = (string)($_POST['password'] ?? '');
    if ($passInput === $adminPassword) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: ' . route_url('admin.php'));
        exit;
    } else {
        $authError = 'Kata sandi salah. Silakan coba lagi.';
    }
}

$isLoggedIn = !empty($_SESSION['admin_logged_in']);

// Paths to local JSON data files
$profileFile = BASE_DIR . '/data/profile.json';
$projectsFile = BASE_DIR . '/data/projects.json';
$mediaFile = BASE_DIR . '/data/media.json';

// Helper functions for reading/writing JSON files
function readJsonFile(string $filePath): array {
    if (!file_exists($filePath)) {
        return [];
    }
    $content = @file_get_contents($filePath);
    if ($content === false) {
        return [];
    }
    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

function writeJsonFile(string $filePath, array $data): bool {
    // Vercel has a read-only filesystem — writes cannot persist
    if (is_vercel()) {
        return false;
    }
    $dir = dirname($filePath);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
        @chmod($dir, 0777);
    }
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    $res = @file_put_contents($filePath, $json) !== false;
    if ($res) {
        @chmod($filePath, 0777);
        // Clear PHP internal file stat cache so next read sees fresh data
        clearstatcache(true, $filePath);
        // Clear all server-side JSON cache files
        (new CacheService(BASE_DIR . '/cache'))->clear();
    }
    return $res;
}

// Process Admin Form Submissions
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // 1. Update Profile & Bio
    if ($action === 'update_profile') {
        $profileData = [
            ['key' => 'name', 'value' => trim((string)($_POST['name'] ?? ''))],
            ['key' => 'role', 'value' => trim((string)($_POST['role'] ?? ''))],
            ['key' => 'tagline', 'value' => trim((string)($_POST['tagline'] ?? ''))],
            ['key' => 'bio', 'value' => trim((string)($_POST['bio'] ?? ''))],
            ['key' => 'email', 'value' => trim((string)($_POST['email'] ?? ''))],
            ['key' => 'whatsapp', 'value' => trim((string)($_POST['whatsapp'] ?? ''))],
            ['key' => 'instagram', 'value' => trim((string)($_POST['instagram'] ?? ''))],
            ['key' => 'youtube', 'value' => trim((string)($_POST['youtube'] ?? ''))],
            ['key' => 'location', 'value' => trim((string)($_POST['location'] ?? ''))],
            ['key' => 'profile_image', 'value' => trim((string)($_POST['profile_image'] ?? ''))],
        ];

        if (writeJsonFile($profileFile, $profileData)) {
            $successMsg = 'Profil dan biografi berhasil diperbarui!';
        } else {
            $authError = 'Gagal menyimpan data profil.';
        }
    }

    // 2. Save / Update Project
    if ($action === 'save_project') {
        $projects = readJsonFile($projectsFile);
        $id = trim((string)($_POST['id'] ?? ''));
        $isNew = empty($id);

        if ($isNew) {
            $id = 'proj-' . time();
        }

        $title = trim((string)($_POST['title'] ?? ''));
        $slug = slugify($title);

        $newProject = [
            'id' => $id,
            'title' => $title,
            'slug' => $slug,
            'category' => strtoupper(trim((string)($_POST['category'] ?? 'CINEMATIC'))),
            'year' => trim((string)($_POST['year'] ?? date('Y'))),
            'client' => trim((string)($_POST['client'] ?? '')),
            'description' => trim((string)($_POST['description'] ?? '')),
            'cover_url' => trim((string)($_POST['cover_url'] ?? '')),
            'featured' => !empty($_POST['featured']),
            'published' => !empty($_POST['published']),
            'order' => (int)($_POST['order'] ?? 99),
        ];

        $found = false;
        foreach ($projects as $idx => $p) {
            if (($p['id'] ?? '') === $id) {
                $projects[$idx] = $newProject;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $projects[] = $newProject;
        }

        usort($projects, fn($a, $b) => ($a['order'] ?? 99) <=> ($b['order'] ?? 99));

        if (writeJsonFile($projectsFile, $projects)) {
            $successMsg = $isNew ? 'Proyek baru berhasil ditambahkan!' : 'Data proyek berhasil diperbarui!';
        } else {
            $authError = 'Gagal menyimpan proyek.';
        }
    }

    // 3. Delete Project
    if ($action === 'delete_project') {
        $id = trim((string)($_POST['id'] ?? ''));
        if (!empty($id)) {
            $projects = readJsonFile($projectsFile);
            $projects = array_values(array_filter($projects, fn($p) => ($p['id'] ?? '') !== $id));
            
            // Also clean up related media items
            $mediaItems = readJsonFile($mediaFile);
            $mediaItems = array_values(array_filter($mediaItems, fn($m) => ($m['project_id'] ?? '') !== $id));
            
            writeJsonFile($mediaFile, $mediaItems);

            if (writeJsonFile($projectsFile, $projects)) {
                $successMsg = 'Proyek berhasil dihapus.';
            } else {
                $authError = 'Gagal menghapus proyek.';
            }
        }
    }

    // 4. Save / Update Media
    if ($action === 'save_media') {
        $mediaItems = readJsonFile($mediaFile);
        $id = trim((string)($_POST['id'] ?? ''));
        $isNew = empty($id);

        if ($isNew) {
            $id = 'med-' . time();
        }

        $newMedia = [
            'id' => $id,
            'project_id' => trim((string)($_POST['project_id'] ?? '')),
            'type' => trim((string)($_POST['type'] ?? 'image')),
            'url' => trim((string)($_POST['url'] ?? '')),
            'thumbnail_url' => trim((string)($_POST['thumbnail_url'] ?? '')),
            'title' => trim((string)($_POST['title'] ?? '')),
            'caption' => trim((string)($_POST['caption'] ?? '')),
            'order' => (int)($_POST['order'] ?? 1),
            'published' => !empty($_POST['published']),
        ];

        $found = false;
        foreach ($mediaItems as $idx => $m) {
            if (($m['id'] ?? '') === $id) {
                $mediaItems[$idx] = $newMedia;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $mediaItems[] = $newMedia;
        }

        usort($mediaItems, fn($a, $b) => ($a['order'] ?? 1) <=> ($b['order'] ?? 1));

        if (writeJsonFile($mediaFile, $mediaItems)) {
            $successMsg = $isNew ? 'Media galeri berhasil ditambahkan!' : 'Media galeri berhasil diperbarui!';
        } else {
            $authError = 'Gagal menyimpan media galeri.';
        }
    }

    // 5. Delete Media
    if ($action === 'delete_media') {
        $id = trim((string)($_POST['id'] ?? ''));
        if (!empty($id)) {
            $mediaItems = readJsonFile($mediaFile);
            $mediaItems = array_values(array_filter($mediaItems, fn($m) => ($m['id'] ?? '') !== $id));

            if (writeJsonFile($mediaFile, $mediaItems)) {
                $successMsg = 'Media galeri berhasil dihapus.';
            } else {
                $authError = 'Gagal menghapus media.';
            }
        }
    }
}

// Load current data for display
$rawProfile = readJsonFile($profileFile);
$profile = new \App\Models\Profile($rawProfile);
$projects = readJsonFile($projectsFile);
$mediaItems = readJsonFile($mediaFile);

$pageTitle = "Admin Dashboard — " . e($profile->name);
require_once __DIR__ . '/includes/header.php';
?>

<style>
.admin-container {
    padding-top: calc(var(--header-height) + 40px);
    padding-bottom: 60px;
    max-width: 1200px;
    margin: 0 auto;
}
.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 1.5rem;
    margin-bottom: 2.5rem;
}
.admin-tabs {
    display: flex;
    gap: 1rem;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 2.5rem;
}
.admin-tab-btn {
    background: none;
    border: none;
    color: var(--text-muted);
    font-family: var(--font-display);
    font-size: 0.9rem;
    font-weight: 700;
    letter-spacing: 0.15em;
    padding: 0.8rem 1.5rem;
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}
.admin-tab-btn.active {
    color: #ffffff;
    border-bottom-color: #ffffff;
}
.admin-card {
    background-color: var(--bg-secondary);
    border: 1px solid var(--border-color);
    padding: 2rem;
    margin-bottom: 2rem;
}
.form-group {
    margin-bottom: 1.5rem;
}
.form-group label {
    display: block;
    font-size: 0.75rem;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin-bottom: 0.5rem;
    font-family: var(--font-display);
}
.form-control {
    width: 100%;
    background-color: var(--bg-main);
    border: 1px solid var(--border-color);
    color: #ffffff;
    padding: 0.8rem 1rem;
    font-family: var(--font-body);
    font-size: 0.95rem;
    box-sizing: border-box;
}
.form-control:focus {
    outline: none;
    border-color: #ffffff;
}
textarea.form-control {
    min-height: 110px;
    resize: vertical;
}
.form-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}
.btn-primary {
    background-color: #ffffff;
    color: #000000;
    border: none;
    padding: 0.9rem 2rem;
    font-family: var(--font-display);
    font-weight: 800;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    font-size: 0.85rem;
    cursor: pointer;
    transition: transform 0.2s ease;
}
.btn-primary:hover {
    transform: translateY(-2px);
}
.btn-danger {
    background-color: #ff4d4d;
    color: #ffffff;
    border: none;
    padding: 0.5rem 1rem;
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 0.75rem;
    cursor: pointer;
}
.alert-success {
    background-color: rgba(76, 175, 80, 0.15);
    border: 1px solid #4caf50;
    color: #4caf50;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    font-size: 0.9rem;
}
.alert-error {
    background-color: rgba(244, 67, 54, 0.15);
    border: 1px solid #f44336;
    color: #f44336;
    padding: 1rem 1.5rem;
    margin-bottom: 2rem;
    font-size: 0.9rem;
}
.table-responsive {
    width: 100%;
    overflow-x: auto;
}
table.admin-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 1rem;
}
table.admin-table th, table.admin-table td {
    padding: 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border-color);
}
table.admin-table th {
    font-family: var(--font-display);
    font-size: 0.75rem;
    letter-spacing: 0.15em;
    color: var(--text-muted);
    text-transform: uppercase;
}
.drive-hint {
    font-size: 0.8rem;
    color: #888;
    margin-top: 0.4rem;
}
</style>

<div class="container admin-container">

    <?php if (!$isLoggedIn): ?>
        <!-- LOGIN MODAL FORM -->
        <div style="max-width: 420px; margin: 4rem auto;" class="admin-card">
            <h2 style="font-family: var(--font-display); font-size: 1.5rem; margin-bottom: 0.5rem; text-align: center;">LOGIN DASHBOARD</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; text-align: center; margin-bottom: 2rem;">Masukan kata sandi untuk mengelola portofolio.</p>

            <?php if (!empty($authError)): ?>
                <div class="alert-error"><?= e($authError); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label>KATA SANDI ADMIN</label>
                    <input type="password" name="password" class="form-control" placeholder="Masukan kata sandi..." required autofocus>
                </div>
                <button type="submit" class="btn-primary" style="width: 100%;">MASUK DASHBOARD &rarr;</button>
            </form>
        </div>

    <?php else: ?>
        <!-- DASHBOARD HEADER -->
        <div class="admin-header">
            <div>
                <span class="mono-tag">MANAGEMENT PANEL</span>
                <h1 style="font-family: var(--font-display); font-size: 2.2rem; margin-top: 0.5rem;">ADMIN DASHBOARD</h1>
            </div>
            <div>
                <a href="<?= route_url('/'); ?>" target="_blank" class="section-link" style="margin-right: 1.5rem;">LIHAT WEBSITE &nearr;</a>
                <a href="<?= route_url('admin.php?action=logout'); ?>" class="btn-danger" style="text-decoration: none; padding: 0.7rem 1.2rem; display: inline-block;">KELUAR</a>
            </div>
        </div>

        <?php if (!empty($successMsg)): ?>
            <div class="alert-success"><?= e($successMsg); ?></div>
        <?php endif; ?>

        <?php if (!empty($authError)): ?>
            <div class="alert-error"><?= e($authError); ?></div>
        <?php endif; ?>

        <?php if (is_vercel()): ?>
            <div class="alert-error" style="border-left: 4px solid #f59e0b; background: rgba(245,158,11,0.1); color: #f59e0b; margin-bottom: 1.5rem;">
                ⚠️ <strong>MODE READ-ONLY (Vercel)</strong> — Perubahan tidak dapat disimpan di Vercel karena filesystem bersifat read-only.
                Untuk mengelola konten, edit file <code>data/profile.json</code>, <code>data/projects.json</code>, dan <code>data/media.json</code> langsung di repository GitHub, lalu redeploy.
            </div>
        <?php endif; ?>


        <!-- TABS NAVIGATION -->
        <div class="admin-tabs">
            <button class="admin-tab-btn active" onclick="showTab('tab-profile', this)">PROFIL SAYA</button>
            <button class="admin-tab-btn" onclick="showTab('tab-projects', this)">PROYEK KARYA</button>
            <button class="admin-tab-btn" onclick="showTab('tab-media', this)">GALERI MEDIA</button>
        </div>

        <!-- TAB 1: PROFIL & BIO -->
        <div id="tab-profile" class="tab-content">
            <div class="admin-card">
                <h3 style="font-family: var(--font-display); margin-bottom: 1.5rem; font-size: 1.2rem;">KELOLA PROFIL & BIOGRAFI</h3>
                <form method="POST" action="">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="form-row">
                        <div class="form-group">
                            <label>NAMA LENGKAP</label>
                            <input type="text" name="name" class="form-control" value="<?= e($profile->name); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>PERAN PROFESIONAL</label>
                            <input type="text" name="role" class="form-control" value="<?= e($profile->role); ?>" required placeholder="Contoh: VIDEOGRAFER & EDITOR">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>SLOGAN / TAGLINE</label>
                        <input type="text" name="tagline" class="form-control" value="<?= e($profile->tagline); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>TENTANG SAYA / BIOGRAFI</label>
                        <textarea name="bio" class="form-control" required><?= e($profile->bio); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label>URL FOTO PROFIL (LINK GOOGLE DRIVE)</label>
                        <input type="text" name="profile_image" id="profile_image" class="form-control" value="<?= e($profile->profileImage); ?>" placeholder="Paste link Google Drive foto profil di sini...">
                        <div class="drive-hint">💡 Cukup salin link Google Drive publik Anda (Contoh: https://drive.google.com/file/d/1TeWRL7UjjyuN9T8Rds_E2QC6-qsSwxrS/view?usp=drive_link). Sistem akan mengonversinya secara otomatis.</div>
                        <div style="margin-top: 0.8rem;">
                            <img id="profile_image_preview" src="<?= e(\App\Services\GoogleDriveService::getDriveImageUrl($profile->profileImage)); ?>" style="max-width: 150px; max-height: 180px; object-fit: cover; border: 1px solid var(--border-color); display: <?= !empty($profile->profileImage) ? 'block' : 'none'; ?>;" referrerpolicy="no-referrer">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>EMAIL KONTAK</label>
                            <input type="email" name="email" class="form-control" value="<?= e($profile->email); ?>">
                        </div>
                        <div class="form-group">
                            <label>LINK WHATSAPP</label>
                            <input type="text" name="whatsapp" class="form-control" value="<?= e($profile->whatsapp); ?>" placeholder="https://wa.me/628xxxxxxxxxx">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>LINK INSTAGRAM</label>
                            <input type="text" name="instagram" class="form-control" value="<?= e($profile->instagram); ?>">
                        </div>
                        <div class="form-group">
                            <label>LINK YOUTUBE</label>
                            <input type="text" name="youtube" class="form-control" value="<?= e($profile->youtube); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>LOKASI DOMISILI</label>
                        <input type="text" name="location" class="form-control" value="<?= e($profile->location); ?>">
                    </div>

                    <button type="submit" class="btn-primary">SIMPAN PROFIL &rarr;</button>
                </form>
            </div>
        </div>

        <!-- TAB 2: PROYEK KARYA -->
        <div id="tab-projects" class="tab-content" style="display: none;">
            <!-- FORM TAMBAH / EDIT PROYEK -->
            <div class="admin-card">
                <h3 id="project-form-title" style="font-family: var(--font-display); margin-bottom: 1.5rem; font-size: 1.2rem;">TAMBAH PROYEK BARU</h3>
                <form method="POST" action="" id="project-form">
                    <input type="hidden" name="action" value="save_project">
                    <input type="hidden" name="id" id="project_id" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label>JUDUL PROYEK</label>
                            <input type="text" name="title" id="project_title" class="form-control" required placeholder="Contoh: Gema Senja — Short Film">
                        </div>
                        <div class="form-group">
                            <label>KATEGORI</label>
                            <select name="category" id="project_category" class="form-control" required>
                                <option value="CINEMATIC">CINEMATIC</option>
                                <option value="DOKUMENTASI">DOKUMENTASI</option>
                                <option value="BRAND CONTENT">BRAND CONTENT</option>
                                <option value="CAMPAIGN">CAMPAIGN</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>TAHUN PRODUKSI</label>
                            <input type="text" name="year" id="project_year" class="form-control" value="<?= date('Y'); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>NAMA KLIEN</label>
                            <input type="text" name="client" id="project_client" class="form-control" placeholder="Contoh: Brand / Perorangan / Independen">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>DESKRIPSI PROYEK</label>
                        <textarea name="description" id="project_description" class="form-control" required placeholder="Jelaskan mengenai proyek ini..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>URL COVER PROYEK (LINK GOOGLE DRIVE)</label>
                        <input type="text" name="cover_url" id="project_cover_url" class="form-control" required placeholder="Paste link Google Drive cover foto/video di sini...">
                        <div class="drive-hint">💡 Salin link Google Drive gambar/video cover proyek. Contoh: https://drive.google.com/file/d/FILE_ID/view</div>
                        <div style="margin-top: 0.8rem;">
                            <img id="project_cover_url_preview" src="" style="max-width: 200px; max-height: 120px; object-fit: cover; border: 1px solid var(--border-color); display: none;" referrerpolicy="no-referrer">
                        </div>
                    </div>

                    <div class="form-row" style="align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="featured" id="project_featured" value="1" checked>
                                <span>TAMPILKAN DI BERANDA (UNGGULAN)</span>
                            </label>
                        </div>
                        <div>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="published" id="project_published" value="1" checked>
                                <span>PUBLIKASIKAN (AKTIF)</span>
                            </label>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>URUTAN TAMPILAN</label>
                            <input type="number" name="order" id="project_order" class="form-control" value="1" style="width: 100px;">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="project-submit-btn">TAMBAH PROYEK &rarr;</button>
                    <button type="button" onclick="resetProjectForm()" class="btn-danger" style="margin-left: 1rem; background: #444;">BATAL / RESET</button>
                </form>
            </div>

            <!-- DAFTAR PROYEK TABLE -->
            <div class="admin-card">
                <h3 style="font-family: var(--font-display); margin-bottom: 1rem; font-size: 1.2rem;">DAFTAR PROYEK</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>COVER</th>
                                <th>JUDUL PROYEK</th>
                                <th>KATEGORI</th>
                                <th>TAHUN</th>
                                <th>UNGGULAN</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $p): ?>
                                <tr>
                                    <td style="width: 80px;">
                                        <img src="<?= e(\App\Services\GoogleDriveService::getDriveImageUrl($p['cover_url'] ?? '')); ?>" style="width: 60px; height: 40px; object-fit: cover;" referrerpolicy="no-referrer">
                                    </td>
                                    <td>
                                        <strong><?= e($p['title'] ?? ''); ?></strong><br>
                                        <small style="color: var(--text-muted);"><?= e($p['client'] ?? ''); ?></small>
                                    </td>
                                    <td><?= e($p['category'] ?? ''); ?></td>
                                    <td><?= e($p['year'] ?? ''); ?></td>
                                    <td><?= !empty($p['featured']) ? '✅ Ya' : 'Tidak'; ?></td>
                                    <td>
                                        <button class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;" onclick='editProject(<?= json_encode($p); ?>)'>EDIT</button>
                                        <form method="POST" action="" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');">
                                            <input type="hidden" name="action" value="delete_project">
                                            <input type="hidden" name="id" value="<?= e($p['id'] ?? ''); ?>">
                                            <button type="submit" class="btn-danger">HAPUS</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- TAB 3: GALERI MEDIA -->
        <div id="tab-media" class="tab-content" style="display: none;">
            <div class="admin-card">
                <h3 id="media-form-title" style="font-family: var(--font-display); margin-bottom: 1.5rem; font-size: 1.2rem;">TAMBAH FOTO / VIDEO KE GALERI PROYEK</h3>
                <form method="POST" action="" id="media-form">
                    <input type="hidden" name="action" value="save_media">
                    <input type="hidden" name="id" id="media_id" value="">

                    <div class="form-row">
                        <div class="form-group">
                            <label>PILIH PROYEK</label>
                            <select name="project_id" id="media_project_id" class="form-control" required>
                                <option value="">-- Pilih Proyek --</option>
                                <?php foreach ($projects as $p): ?>
                                    <option value="<?= e($p['id'] ?? ''); ?>"><?= e($p['title'] ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>TIPE MEDIA</label>
                            <select name="type" id="media_type" class="form-control" required>
                                <option value="image">FOTO (Gambar)</option>
                                <option value="video">VIDEO (.mp4 / Drive Player)</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>URL FOTO / VIDEO (LINK GOOGLE DRIVE)</label>
                        <input type="text" name="url" id="media_url" class="form-control" required placeholder="Paste link Google Drive foto atau video di sini...">
                        <div class="drive-hint">💡 Salin link Google Drive file media Anda.</div>
                        <div style="margin-top: 0.8rem;">
                            <img id="media_url_preview" src="" style="max-width: 200px; max-height: 120px; object-fit: cover; border: 1px solid var(--border-color); display: none;" referrerpolicy="no-referrer">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>URL THUMBNAIL VIDEO (OPSIONAL - KHUSUS VIDEO)</label>
                        <input type="text" name="thumbnail_url" id="media_thumbnail_url" class="form-control" placeholder="Link Drive poster thumbnail video jika ada...">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>JUDUL MEDIA</label>
                            <input type="text" name="title" id="media_title" class="form-control" placeholder="Contoh: Still Frame 01">
                        </div>
                        <div class="form-group">
                            <label>CAPTION / KETERANGAN</label>
                            <input type="text" name="caption" id="media_caption" class="form-control" placeholder="Keterangan singkat bingkai foto/video...">
                        </div>
                    </div>

                    <div class="form-row" style="align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                                <input type="checkbox" name="published" id="media_published" value="1" checked>
                                <span>PUBLIKASIKAN (TAMPIL DI GALERI)</span>
                            </label>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>URUTAN</label>
                            <input type="number" name="order" id="media_order" class="form-control" value="1" style="width: 100px;">
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" id="media-submit-btn">SIMPAN MEDIA &rarr;</button>
                    <button type="button" onclick="resetMediaForm()" class="btn-danger" style="margin-left: 1rem; background: #444;">BATAL / RESET</button>
                </form>
            </div>

            <!-- DAFTAR MEDIA TABLE -->
            <div class="admin-card">
                <h3 style="font-family: var(--font-display); margin-bottom: 1rem; font-size: 1.2rem;">DAFTAR GALERI MEDIA</h3>
                <div class="table-responsive">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>PREVIEW</th>
                                <th>TIPE</th>
                                <th>JUDUL & CAPTION</th>
                                <th>PROYEK</th>
                                <th>AKSI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mediaItems as $m): 
                                $projTitle = '—';
                                foreach ($projects as $p) {
                                    if (($p['id'] ?? '') === ($m['project_id'] ?? '')) {
                                        $projTitle = $p['title'] ?? '';
                                        break;
                                    }
                                }
                            ?>
                                <tr>
                                    <td style="width: 80px;">
                                        <img src="<?= e(\App\Services\GoogleDriveService::getDriveImageUrl($m['url'] ?? '')); ?>" style="width: 60px; height: 40px; object-fit: cover;" referrerpolicy="no-referrer">
                                    </td>
                                    <td><?= strtoupper(e($m['type'] ?? 'IMAGE')); ?></td>
                                    <td>
                                        <strong><?= e($m['title'] ?? 'Tanpa Judul'); ?></strong><br>
                                        <small style="color: var(--text-muted);"><?= e($m['caption'] ?? ''); ?></small>
                                    </td>
                                    <td><?= e($projTitle); ?></td>
                                    <td>
                                        <button class="btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.75rem;" onclick='editMedia(<?= json_encode($m); ?>)'>EDIT</button>
                                        <form method="POST" action="" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus media ini?');">
                                            <input type="hidden" name="action" value="delete_media">
                                            <input type="hidden" name="id" value="<?= e($m['id'] ?? ''); ?>">
                                            <button type="submit" class="btn-danger">HAPUS</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<script>
function showTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.admin-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    if (btn) btn.classList.add('active');
}

function editProject(p) {
    document.getElementById('project-form-title').textContent = 'EDIT PROYEK: ' + p.title;
    document.getElementById('project_id').value = p.id || '';
    document.getElementById('project_title').value = p.title || '';
    document.getElementById('project_category').value = p.category || 'CINEMATIC';
    document.getElementById('project_year').value = p.year || '';
    document.getElementById('project_client').value = p.client || '';
    document.getElementById('project_description').value = p.description || '';
    document.getElementById('project_cover_url').value = p.cover_url || '';
    document.getElementById('project_featured').checked = !!p.featured;
    document.getElementById('project_published').checked = !!p.published;
    document.getElementById('project_order').value = p.order || 1;
    document.getElementById('project-submit-btn').textContent = 'UPDATE PROYEK \u2192';
    // Update preview gambar cover
    updateDrivePreview('project_cover_url', 'project_cover_url_preview');
    window.scrollTo({ top: document.getElementById('project-form').offsetTop - 100, behavior: 'smooth' });
}

function resetProjectForm() {
    document.getElementById('project-form-title').textContent = 'TAMBAH PROYEK BARU';
    document.getElementById('project-form').reset();
    document.getElementById('project_id').value = '';
    document.getElementById('project-submit-btn').textContent = 'TAMBAH PROYEK \u2192';
}

function editMedia(m) {
    document.getElementById('media-form-title').textContent = 'EDIT MEDIA: ' + (m.title || 'Galeri');
    document.getElementById('media_id').value = m.id || '';
    document.getElementById('media_project_id').value = m.project_id || '';
    document.getElementById('media_type').value = m.type || 'image';
    document.getElementById('media_url').value = m.url || '';
    document.getElementById('media_thumbnail_url').value = m.thumbnail_url || '';
    document.getElementById('media_title').value = m.title || '';
    document.getElementById('media_caption').value = m.caption || '';
    document.getElementById('media_published').checked = !!m.published;
    document.getElementById('media_order').value = m.order || 1;
    document.getElementById('media-submit-btn').textContent = 'UPDATE MEDIA \u2192';
    // Update preview gambar/video media
    updateDrivePreview('media_url', 'media_url_preview');
    window.scrollTo({ top: document.getElementById('media-form').offsetTop - 100, behavior: 'smooth' });
}

function resetMediaForm() {
    document.getElementById('media-form-title').textContent = 'TAMBAH FOTO / VIDEO KE GALERI PROYEK';
    document.getElementById('media-form').reset();
    document.getElementById('media_id').value = '';
    document.getElementById('media-submit-btn').textContent = 'SIMPAN MEDIA \u2192';
    updateDrivePreview('media_url', 'media_url_preview');
}

function extractDriveId(url) {
    if (!url) return null;
    let m = url.match(/\/file\/d\/([a-zA-Z0-9_-]{20,})/i) ||
            url.match(/[?&]id=([a-zA-Z0-9_-]{20,})/i) ||
            url.match(/\/d\/([a-zA-Z0-9_-]{20,})/i) ||
            url.match(/\/folders\/([a-zA-Z0-9_-]{20,})/i) ||
            url.match(/^([a-zA-Z0-9_-]{20,})$/);
    return m ? m[1] : null;
}

function updateDrivePreview(inputId, previewImgId) {
    const input = document.getElementById(inputId) || document.querySelector('[name="' + inputId + '"]');
    const img = document.getElementById(previewImgId);
    if (!input || !img) return;

    const val = input.value.trim();
    const driveId = extractDriveId(val);

    if (driveId) {
        img.src = 'https://lh3.googleusercontent.com/d/' + driveId + '=w800';
        img.style.display = 'block';
    } else if (val.startsWith('http')) {
        img.src = val;
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    ['profile_image', 'project_cover_url', 'media_url'].forEach(id => {
        const input = document.getElementById(id) || document.querySelector('[name="' + id + '"]');
        if (input) {
            updateDrivePreview(id, id + '_preview');
            input.addEventListener('input', () => updateDrivePreview(id, id + '_preview'));
            input.addEventListener('paste', () => setTimeout(() => updateDrivePreview(id, id + '_preview'), 50));
        }
    });
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
