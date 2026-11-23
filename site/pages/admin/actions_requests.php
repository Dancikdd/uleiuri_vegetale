<?php
/**
 * Acțiuni administrare pentru solicitări/comenzi: schimbare statut și ștergere.
 */

if (!isset($action)) {
    return;
}

if ($action === 'request_status') {
    if (!token_csrf_valid()) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.'];
        header('Location: administrare.php#admin-solicitari');
        exit;
    }
    $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';
    try {
        if (!$requestId || $requestId < 1 || !in_array($newStatus, ['approved', 'rejected', 'cancelled'], true)) {
            throw new RuntimeException('Invalid request status action');
        }
        $stmt = database()->prepare(
            "UPDATE requests SET status = :status WHERE id = :id AND status = 'pending'"
        );
        $stmt->execute(['status' => $newStatus, 'id' => $requestId]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['admin_flash'] = [
                'type' => 'success',
                'text' => match ($newStatus) {
                    'approved' => 'Comanda a fost aprobată.',
                    'rejected' => 'Comanda a fost respinsă.',
                    default => 'Comanda a fost anulată.',
                },
            ];
        } else {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'text' => 'Comanda nu a fost găsită sau nu mai este în așteptare.',
            ];
        }
    } catch (Throwable $e) {
        error_log('Request status update error: ' . $e->getMessage());
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Statutul comenzii nu a putut fi modificat.'];
    }
    header('Location: administrare.php#admin-solicitari');
    exit;
} elseif ($action === 'request_delete') {
    if (!token_csrf_valid()) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.'];
        header('Location: administrare.php#admin-solicitari');
        exit;
    }
    $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
    $db = null;
    try {
        if (!$requestId || $requestId < 1) {
            throw new RuntimeException('Invalid request ID');
        }
        $db = database();
        $db->beginTransaction();
        $findStmt = $db->prepare('SELECT id FROM requests WHERE id = :id FOR UPDATE');
        $findStmt->execute(['id' => $requestId]);
        if (!$findStmt->fetchColumn()) {
            $db->rollBack();
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Comanda nu a fost găsită.'];
        } else {
            $deleteItems = $db->prepare('DELETE FROM request_items WHERE request_id = :request_id');
            $deleteItems->execute(['request_id' => $requestId]);
            $deleteRequest = $db->prepare('DELETE FROM requests WHERE id = :id');
            $deleteRequest->execute(['id' => $requestId]);
            $db->commit();
            $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Comanda și produsele ei au fost șterse.'];
        }
    } catch (Throwable $e) {
        if ($db instanceof PDO && $db->inTransaction()) {
            $db->rollBack();
        }
        error_log('Request delete database error: ' . $e->getMessage());
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Comanda nu a putut fi ștearsă.'];
    }
    header('Location: administrare.php#admin-solicitari');
    exit;
}
