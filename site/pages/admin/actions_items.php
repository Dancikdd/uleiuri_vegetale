<?php
/**
 * Acțiuni administrare pentru produse: salvare, activare/dezactivare și ștergere.
 */

if (!isset($action)) {
    return;
}

if ($action === 'item_save') {
    $formType = 'item';
    $rawId = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';

    if (!token_csrf_valid()) {
        $generalError = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
        $itemForm['id'] = $rawId === '' ? 0 : (filter_var($rawId, FILTER_VALIDATE_INT) ?: 0);
        $itemForm['category_id'] = is_string($_POST['category_id'] ?? null) ? (filter_var($_POST['category_id'], FILTER_VALIDATE_INT) ?: 0) : 0;
        $itemForm['name'] = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
        $itemForm['description'] = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
        $itemForm['price'] = trim(is_string($_POST['price'] ?? null) ? $_POST['price'] : '');
        $itemForm['quantity_available'] = trim(is_string($_POST['quantity_available'] ?? null) ? $_POST['quantity_available'] : '');
        return;
    }

    $parsedId = $rawId === '' ? 0 : filter_var($rawId, FILTER_VALIDATE_INT);
    $itemForm['id'] = $parsedId === false ? -1 : $parsedId;
    $rawCategoryId = is_string($_POST['category_id'] ?? null) ? trim($_POST['category_id']) : '';
    $itemForm['category_id'] = filter_var($rawCategoryId, FILTER_VALIDATE_INT) ?: 0;
    $itemForm['name'] = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
    $itemForm['description'] = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
    $itemForm['price'] = trim(is_string($_POST['price'] ?? null) ? $_POST['price'] : '');
    $itemForm['quantity_available'] = trim(is_string($_POST['quantity_available'] ?? null) ? $_POST['quantity_available'] : '');

    if ($itemForm['id'] < 0) {
        $formErrors['id'] = 'Produsul selectat nu este valid.';
    }
    if ($itemForm['category_id'] < 1) {
        $formErrors['category_id'] = 'Selectați o categorie.';
    }
    if (preg_match('/\A.{1,150}\z/us', $itemForm['name']) !== 1) {
        $formErrors['name'] = 'Denumirea este obligatorie și poate avea maximum 150 de caractere.';
    }
    if (strlen($itemForm['description']) > 60000) {
        $formErrors['description'] = 'Descrierea este prea lungă.';
    }
    if (!preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $itemForm['price'])) {
        $formErrors['price'] = 'Introduceți un preț nenegativ cu maximum două zecimale.';
    }
    $quantityValue = filter_var($itemForm['quantity_available'], FILTER_VALIDATE_INT);
    if ($quantityValue === false || $quantityValue < 0) {
        $formErrors['quantity_available'] = 'Cantitatea disponibilă trebuie să fie un număr întreg nenegativ.';
    }

    $newImage = null;
    if (!$formErrors) {
        try {
            $newImage = salveazaImagineProdus($_FILES['image'] ?? null);
        } catch (InvalidArgumentException $e) {
            $formErrors['image'] = $e->getMessage();
        } catch (Throwable $e) {
            error_log('Product image upload error: ' . $e->getMessage());
            $formErrors['image'] = 'Imaginea nu a putut fi salvată momentan.';
        }
    }

    if (!$formErrors) {
        try {
            $db = database();
            $categoryStmt = $db->prepare('SELECT id FROM categories WHERE id = :id');
            $categoryStmt->execute(['id' => $itemForm['category_id']]);
            if (!$categoryStmt->fetchColumn()) {
                $formErrors['category_id'] = 'Categoria selectată nu există.';
                if ($newImage) {
                    @unlink($newImage['absolute']);
                }
            } elseif ($itemForm['id'] > 0) {
                $oldImageStmt = $db->prepare('SELECT image_path FROM items WHERE id = :id');
                $oldImageStmt->execute(['id' => $itemForm['id']]);
                $oldItem = $oldImageStmt->fetch();
                if (!$oldItem) {
                    $formErrors['id'] = 'Elementul selectat nu mai există.';
                    if ($newImage) {
                        @unlink($newImage['absolute']);
                    }
                    throw new RuntimeException('Item not found');
                }
                $oldImagePath = $oldItem['image_path'];
                $imagePath = $newImage['path'] ?? $oldImagePath;
                $stmt = $db->prepare(
                    'UPDATE items
                     SET category_id = :category_id, name = :name, description = :description,
                         image_path = :image_path, price = :price, quantity_available = :quantity_available
                     WHERE id = :id'
                );
                $stmt->execute([
                    'category_id' => $itemForm['category_id'],
                    'name' => $itemForm['name'],
                    'description' => $itemForm['description'],
                    'image_path' => $imagePath,
                    'price' => $itemForm['price'],
                    'quantity_available' => $quantityValue,
                    'id' => $itemForm['id'],
                ]);
                if ($stmt->rowCount() === 0) {
                    $exists = $db->prepare('SELECT id FROM items WHERE id = :id');
                    $exists->execute(['id' => $itemForm['id']]);
                    if (!$exists->fetchColumn()) {
                        $formErrors['id'] = 'Elementul selectat nu mai există.';
                        throw new RuntimeException('Item not found');
                    }
                }
                if ($newImage) {
                    stergeImagineProdus($oldImagePath);
                }
                $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Modificări salvate'];
                header('Location: administrare.php');
                exit;
            } else {
                $stmt = $db->prepare(
                    'INSERT INTO items (category_id, name, description, image_path, price, quantity_available, is_active)
                     VALUES (:category_id, :name, :description, :image_path, :price, :quantity_available, 1)'
                );
                $stmt->execute([
                    'category_id' => $itemForm['category_id'],
                    'name' => $itemForm['name'],
                    'description' => $itemForm['description'],
                    'image_path' => $newImage['path'] ?? null,
                    'price' => $itemForm['price'],
                    'quantity_available' => $quantityValue,
                ]);
                $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Înregistrare adăugată'];
                header('Location: administrare.php');
                exit;
            }
        } catch (Throwable $e) {
            error_log('Item save database error: ' . $e->getMessage());
            if ($newImage) {
                @unlink($newImage['absolute']);
            }
            if (!isset($formErrors['id'])) {
                $generalError = 'Produsul nu a putut fi salvat momentan.';
            }
        }
    }
} elseif ($action === 'item_toggle' || $action === 'item_delete') {
    if (!token_csrf_valid()) {
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.'];
        header('Location: administrare.php');
        exit;
    }
    $itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT);
    try {
        $db = database();
        if (!$itemId || $itemId < 1) {
            throw new RuntimeException('Invalid item ID');
        }
        if ($action === 'item_toggle') {
            $active = filter_var($_POST['is_active'] ?? null, FILTER_VALIDATE_INT);
            if (!in_array($active, [0, 1], true)) {
                throw new RuntimeException('Invalid active state');
            }
            $stmt = $db->prepare('UPDATE items SET is_active = :is_active WHERE id = :id');
            $stmt->execute(['is_active' => $active, 'id' => $itemId]);
            if ($stmt->rowCount() === 0 && !$db->query('SELECT id FROM items WHERE id = ' . (int) $itemId)->fetchColumn()) {
                throw new RuntimeException('Item not found');
            }
            $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Modificări salvate'];
        } else {
            $countStmt = $db->prepare('SELECT COUNT(*) FROM request_items WHERE item_id = :item_id');
            $countStmt->execute(['item_id' => $itemId]);
            if ((int) $countStmt->fetchColumn() > 0) {
                $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Elementul nu poate fi șters deoarece apare în comenzi.'];
            } else {
                $imageStmt = $db->prepare('SELECT image_path FROM items WHERE id = :id');
                $imageStmt->execute(['id' => $itemId]);
                $imagePath = $imageStmt->fetchColumn();
                $deleteStmt = $db->prepare('DELETE FROM items WHERE id = :id');
                $deleteStmt->execute(['id' => $itemId]);
                if ($deleteStmt->rowCount() === 0) {
                    $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Produsul nu a fost găsit.'];
                } else {
                    stergeImagineProdus(is_string($imagePath) ? $imagePath : null);
                    $_SESSION['admin_flash'] = ['type' => 'success', 'text' => 'Înregistrare ștearsă'];
                }
            }
        }
    } catch (Throwable $e) {
        error_log('Item action database error: ' . $e->getMessage());
        $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Operația asupra produsului nu a putut fi efectuată.'];
    }
    header('Location: administrare.php');
    exit;
}
