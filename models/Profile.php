<?php
declare(strict_types=1);

namespace App\Models;

/**
 * Profile Data Model for Ilham Ramadhan Setiawan
 */
class Profile {
    public string $name = 'Ilham Ramadhan Setiawan';
    public string $role = 'VIDEOGRAFER & EDITOR';
    public string $tagline = 'Membuat Visual yang Kuat, Emosional, dan Bercerita.';
    public string $bio = 'Saya adalah seorang videografer dan editor yang berfokus pada pembuatan visual yang kuat, emosional, dan bercerita. Berpengalaman dalam proses produksi mulai dari perencanaan konsep, pengambilan gambar, hingga editing akhir. Saya terbiasa bekerja dengan berbagai jenis proyek seperti, cinematic, dokumentasi, brand content, dan social media campaign';
    public string $email = 'contact@ilhamramadhan.com';
    public string $instagram = 'https://instagram.com';
    public string $youtube = 'https://youtube.com';
    public string $whatsapp = 'https://wa.me/6289626376804';
    public string $location = 'Bandung / Jakarta, Indonesia';
    public string $profileImage = 'https://drive.google.com/file/d/1TeWRL7UjjyuN9T8Rds_E2QC6-qsSwxrS/view?usp=drive_link';

    public function __construct(array $keyValues = []) {
        foreach ($keyValues as $row) {
            $key = $row['key'] ?? $row[0] ?? null;
            $val = $row['value'] ?? $row[1] ?? null;
            
            if (!$key) continue;
            
            $v = trim((string)$val);
            if (empty($v) || str_starts_with($v, '[')) continue;
            
            switch (strtolower((string)$key)) {
                case 'name':
                    $this->name = $v;
                    break;
                case 'role':
                    if (str_contains(strtolower($v), 'photographer') || str_contains(strtolower($v), 'videographer & photographer')) {
                        $this->role = 'VIDEOGRAFER & EDITOR';
                    } else {
                        $this->role = $v;
                    }
                    break;
                case 'tagline':
                    if (str_contains(strtolower($v), 'visual stories') || str_contains(strtolower($v), 'captured with intention')) {
                        $this->tagline = 'Membuat Visual yang Kuat, Emosional, dan Bercerita.';
                    } else {
                        $this->tagline = $v;
                    }
                    break;
                case 'bio':
                    if (str_contains(strtolower($v), 'videographer and photographer') || str_contains(strtolower($v), 'cinematic visual storytelling')) {
                        $this->bio = 'Saya adalah seorang videografer dan editor yang berfokus pada pembuatan visual yang kuat, emosional, dan bercerita. Berpengalaman dalam proses produksi mulai dari perencanaan konsep, pengambilan gambar, hingga editing akhir. Saya terbiasa bekerja dengan berbagai jenis proyek seperti, cinematic, dokumentasi, brand content, dan social media campaign';
                    } else {
                        $this->bio = $v;
                    }
                    break;
                case 'email':
                    if (str_contains($v, '@')) $this->email = $v;
                    break;
                case 'instagram':
                    if (!str_contains($v, 'INSTAGRAM_URL')) $this->instagram = $v;
                    break;
                case 'youtube':
                    if (!str_contains($v, 'YOUTUBE_URL')) $this->youtube = $v;
                    break;
                case 'whatsapp':
                    if (!empty($v)) $this->whatsapp = $v;
                    break;
                case 'location':
                    if (strtoupper($v) === 'INDONESIA' || empty($v)) {
                        $this->location = 'Bandung / Jakarta, Indonesia';
                    } else {
                        $this->location = $v;
                    }
                    break;
                case 'profile_image':
                case 'profileimage':
                    if (!str_contains($v, 'PASTE_FILE_URL') && !empty($v)) {
                        $this->profileImage = $v;
                    }
                    break;
            }
        }
    }

    public function toArray(): array {
        return [
            'name' => $this->name,
            'role' => $this->role,
            'tagline' => $this->tagline,
            'bio' => $this->bio,
            'email' => $this->email,
            'instagram' => $this->instagram,
            'youtube' => $this->youtube,
            'whatsapp' => $this->whatsapp,
            'location' => $this->location,
            'profile_image' => $this->profileImage,
        ];
    }
}
