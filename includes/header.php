<?php
declare(strict_types=1);

/**
 * Header & HTML Head Template
 * Ilham Ramadhan Setiawan Portfolio
 */

$pageTitle = $pageTitle ?? 'Ilham Ramadhan Setiawan — Videografer & Editor';
$metaDescription = $metaDescription ?? 'Portofolio Ilham Ramadhan Setiawan, seorang videografer dan editor yang berfokus pada pembuatan visual yang kuat, emosional, dan bercerita.';
$ogImage = $ogImage ?? asset_url('assets/images/og-cover.jpg');
$canonicalUrl = $canonicalUrl ?? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- Primary Meta Tags -->
    <title><?= e($pageTitle); ?></title>
    <meta name="title" content="<?= e($pageTitle); ?>">
    <meta name="description" content="<?= e($metaDescription); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= e($canonicalUrl); ?>">

    <!-- Open Graph / Facebook / WhatsApp -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e($canonicalUrl); ?>">
    <meta property="og:title" content="<?= e($pageTitle); ?>">
    <meta property="og:description" content="<?= e($metaDescription); ?>">
    <meta property="og:image" content="<?= e($ogImage); ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= e($canonicalUrl); ?>">
    <meta property="twitter:title" content="<?= e($pageTitle); ?>">
    <meta property="twitter:description" content="<?= e($metaDescription); ?>">
    <meta property="twitter:image" content="<?= e($ogImage); ?>">

    <!-- Preconnect Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Style Sheets -->
    <link rel="stylesheet" href="<?= asset_url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?= asset_url('assets/css/responsive.css'); ?>">
</head>
<body>

    <!-- Custom Smooth Trailing Cursor -->
    <div id="custom-cursor" class="custom-cursor">
        <span id="cursor-text" class="custom-cursor-text"></span>
    </div>

    <!-- Minimal Brand Preloader -->
    <div id="preloader">
        <div class="preloader-title">ILHAM RAMADHAN SETIAWAN</div>
        <div class="preloader-subtitle">VIDEOGRAFER / EDITOR</div>
        <div class="preloader-bar">
            <div id="preloader-progress" class="preloader-progress"></div>
        </div>
    </div>
