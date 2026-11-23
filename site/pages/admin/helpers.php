<?php
/**
 * Utilitare pentru administrare (gestiune imagini produse).
 */

function salveazaImagineProdus($file): ?array
{
    if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Imaginea nu a putut fi încărcată. Încearcă din nou.');
    }
    if (!isset($file['tmp_name'], $file['size']) || (int) $file['size'] < 1 || (int) $file['size'] > 2 * 1024 * 1024) {
        throw new InvalidArgumentException('Imaginea trebuie să aibă maximum 2 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!isset($extensions[$mime]) || $imageInfo === false || $imageInfo[0] * $imageInfo[1] > 40000000) {
        throw new InvalidArgumentException('Alege o imagine JPG, PNG sau WebP validă, de maximum 40 megapixeli.');
    }

    $directory = __DIR__ . '/../../img/products';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('Directorul pentru imaginile produselor nu poate fi creat.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    $absolutePath = $directory . DIRECTORY_SEPARATOR . $filename;
    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        throw new RuntimeException('Imaginea nu a putut fi salvată pe server.');
    }

    return ['path' => 'img/products/' . $filename, 'absolute' => $absolutePath];
}

function stergeImagineProdus(?string $relativePath): void
{
    if (!is_string($relativePath)
        || !preg_match('~\Aimg/products/[a-f0-9]{32}\.(?:jpg|png|webp)\z~', $relativePath)) {
        return;
    }
    $absolutePath = __DIR__ . '/../../' . $relativePath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}
