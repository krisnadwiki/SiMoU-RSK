<?php
/**
 * upload.php — File Upload Handler
 * PDF upload dengan validasi MIME, size, dan safe filename
 */

/**
 * Handle upload file MoU / MoA dokumen PDF.
 *
 * @param  array  $file   $_FILES['field_name']
 * @param  string $prefix Prefix nama file (mis. 'mou', 'renewal')
 * @return array{success:bool, path?:string, error?:string}
 */
function handle_document_upload(array $file, string $prefix = 'mou'): array
{
    $maxBytes = UPLOAD_MAX_MB * 1024 * 1024;

    // Cek error upload bawaan PHP
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE   => 'Ukuran file melebihi batas server.',
            UPLOAD_ERR_FORM_SIZE  => 'Ukuran file melebihi batas form.',
            UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian.',
            UPLOAD_ERR_NO_FILE    => 'Tidak ada file yang dipilih.',
            UPLOAD_ERR_NO_TMP_DIR => 'Direktori sementara tidak ditemukan.',
            UPLOAD_ERR_CANT_WRITE => 'Gagal menulis file ke disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload dihentikan oleh ekstensi PHP.',
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Error upload tidak diketahui.'];
    }

    // Validasi ukuran
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'Ukuran file maksimal ' . UPLOAD_MAX_MB . ' MB.'];
    }

    // Validasi MIME type (baca magic bytes, jangan percaya Content-Type)
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    $allowed = ['application/pdf', 'application/x-pdf'];
    if (!in_array($mimeType, $allowed, true)) {
        return ['success' => false, 'error' => 'Hanya file PDF yang diizinkan. Tipe terdeteksi: ' . $mimeType];
    }

    // Validasi ekstensi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'pdf') {
        return ['success' => false, 'error' => 'Ekstensi file harus .pdf'];
    }

    // Buat direktori upload jika belum ada
    $uploadDir = UPLOAD_DIR . 'documents/';
    if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
        return ['success' => false, 'error' => 'Gagal membuat direktori upload.'];
    }

    // Generate safe filename
    $safePrefix = preg_replace('/[^a-zA-Z0-9_-]/', '_', $prefix);
    $filename   = $safePrefix . '_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.pdf';
    $destPath   = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        return ['success' => false, 'error' => 'Gagal memindahkan file upload.'];
    }

    return [
        'success' => true,
        'path'    => 'documents/' . $filename,   // relatif terhadap UPLOAD_DIR
        'name'    => $filename,
        'size'    => $file['size'],
        'mime'    => $mimeType,
    ];
}

/**
 * Hapus file upload jika ada.
 */
function delete_upload(string|null $relativePath): void
{
    if (empty($relativePath)) return;
    $full = UPLOAD_DIR . $relativePath;
    if (file_exists($full) && is_file($full)) {
        @unlink($full);
    }
}
