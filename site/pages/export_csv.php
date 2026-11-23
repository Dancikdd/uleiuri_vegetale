<?php
/**
 * Exportă raportul solicitărilor (comenzilor) pentru un interval de date selectat.
 * Accesibil doar administratorilor, via GET cu parametrii ?data_start=...&data_sfarsit=...
 */
require_once __DIR__ . '/../components/config.php';
cere_roluri('administrator');
require_once __DIR__ . '/../components/database.php';

// ── Validare interval de date ──────────────────────────────────────────────
$dataStart  = trim(is_string($_GET['data_start']  ?? null) ? $_GET['data_start']  : '');
$dataSfarsit = trim(is_string($_GET['data_sfarsit'] ?? null) ? $_GET['data_sfarsit'] : '');

if ($dataStart === '' || $dataSfarsit === '') {
    $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Selectează ambele date pentru export.'];
    header('Location: administrare.php#admin-solicitari');
    exit;
}

// Verifică formatul YYYY-MM-DD
if (!preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $dataStart) || !preg_match('/\A\d{4}-\d{2}-\d{2}\z/', $dataSfarsit)) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Formatul datei trebuie să fie AAAA-LL-ZZ.'];
    header('Location: administrare.php#admin-solicitari');
    exit;
}

// Verifică validitatea calendaristică
$dtStart  = DateTimeImmutable::createFromFormat('Y-m-d', $dataStart);
$dtSfarsit = DateTimeImmutable::createFromFormat('Y-m-d', $dataSfarsit);
if (!$dtStart || !$dtSfarsit || $dtStart->format('Y-m-d') !== $dataStart || $dtSfarsit->format('Y-m-d') !== $dataSfarsit) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Datele introduse nu sunt valide.'];
    header('Location: administrare.php#admin-solicitari');
    exit;
}

if ($dtStart > $dtSfarsit) {
    $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Data de început nu poate fi după data de sfârșit.'];
    header('Location: administrare.php#admin-solicitari');
    exit;
}

// ── Interogare bază de date ────────────────────────────────────────────────
try {
    $db = database();
    $stmt = $db->prepare(
        "SELECT r.id                                             AS comanda_id,
                r.created_at                                     AS data_comanda,
                r.status                                         AS statut,
                u.name                                           AS utilizator,
                u.email                                          AS email,
                COALESCE(GROUP_CONCAT(
                    CONCAT(i.name, ' × ', ri.quantity)
                    ORDER BY i.name SEPARATOR '; '
                ), '')                                           AS produse,
                COALESCE(SUM(ri.quantity * ri.unit_price), 0)    AS total
         FROM requests r
         JOIN users u ON u.id = r.user_id
         LEFT JOIN request_items ri ON ri.request_id = r.id
         LEFT JOIN items i ON i.id = ri.item_id
         WHERE DATE(r.created_at) >= :data_start
           AND DATE(r.created_at) <= :data_sfarsit
         GROUP BY r.id, r.created_at, r.status, u.id, u.name, u.email
         ORDER BY r.created_at ASC, r.id ASC"
    );
    $stmt->execute([
        'data_start'  => $dataStart,
        'data_sfarsit' => $dataSfarsit,
    ]);
    $rows = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('CSV export error: ' . $e->getMessage());
    $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Exportul nu a putut fi generat momentan.'];
    header('Location: administrare.php#admin-solicitari');
    exit;
}

// ── Traducere status pentru CSV ────────────────────────────────────────────
$eticheteStatus = [
    'pending'   => 'În așteptare',
    'approved'  => 'Aprobată',
    'rejected'  => 'Respinsă',
    'cancelled' => 'Anulată',
];

// ── Generare CSV ───────────────────────────────────────────────────────────
$filename = 'raport_solicitari_' . $dataStart . '_' . $dataSfarsit . '.csv';

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// BOM UTF-8 pentru Excel
$output = fopen('php://output', 'w');
fwrite($output, "\xEF\xBB\xBF");

// Antet coloane
fputcsv($output, [
    'ID Comandă',
    'Data comenzii',
    'Statut',
    'Utilizator',
    'Email',
    'Produse comandate',
    'Total (lei)',
]);

foreach ($rows as $row) {
    fputcsv($output, [
        $row['comanda_id'],
        $row['data_comanda'],
        $eticheteStatus[$row['statut']] ?? $row['statut'],
        $row['utilizator'],
        $row['email'],
        $row['produse'],
        number_format((float) $row['total'], 2, '.', ''),
    ]);
}

fclose($output);
exit;
