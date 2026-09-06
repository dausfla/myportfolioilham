# Portfolio Website — Ilham Ramadhan Setiawan
**Videographer & Photographer**

A bespoke, high-end, monochrome portfolio application built with native **PHP 8.2+**, using **Google Sheets** as a headless CMS, **Google Drive** for media hosting, **GSAP** motion design, and a minimalist editorial visual layout.

---

## Technical Specifications & Features

- **Core Application**: PHP 8.2+ (No React/Vue/Node backend overhead; pure high-performance server rendering)
- **CMS Integration**: Google Sheets API as Source of Truth
- **Spreadsheet ID**: `1BW3oAgh8EtTBTalwIzRGISxh5nu0VFFTI3BmIHAfVus`
- **Server Caching**: Automatic JSON caching with 300s TTL + stale cache fallback
- **Design System**: Monochromatic palette (`#050505`, `#FFFFFF`, `#808080`), oversized display typography (`Syne` / `Cinzel`), custom magnetic follower cursor
- **Media Experience**: Dynamic HTML5 video player + responsive photo gallery with keyboard-navigable lightbox (`ESC`, `←`, `→`)
- **SEO & Security**: Full Open Graph metadata, canonical links, semantic HTML5, zero credentials exposed to client browser

---

## Folder Structure

```
ilham-ramadhan-portfolio/
├── .htaccess                  # Apache clean URL rewrite rules
├── .env                       # Active environment variables
├── .env.example               # Environment variables template
├── .gitignore                 # Sensitive credentials & cache ignore
├── composer.json              # Dependency declaration
├── index.php                  # Homepage controller
├── work.php                   # Portfolio listing & category filters
├── project.php                # Dynamic project detail & media stack
├── about.php                  # Biography & Capabilities page
├── contact.php                # Contact page & booking triggers
├── 404.php                    # Custom monochrome error view
│
├── config/
│   └── config.php             # System configuration & env parser
│
├── services/
│   ├── CacheService.php       # JSON Cache engine (300s TTL + fallback)
│   ├── GoogleDriveService.php # Drive URL normalizer & direct image conversion
│   └── GoogleSheetsService.php# Sheets CMS data reader & parser
│
├── models/
│   ├── Project.php            # Project entity model
│   ├── Media.php              # Media item entity model
│   └── Profile.php            # Artist profile entity model
│
├── includes/
│   ├── header.php             # Head meta & preloader template
│   ├── navigation.php         # Desktop nav header & mobile overlay
│   ├── footer.php             # Site footer & lightbox modal
│   └── helpers.php            # Security & URL helper utilities
│
├── assets/
│   ├── css/
│   │   ├── style.css          # Design tokens, typography & grid
│   │   └── responsive.css     # Mobile & tablet breakpoints
│   └── js/
│       ├── cursor.js          # Custom smooth follower cursor
│       ├── animation.js       # Motion reveals & header scroll states
│       ├── gallery.js         # Video player & photo lightbox modal
│       └── main.js            # App bootstrapper & category filter
│
└── cache/                     # Server cache & fallback seed JSON files
    ├── projects.json
    ├── media.json
    └── profile.json
```

---

## Google Sheets CMS Structure

Your Google Spreadsheet (`1BW3oAgh8EtTBTalwIzRGISxh5nu0VFFTI3BmIHAfVus`) must have 3 worksheets with the exact column headers below:

### 1. SHEET: `PROJECTS`

| Column | Description | Example |
| :--- | :--- | :--- |
| `id` | Unique ID | `P001` |
| `title` | Project title | `Wedding Film Bandung` |
| `slug` | URL slug | `wedding-film-bandung` |
| `category` | `VIDEO`, `PHOTOGRAPHY`, `COMMERCIAL`, `PERSONAL` | `VIDEO` |
| `year` | Release year | `2026` |
| `client` | Client name | `Private Client` |
| `description` | Full project description text | `Cinematic 35mm wedding film...` |
| `cover_url` | Google Drive image link or direct URL | `https://drive.google.com/file/d/...` |
| `featured` | `TRUE` or `FALSE` | `TRUE` |
| `published` | `TRUE` or `FALSE` | `TRUE` |
| `order` | Display sorting priority | `1` |

### 2. SHEET: `MEDIA`

| Column | Description | Example |
| :--- | :--- | :--- |
| `id` | Unique Media ID | `M001` |
| `project_id` | Foreign Key matching `PROJECTS.id` | `P001` |
| `type` | `image` or `video` | `video` |
| `title` | Frame title | `Highlight Reel` |
| `url` | Direct MP4 link, YouTube, or Google Drive URL | `https://.../video.mp4` |
| `thumbnail_url` | Video poster image or Drive thumbnail | `https://drive.google.com/file/d/...` |
| `caption` | Caption / technical specs | `Shot on 35mm prime lens.` |
| `order` | Display priority | `1` |
| `published` | `TRUE` or `FALSE` | `TRUE` |

### 3. SHEET: `PROFILE`

| Key | Example Value |
| :--- | :--- |
| `name` | `Ilham Ramadhan Setiawan` |
| `role` | `VIDEOGRAPHER & PHOTOGRAPHER` |
| `tagline` | `Visual stories captured through light, movement and emotion.` |
| `bio` | `Filmmaker and photographer based in Indonesia...` |
| `email` | `hello@ilhamramadhan.com` |
| `instagram` | `https://instagram.com/ilhamramadhan` |
| `youtube` | `https://youtube.com/@ilhamramadhan` |
| `whatsapp` | `https://wa.me/6280000000000` |
| `location` | `Indonesia` |
| `profile_image` | `https://drive.google.com/file/d/...` |

---

## Setup & Local Development Instructions

### 1. Requirements
- PHP 8.2 or higher
- Composer
- Web Server (Apache with `mod_rewrite` enabled or Nginx or PHP Built-in Server)

### 2. Install Dependencies
Run Composer to install dependencies:
```bash
composer install
```

### 3. Configure Environment Variables
Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```
Ensure `GOOGLE_SPREADSHEET_ID` is set:
```ini
GOOGLE_SPREADSHEET_ID="1BW3oAgh8EtTBTalwIzRGISxh5nu0VFFTI3BmIHAfVus"
```

### 4. Run Local Server
Start the PHP development server inside the project root:
```bash
php -S localhost:8000
```
Open your browser at `http://localhost:8000`.

---

## Google Sheets Permission Setup

To allow your PHP application to fetch data live from your Google Sheet:
1. Open your Google Spreadsheet in a web browser.
2. Click **Share** at the top right.
3. Change General Access to **"Anyone with the link can view"**.
4. The application will automatically stream data via the public CSV endpoint and cache it locally in `cache/` for 300 seconds.

---

## Deployment (Apache / cPanel / Vercel PHP / VPS)

1. Upload all project files to your server document root.
2. Make sure the `cache/` folder is writable by the web server user (`chmod 755 cache`).
3. Verify that Apache `mod_rewrite` is enabled so `.htaccess` can serve clean URLs (`/work`, `/project/wedding-film-bandung`).
