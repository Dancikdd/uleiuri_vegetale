<?php
function cale_imagine_produs(?string $imagePath, string $productName = ''): string
{
    if (is_string($imagePath)
        && preg_match('~\Aimg/products/[a-f0-9]{32}\.(?:jpg|png|webp)\z~', $imagePath)
        && is_file(__DIR__ . '/../' . $imagePath)) {
        return $imagePath;
    }

    if (stripos($productName, 'dovleac') !== false) {
        return 'img/dovleac.jpeg';
    }
    if (stripos($productName, 'măsline') !== false || stripos($productName, 'masline') !== false) {
        return 'img/masline.jpg';
    }
    return 'img/floarea-soarelui.jpg';
}
