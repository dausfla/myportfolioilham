<?php
declare(strict_types=1);

/**
 * Cloudflare Pages Static Exporter
 * Ilham Ramadhan Setiawan Portfolio
 * 
 * Run via CLI: php build.php
 */

define('STATIC_BUILD', true);

require_once __DIR__ . '/config/config.php';

use App\Services\GoogleSheetsService;

$distDir = __DIR__ . '/dist';

echo "=============================================\n";
echo " Building Portfolio for Cloudflare Pages...\n";
echo "=============================================\n\n";

// Helper: render PHP file in isolated subprocess so require_once works for every page
function renderPage(string $file, array $getParams = [], string $uri = '/'): string {
    $phpBinary = PHP_BINARY;
    if (!file_exists($phpBinary) || str_contains($phpBinary, 'Herd')) {
        if (file_exists('/Applications/XAMPP/xamppfiles/bin/php')) {
            $phpBinary = '/Applications/XAMPP/xamppfiles/bin/php';
        } else {
            $phpBinary = 'php';
        }
    }

    $code = sprintf(
        'define("STATIC_BUILD", true); $_GET = %s; $_SERVER["HTTP_HOST"] = "localhost"; $_SERVER["REQUEST_URI"] = %s; require %s;',
        var_export($getParams, true),
        var_export($uri, true),
        var_export($file, true)
    );

    $cmd = sprintf('%s -r %s', escapeshellarg($phpBinary), escapeshellarg($code));
    $descriptors = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $process = proc_open($cmd, $descriptors, $pipes, __DIR__);
    if (is_resource($process)) {
        $stdout = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
        return $stdout;
    }

    return '';
}

// Helper: write content to dist/ directory
function writeDistFile(string $distDir, string $relativeFilePath, string $content): void {
    $targetPath = $distDir . '/' . ltrim($relativeFilePath, '/');
    $dir = dirname($targetPath);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($targetPath, $content);
    echo "  [✓] Generated: dist/" . ltrim($relativeFilePath, '/') . "\n";
}

// Helper: copy directory recursively
function copyDir(string $src, string $dst): void {
    $dir = opendir($src);
    if (!$dir) return;
    @mkdir($dst, 0755, true);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                copyDir($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Clean old dist if exists
if (is_dir($distDir)) {
    echo "Cleaning existing dist directory...\n";
    // System command or php recursive delete
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($distDir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );
    foreach ($files as $fileinfo) {
        $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
        $todo($fileinfo->getRealPath());
    }
}

// 1. Render Main Pages
echo "Rendering main pages...\n";

// Homepage
writeDistFile($distDir, 'index.html', renderPage(__DIR__ . '/index.php'));

// Work Page
writeDistFile($distDir, 'work/index.html', renderPage(__DIR__ . '/work.php'));

// About Page
writeDistFile($distDir, 'about/index.html', renderPage(__DIR__ . '/about.php'));

// Contact Page
writeDistFile($distDir, 'contact/index.html', renderPage(__DIR__ . '/contact.php'));

// 404 Page
writeDistFile($distDir, '404.html', renderPage(__DIR__ . '/404.php'));

// Admin Dashboard CMS Page
writeDistFile($distDir, 'admin/index.html', renderPage(__DIR__ . '/admin.php', [], '/admin/'));


// 2. Render Dynamic Project Detail Pages from Google Sheets
echo "\nRendering dynamic project pages...\n";
try {
    $sheetsService = new GoogleSheetsService();
    $projects = $sheetsService->getProjects();

    echo "Found " . count($projects) . " project(s) from CMS.\n";

    foreach ($projects as $proj) {
        if (empty($proj->slug)) {
            continue;
        }
        $html = renderPage(__DIR__ . '/project.php', ['slug' => $proj->slug]);
        writeDistFile($distDir, "project/{$proj->slug}/index.html", $html);
    }
} catch (\Throwable $e) {
    echo "  [!] Warning while fetching projects: " . $e->getMessage() . "\n";
}


// 3. Copy Assets
echo "\nCopying static assets...\n";
if (is_dir(__DIR__ . '/assets')) {
    copyDir(__DIR__ . '/assets', $distDir . '/assets');
    echo "  [✓] Copied assets/ -> dist/assets/\n";
}

// 4. Create .nojekyll
file_put_contents($distDir . '/.nojekyll', '');
echo "  [✓] Created dist/.nojekyll\n";

echo "\n=============================================\n";
echo " BUILD SUCCESSFUL! \n";
echo " Output Directory: {$distDir}\n";
echo " You can now upload the 'dist' folder to Cloudflare Pages!\n";
echo "=============================================\n";
