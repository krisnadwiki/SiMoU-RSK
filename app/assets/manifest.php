<?php
/**
 * manifest.php — Dynamic PWA Manifest
 * Served as application/manifest+json, inject APP_URL dinamis.
 */
require_once __DIR__ . '/../config/app.php';

header('Content-Type: application/manifest+json');
header('Cache-Control: public, max-age=86400');

$base = rtrim(APP_URL, '/');

$manifest = [
    'name'             => APP_NAME,
    'short_name'       => APP_SHORT,
    'description'      => 'Sistem Informasi MoU & MoA RSUD Kilisuci Kota Kediri',
    'start_url'        => $base . '/admin',
    'scope'            => $base . '/',
    'display'          => 'standalone',
    'orientation'      => 'portrait-primary',
    'background_color' => '#0d2635',
    'theme_color'      => '#0a7ea4',
    'lang'             => 'id',
    'categories'       => ['productivity', 'medical'],
    'icons'            => [
        [
            'src'     => $base . '/assets/image/icon-192.png',
            'sizes'   => '192x192',
            'type'    => 'image/png',
            'purpose' => 'any maskable',
        ],
        [
            'src'     => $base . '/assets/image/icon-512.png',
            'sizes'   => '512x512',
            'type'    => 'image/png',
            'purpose' => 'any maskable',
        ],
    ],
    'shortcuts' => [
        [
            'name'        => 'Daftar MoU',
            'short_name'  => 'MoU',
            'description' => 'Buka daftar dokumen MoU / MoA',
            'url'         => $base . '/admin/mou',
            'icons'       => [['src' => $base . '/assets/image/icon-192.png', 'sizes' => '192x192']],
        ],
        [
            'name'        => 'Tambah MoU',
            'short_name'  => 'Tambah',
            'description' => 'Tambah dokumen MoU baru',
            'url'         => $base . '/admin/mou/create',
            'icons'       => [['src' => $base . '/assets/image/icon-192.png', 'sizes' => '192x192']],
        ],
    ],
];

echo json_encode($manifest, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
