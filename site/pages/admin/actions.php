<?php
/**
 * Rutează acțiunile POST din panoul de administrare către modulele specifice.
 */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';

    match ($action) {
        'category_save', 'category_delete' => require __DIR__ . '/actions_categories.php',
        'item_save', 'item_toggle', 'item_delete' => require __DIR__ . '/actions_items.php',
        'request_status', 'request_delete' => require __DIR__ . '/actions_requests.php',
        default => $generalError = 'Acțiunea solicitată nu este validă.',
    };
}
