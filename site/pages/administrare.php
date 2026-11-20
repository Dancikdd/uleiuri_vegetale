<?php
require_once __DIR__ . '/../components/config.php';
cere_roluri('administrator');
require_once __DIR__ . '/../components/database.php';
require_once __DIR__ . '/../components/product_images.php';

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

    $directory = __DIR__ . '/../img/products';
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
    $absolutePath = __DIR__ . '/../' . $relativePath;
    if (is_file($absolutePath)) {
        @unlink($absolutePath);
    }
}

$root = '../';
$paginaActiva = '';
$titluPagina = 'Administrare';
$formType = '';
$formErrors = [];
$generalError = null;
$flash = $_SESSION['admin_flash'] ?? null;
unset($_SESSION['admin_flash']);

$categoryForm = ['id' => 0, 'name' => '', 'description' => ''];
$itemForm = ['id' => 0, 'category_id' => 0, 'name' => '', 'description' => '', 'price' => '', 'quantity_available' => '0', 'image_path' => null];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
    if (!token_csrf_valid()) {
        $generalError = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
        if ($action === 'category_save') {
            $formType = 'category';
            $rawId = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';
            $categoryForm['id'] = $rawId === '' ? 0 : (filter_var($rawId, FILTER_VALIDATE_INT) ?: 0);
            $categoryForm['name'] = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
            $categoryForm['description'] = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
        } elseif ($action === 'item_save') {
            $formType = 'item';
            $rawId = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';
            $itemForm['id'] = $rawId === '' ? 0 : (filter_var($rawId, FILTER_VALIDATE_INT) ?: 0);
            $itemForm['category_id'] = is_string($_POST['category_id'] ?? null) ? (filter_var($_POST['category_id'], FILTER_VALIDATE_INT) ?: 0) : 0;
            $itemForm['name'] = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
            $itemForm['description'] = trim(is_string($_POST['description'] ?? null) ? $_POST['description'] : '');
            $itemForm['price'] = trim(is_string($_POST['price'] ?? null) ? $_POST['price'] : '');
            $itemForm['quantity_available'] = trim(is_string($_POST['quantity_available'] ?? null) ? $_POST['quantity_available'] : '');
        }
    } elseif ($action === 'category_save') {
        $formType = 'category';
        $rawId = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';
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
    } elseif ($action === 'item_save') {
        $formType = 'item';
        $rawId = is_string($_POST['id'] ?? null) ? trim($_POST['id']) : '';
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
    } elseif ($action === 'request_status') {
        if (!token_csrf_valid()) {
            $_SESSION['admin_flash'] = ['type' => 'error', 'text' => 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.'];
            header('Location: administrare.php#admin-solicitari');
            exit;
        }
        $requestId = filter_var($_POST['request_id'] ?? null, FILTER_VALIDATE_INT);
        $newStatus = $_POST['status'] ?? '';
        try {
            if (!$requestId || $requestId < 1 || !in_array($newStatus, ['approved', 'rejected'], true)) {
                throw new RuntimeException('Invalid request status action');
            }
            $stmt = database()->prepare(
                "UPDATE requests SET status = :status WHERE id = :id AND status = 'pending'"
            );
            $stmt->execute(['status' => $newStatus, 'id' => $requestId]);
            if ($stmt->rowCount() > 0) {
                $_SESSION['admin_flash'] = [
                    'type' => 'success',
                    'text' => $newStatus === 'approved' ? 'Comanda a fost aprobată.' : 'Comanda a fost respinsă.',
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
    } else {
        $generalError = 'Acțiunea solicitată nu este validă.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_category'])) {
    $editId = filter_var($_GET['edit_category'], FILTER_VALIDATE_INT);
    if (!$editId || $editId < 1) {
        pagina_indisponibila();
    }
    try {
        $stmt = database()->prepare('SELECT id, name, description FROM categories WHERE id = :id');
        $stmt->execute(['id' => $editId]);
        $row = $stmt->fetch();
        if (!$row) {
            pagina_indisponibila();
        }
        $categoryForm = ['id' => (int) $row['id'], 'name' => $row['name'], 'description' => $row['description'] ?? ''];
        $formType = 'category';
    } catch (Throwable $e) {
        error_log('Category edit load error: ' . $e->getMessage());
        $generalError = 'Categoria nu a putut fi încărcată momentan.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['edit_item'])) {
    $editId = filter_var($_GET['edit_item'], FILTER_VALIDATE_INT);
    if (!$editId || $editId < 1) {
        pagina_indisponibila();
    }
    try {
        $stmt = database()->prepare(
            'SELECT id, category_id, name, description, image_path, price, quantity_available FROM items WHERE id = :id'
        );
        $stmt->execute(['id' => $editId]);
        $row = $stmt->fetch();
        if (!$row) {
            pagina_indisponibila();
        }
        $itemForm = [
            'id' => (int) $row['id'],
            'category_id' => (int) $row['category_id'],
            'name' => $row['name'],
            'description' => $row['description'] ?? '',
            'image_path' => $row['image_path'] ?? null,
            'price' => (string) $row['price'],
            'quantity_available' => (string) $row['quantity_available'],
        ];
        $formType = 'item';
    } catch (Throwable $e) {
        error_log('Item edit load error: ' . $e->getMessage());
        $generalError = 'Produsul nu a putut fi încărcat momentan.';
    }
}

$categorii = [];
$produse = [];
$utilizatori = [];
$solicitariAdmin = [];
$eroareCitire = null;
$eroareAdminData = null;
try {
    $db = database();
    $categorii = $db->query(
        'SELECT c.id, c.name, c.description, COUNT(i.id) AS item_count
         FROM categories c
         LEFT JOIN items i ON i.category_id = c.id
         GROUP BY c.id, c.name, c.description
         ORDER BY c.name'
    )->fetchAll();
    $produse = $db->query(
        'SELECT i.id, i.category_id, i.name, i.description, i.image_path, i.price,
                i.quantity_available, i.is_active, c.name AS category_name
         FROM items i
         JOIN categories c ON c.id = i.category_id
         ORDER BY i.name'
    )->fetchAll();
} catch (Throwable $e) {
    error_log('Admin catalog read error: ' . $e->getMessage());
    $eroareCitire = 'Datele catalogului nu sunt disponibile momentan.';
}

try {
    $db = database();
    $utilizatori = $db->query(
        'SELECT u.id, u.name, u.email, u.role, u.created_at, COUNT(r.id) AS request_count
         FROM users u
         LEFT JOIN requests r ON r.user_id = u.id
         GROUP BY u.id, u.name, u.email, u.role, u.created_at
         ORDER BY u.created_at DESC, u.name'
    )->fetchAll();
    $solicitariAdmin = $db->query(
        "SELECT r.id, r.created_at, r.status, u.name AS user_name, u.email AS user_email,
                COALESCE(SUM(ri.quantity * ri.unit_price), 0) AS total,
                GROUP_CONCAT(CONCAT(i.name, ' × ', ri.quantity)
                             ORDER BY i.name SEPARATOR ', ') AS products
         FROM requests r
         JOIN users u ON u.id = r.user_id
         LEFT JOIN request_items ri ON ri.request_id = r.id
         LEFT JOIN items i ON i.id = ri.item_id
         GROUP BY r.id, r.created_at, r.status, u.id, u.name, u.email
         ORDER BY r.created_at DESC, r.id DESC"
    )->fetchAll();
} catch (Throwable $e) {
    error_log('Admin users and requests read error: ' . $e->getMessage());
    $eroareAdminData = 'Utilizatorii și comenzile nu sunt disponibile momentan.';
}

require __DIR__ . '/../components/header.php';

$numarCategorii = count($categorii);
$numarProduse = count($produse);
$numarActive = count(array_filter($produse, static fn($produs) => (int) $produs['is_active'] === 1));
$numarSolicitari = count($solicitariAdmin);
$numarUtilizatori = count($utilizatori);
?>

<section class="pagina admin-dashboard">
    <header class="admin-heading">
        <div>
            <span class="admin-eyebrow">Panou de control</span>
            <h1>Administrare</h1>
            <p>Gestionează catalogul de produse și categoriile magazinului.</p>
        </div>
        <nav class="admin-shortcuts" aria-label="Scurtături administrare">
            <a href="#admin-categorii">Categorii</a>
            <a href="#admin-produse">Produse</a>
            <a href="#admin-solicitari">Solicitări</a>
            <a href="#admin-utilizatori">Utilizatori</a>
        </nav>
    </header>

    <?php if ($flash): ?>
        <p class="mesaj <?= ($flash['type'] ?? '') === 'error' ? 'mesaj-eroare' : 'mesaj-succes' ?>" role="status">
            <?= htmlspecialchars($flash['text'] ?? '', ENT_QUOTES, 'UTF-8') ?>
        </p>
    <?php endif; ?>
    <?php if ($generalError): ?>
        <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($generalError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if (!$eroareCitire): ?>
        <div class="admin-stats" aria-label="Rezumatul platformei">
            <article class="admin-stat"><span>Categorii</span><strong><?= $numarCategorii ?></strong></article>
            <article class="admin-stat"><span>Total produse</span><strong><?= $numarProduse ?></strong></article>
            <article class="admin-stat"><span>Produse active</span><strong><?= $numarActive ?></strong></article>
            <article class="admin-stat"><span>Solicitări</span><strong><?= $numarSolicitari ?></strong></article>
            <article class="admin-stat"><span>Utilizatori</span><strong><?= $numarUtilizatori ?></strong></article>
        </div>
    <?php endif; ?>

    <?php if ($eroareCitire): ?>
        <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareCitire, ENT_QUOTES, 'UTF-8') ?></p>
    <?php else: ?>
        <div class="admin-sections">
            <section class="admin-panel" id="admin-categorii">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Organizare catalog</span><h2>Categorii</h2></div>
                    <div class="admin-panel-tools"><span class="admin-count"><?= $numarCategorii ?> total</span><button class="admin-add-link" type="button" data-open-dialog="category-dialog">+ Adaugă</button></div>
                </div>
                <div class="admin-search-row">
                    <label class="sr-only" for="category-search">Caută categorii</label>
                    <input class="admin-search" id="category-search" type="search" placeholder="Caută categorie..." data-table-search="categories-table">
                    <span class="admin-search-count" data-search-count="categories-table"><?= $numarCategorii ?> categorii</span>
                </div>
                <?php if (!$categorii): ?><p class="admin-empty">Nu există categorii încă.</p><?php endif; ?>
                <div class="tabel-responsiv">
                    <table class="admin-table" id="categories-table">
                        <thead><tr><th>Denumire</th><th>Elemente</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($categorii as $categorie): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($categorie['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($categorie['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></td>
                                    <td><span class="admin-count"><?= (int) $categorie['item_count'] ?></span></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a class="admin-text-link" href="administrare.php?edit_category=<?= (int) $categorie['id'] ?>#admin-categorii">Modifică</a>
                                            <form action="administrare.php#admin-categorii" method="post" onsubmit="return confirm('Ștergi această categorie?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="category_delete">
                                                <input type="hidden" name="category_id" value="<?= (int) $categorie['id'] ?>">
                                                <button class="admin-action-button action-delete" type="submit">Șterge</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="admin-empty" data-search-empty="categories-table" hidden>Nu am găsit categorii potrivite.</p>
                <dialog class="admin-modal" id="category-dialog" aria-labelledby="category-dialog-title" data-auto-open="<?= $formType === 'category' ? 'true' : 'false' ?>" data-reset-url="administrare.php#admin-categorii">
                    <div class="admin-modal-heading">
                        <div><span class="admin-eyebrow">Organizare catalog</span><h2 id="category-dialog-title"><?= $categoryForm['id'] ? 'Modifică categoria' : 'Adaugă o categorie' ?></h2></div>
                        <button class="admin-modal-close" type="button" data-close-dialog aria-label="Închide fereastra">&times;</button>
                    </div>
                    <form class="formular admin-form" action="administrare.php" method="post">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="category_save">
                        <input type="hidden" name="id" value="<?= (int) $categoryForm['id'] ?>">
                        <?php if (isset($formErrors['id']) && $formType === 'category'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['id'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <label for="category_name">Denumire</label>
                        <input type="text" id="category_name" name="name" required maxlength="100" value="<?= htmlspecialchars($categoryForm['name'], ENT_QUOTES, 'UTF-8') ?>">
                        <?php if (isset($formErrors['name']) && $formType === 'category'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['name'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <label for="category_description">Descriere</label>
                        <textarea id="category_description" name="description" rows="3"><?= htmlspecialchars($categoryForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea>
                        <?php if (isset($formErrors['description']) && $formType === 'category'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['description'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <div class="admin-form-actions"><button class="buton" type="submit"><?= $categoryForm['id'] ? 'Salvează modificările' : 'Adaugă categoria' ?></button><?php if ($categoryForm['id']): ?><a href="administrare.php#admin-categorii">Anulează</a><?php else: ?><button class="admin-cancel-button" type="button" data-close-dialog>Anulează</button><?php endif; ?></div>
                    </form>
                </dialog>
            </section>

            <section class="admin-panel admin-products" id="admin-produse">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Catalog magazin</span><h2>Produse</h2></div>
                    <div class="admin-panel-tools"><span class="admin-count"><?= $numarProduse ?> total</span><button class="admin-add-link" type="button" data-open-dialog="item-dialog">+ Adaugă</button></div>
                </div>
                <div class="admin-search-row">
                    <label class="sr-only" for="item-search">Caută produse</label>
                    <input class="admin-search" id="item-search" type="search" placeholder="Caută după produs sau categorie..." data-table-search="items-table">
                    <span class="admin-search-count" data-search-count="items-table"><?= $numarProduse ?> produse</span>
                </div>
                <div class="tabel-responsiv">
                    <table class="admin-table" id="items-table">
                        <thead><tr><th>Produs</th><th>Categorie</th><th>Preț</th><th>Stoc</th><th>Statut</th><th>Acțiuni</th></tr></thead>
                        <tbody>
                            <?php foreach ($produse as $produs): ?>
                                <tr>
                                    <td>
                                        <div class="admin-product-ident">
                                            <img src="../<?= htmlspecialchars(cale_imagine_produs($produs['image_path'] ?? null, $produs['name']), ENT_QUOTES, 'UTF-8') ?>" alt="" loading="lazy">
                                            <div><strong><?= htmlspecialchars($produs['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($produs['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></small></div>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($produs['category_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td class="admin-price"><?= number_format((float) $produs['price'], 2) ?> lei</td>
                                    <td><?= (int) $produs['quantity_available'] ?></td>
                                    <td><span class="admin-status <?= (int) $produs['is_active'] === 1 ? 'este-activ' : 'este-inactiv' ?>"><?= (int) $produs['is_active'] === 1 ? 'Activ' : 'Inactiv' ?></span></td>
                                    <td>
                                        <div class="admin-actions">
                                            <a class="admin-text-link" href="administrare.php?edit_item=<?= (int) $produs['id'] ?>#admin-produse">Modifică</a>
                                            <form action="administrare.php#admin-produse" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="item_toggle">
                                                <input type="hidden" name="item_id" value="<?= (int) $produs['id'] ?>">
                                                <input type="hidden" name="is_active" value="<?= (int) $produs['is_active'] === 1 ? 0 : 1 ?>">
                                                <button class="admin-action-button" type="submit"><?= (int) $produs['is_active'] === 1 ? 'Dezactivează' : 'Activează' ?></button>
                                            </form>
                                            <form action="administrare.php#admin-produse" method="post">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="item_delete">
                                                <input type="hidden" name="item_id" value="<?= (int) $produs['id'] ?>">
                                                <button class="admin-action-button action-delete" type="submit">Șterge</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php if (!$produse): ?><p class="admin-empty">Nu există produse încă.</p><?php endif; ?>
                </div>
                <p class="admin-empty" data-search-empty="items-table" hidden>Nu am găsit produse potrivite.</p>

                <dialog class="admin-modal admin-modal-large" id="item-dialog" aria-labelledby="item-dialog-title" data-auto-open="<?= $formType === 'item' ? 'true' : 'false' ?>" data-reset-url="administrare.php#admin-produse">
                    <div class="admin-modal-heading">
                        <div><span class="admin-eyebrow">Catalog magazin</span><h2 id="item-dialog-title"><?= $itemForm['id'] ? 'Modifică produsul' : 'Adaugă un produs' ?></h2></div>
                        <button class="admin-modal-close" type="button" data-close-dialog aria-label="Închide fereastra">&times;</button>
                    </div>
                    <?php if (!$categorii): ?><p class="admin-empty">Adaugă mai întâi o categorie.</p><?php endif; ?>
                    <form class="formular admin-form" action="administrare.php#admin-produse" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                        <input type="hidden" name="action" value="item_save">
                        <input type="hidden" name="id" value="<?= (int) $itemForm['id'] ?>">
                        <div class="admin-form-grid">
                            <div><label for="item_name">Denumire</label><input type="text" id="item_name" name="name" required maxlength="150" value="<?= htmlspecialchars($itemForm['name'], ENT_QUOTES, 'UTF-8') ?>"><?php if (isset($formErrors['name']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['name'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div><label for="category_id">Categorie</label><select id="category_id" name="category_id" required><option value="">Alege categoria</option><?php foreach ($categorii as $categorie): ?><option value="<?= (int) $categorie['id'] ?>" <?= (int) $itemForm['category_id'] === (int) $categorie['id'] ? 'selected' : '' ?>><?= htmlspecialchars($categorie['name'], ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select><?php if (isset($formErrors['category_id']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['category_id'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div><label for="price">Preț (lei)</label><input type="number" id="price" name="price" min="0" max="99999999.99" step="0.01" required value="<?= htmlspecialchars((string) $itemForm['price'], ENT_QUOTES, 'UTF-8') ?>"><?php if (isset($formErrors['price']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['price'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div><label for="quantity_available">Cantitate disponibilă</label><input type="number" id="quantity_available" name="quantity_available" min="0" step="1" required value="<?= htmlspecialchars((string) $itemForm['quantity_available'], ENT_QUOTES, 'UTF-8') ?>"><?php if (isset($formErrors['quantity_available']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['quantity_available'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div class="admin-description-field"><label for="item_description">Descriere</label><textarea id="item_description" name="description" rows="3"><?= htmlspecialchars($itemForm['description'], ENT_QUOTES, 'UTF-8') ?></textarea><?php if (isset($formErrors['description']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['description'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></div>
                            <div class="admin-image-field"><label for="item_image">Imagine produs (JPG, PNG sau WebP, maximum 2 MB)</label><input type="file" id="item_image" name="image" accept="image/jpeg,image/png,image/webp"><?php if (isset($formErrors['image']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['image'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?><?php if (!empty($itemForm['image_path'])): ?><span class="admin-current-image">Imaginea curentă</span><img class="admin-image-preview" src="../<?= htmlspecialchars(cale_imagine_produs($itemForm['image_path'], $itemForm['name']), ENT_QUOTES, 'UTF-8') ?>" alt="Imagine curentă: <?= htmlspecialchars($itemForm['name'], ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?></div>
                        </div>
                        <?php if (isset($formErrors['id']) && $formType === 'item'): ?><small class="mesaj-eroare"><?= htmlspecialchars($formErrors['id'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
                        <div class="admin-form-actions"><button class="buton" type="submit" <?= !$categorii ? 'disabled' : '' ?>><?= $itemForm['id'] ? 'Salvează modificările' : 'Adaugă produsul' ?></button><?php if ($itemForm['id']): ?><a href="administrare.php#admin-produse">Anulează</a><?php else: ?><button class="admin-cancel-button" type="button" data-close-dialog>Anulează</button><?php endif; ?></div>
                    </form>
                </dialog>
            </section>

            <section class="admin-panel admin-data-panel" id="admin-solicitari">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Comenzi primite</span><h2>Toate comenzile</h2></div>
                    <span class="admin-count"><?= $numarSolicitari ?> total</span>
                </div>
                <?php if ($eroareAdminData): ?>
                    <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareAdminData, ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif (!$solicitariAdmin): ?>
                    <p class="admin-empty">Nu există comenzi înregistrate.</p>
                <?php else: ?>
                    <?php $eticheteStatus = ['pending' => 'În așteptare', 'approved' => 'Aprobată', 'rejected' => 'Respinsă', 'cancelled' => 'Anulată']; ?>
                    <div class="tabel-responsiv">
                        <table class="admin-table admin-request-table">
                            <thead><tr><th>Comandă</th><th>Utilizator</th><th>Produse comandate</th><th>Total</th><th>Statut</th><th>Acțiuni</th></tr></thead>
                            <tbody>
                                <?php foreach ($solicitariAdmin as $solicitare): ?>
                                    <tr>
                                        <td><strong>#<?= (int) $solicitare['id'] ?></strong><small><?= htmlspecialchars($solicitare['created_at'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><strong><?= htmlspecialchars($solicitare['user_name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($solicitare['user_email'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><?= htmlspecialchars($solicitare['products'] ?? 'Fără produse', ENT_QUOTES, 'UTF-8') ?></td>
                                        <td class="admin-price"><?= number_format((float) $solicitare['total'], 2) ?> lei</td>
                                        <td><span class="admin-status status-<?= htmlspecialchars($solicitare['status'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($eticheteStatus[$solicitare['status']] ?? $solicitare['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                                        <td>
                                            <?php if ($solicitare['status'] === 'pending'): ?>
                                                <div class="admin-actions">
                                                    <form action="administrare.php#admin-solicitari" method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="request_status">
                                                        <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                        <input type="hidden" name="status" value="approved">
                                                        <button class="admin-action-button" type="submit">Aprobă</button>
                                                    </form>
                                                    <form action="administrare.php#admin-solicitari" method="post">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                        <input type="hidden" name="action" value="request_status">
                                                        <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                        <input type="hidden" name="status" value="rejected">
                                                        <button class="admin-action-button action-delete" type="submit">Respinge</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                            <form action="administrare.php#admin-solicitari" method="post" onsubmit="return confirm('Ștergi definitiv această comandă și produsele ei?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                                                <input type="hidden" name="action" value="request_delete">
                                                <input type="hidden" name="request_id" value="<?= (int) $solicitare['id'] ?>">
                                                <button class="admin-action-button action-delete" type="submit">Șterge comanda</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="admin-panel admin-data-panel" id="admin-utilizatori">
                <div class="admin-panel-heading">
                    <div><span class="admin-eyebrow">Conturi înregistrate</span><h2>Toți utilizatorii</h2></div>
                    <span class="admin-count"><?= count($utilizatori) ?> total</span>
                </div>
                <?php if ($eroareAdminData): ?>
                    <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareAdminData, ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif (!$utilizatori): ?>
                    <p class="admin-empty">Nu există utilizatori înregistrați.</p>
                <?php else: ?>
                    <div class="tabel-responsiv">
                        <table class="admin-table admin-users-table">
                            <thead><tr><th>Utilizator</th><th>Rol</th><th>Comenzi</th><th>Cont creat</th></tr></thead>
                            <tbody>
                                <?php foreach ($utilizatori as $utilizator): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($utilizator['name'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($utilizator['email'], ENT_QUOTES, 'UTF-8') ?></small></td>
                                        <td><?= htmlspecialchars(ROLURI[$utilizator['role']] ?? $utilizator['role'], ENT_QUOTES, 'UTF-8') ?></td>
                                        <td><span class="admin-count"><?= (int) $utilizator['request_count'] ?></span></td>
                                        <td><?= htmlspecialchars($utilizator['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</section>

<script src="../js/admin-dashboard.js" defer></script>
<?php require __DIR__ . '/../components/footer.php'; ?>
