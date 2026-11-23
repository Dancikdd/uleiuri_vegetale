<?php
/**
 * Acțiuni administrare pentru categorii: salvare și ștergere.
 */

if (!isset($action)) {
    return;
}

if ($action === 'category_save') {
    $formType = 'category';
    $rawId = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';

    if (!token_csrf_valid()) {
        $generalError = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
        $categoryForm['id'] = $rawId === '' ? 0 : (filter_var($rawId, FILTER_VALIDATE_INT) ?: 0);
        $categoryForm['name'] = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
        $categoryForm['description'] = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
        return;
    }

    $parsedId = $rawId === '' ? 0 : filter_var($rawId, FILTER_VALIDATE_INT);
    $categoryForm['id'] = $parsedId === false ? -1 : $parsedId;
    $categoryForm['name'] = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
    $categoryForm['description'] = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');

    if ($categoryForm['id'] < 0) {
        $formErrors['id'] = 'Categoria selectată nu este validă.';
    }
    if (preg_match('/\A.{1,100}\z/us', $categoryForm['name']) !== 1) {
        $formErrors['name'] = 'Denumirea este obligatorie și poate avea maximum 100 de caractere.';
    }
    if (strlen($categoryForm['description']) > 60000) {
        $formErrors['description'] = 'Descrierea este prea lungă.';
    }

    if (!$formErrors) {
        try {
            $db = database();
            if ($categoryForm['id'] > 0) {
                $stmt = $db->prepare('UPDATE categories SET name = :name, description = :description WHERE id = :id');
                $stmt->execute([
                    'name' => $categoryForm['name'],
                    'description' => $categoryForm['description'],
                    'id' => $categoryForm['id'],
                ]);
                if ($stmt->rowCount() === 0) {
                    $exists = $db->prepare('SELECT id FROM categories WHERE id = :id');
                    $exists->execute(['id' => $categoryForm['id']]);
                    if (!$exists->fetchColumn()) {
                        $formErrors['id'] = 'Categoria selectată nu mai există.';
                        throw new RuntimeException('Category not found');
                    }
                }
                $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Modificări salvate'];
            } else {
                $stmt = $db->prepare('INSERT INTO categories (name, description) VALUES (:name, :description)');
                $stmt->execute([
                    'name' => $categoryForm['name'],
                    'description' => $categoryForm['description'],
                ]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Înregistrare adăugată'];
            }
            header('Location: administrare.php');
            exit;
        } catch (PDOException $e) {
            error_log('Category save database error: ' . $e->getMessage());
            $formErrors['name'] = (($e->errorInfo[1] ?? null) === 1062)
                ? 'Există deja o categorie cu această denumire.'
                : 'Categoria nu a putut fi salvată momentan.';
        } catch (Throwable $e) {
            error_log('Category save error: ' . $e->getMessage());
            if (!isset($formErrors['id'])) {
                $generalError = 'Categoria nu a putut fi salvată momentan.';
            }
        }
    }
} elseif ($action === 'category_delete') {
    if (!token_csrf_valid()) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.'];
        header('Location: administrare.php#admin-categorii');
        exit;
    }
    $categoryId = filter_var($_POST['category_id'] ?? null, FILTER_VALIDATE_INT);
    try {
        if (!$categoryId || $categoryId < 1) {
            throw new RuntimeException('Invalid category ID');
        }
        $db = database();
        $countStmt = $db->prepare('SELECT COUNT(*) FROM items WHERE category_id = :category_id');
        $countStmt->execute(['category_id' => $categoryId]);
        if ((int) $countStmt->fetchColumn() > 0) {
            $_SESSION['admin_flash'] = [
                'type' => 'error',
                'text' => 'Categoria nu poate fi ștearsă cât timp are produse. Mută sau șterge produsele mai întâi.',
            ];
        } else {
            $deleteStmt = $db->prepare('DELETE FROM categories WHERE id = :id');
            $deleteStmt->execute(['id' => $categoryId]);
            $_SESSION['admin_flash'] = $deleteStmt->rowCount() > 0
                ? ['type' => 'success', 'text' => 'Înregistrare ștearsă']
                : ['type' => 'error', 'text' => 'Categoria nu a fost găsită.'];
        }
    } catch (Throwable $e) {
        error_log('Category delete database error: ' . $e->getMessage());
        $_SESSION['admin_flash'] = [
            'type' => 'error',
            'text' => 'Categoria nu a putut fi ștearsă. Verifică dacă mai are produse asociate.',
        ];
    }
    header('Location: administrare.php#admin-categorii');
    exit;
}
