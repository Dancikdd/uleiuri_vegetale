<?php
/**
 * Preluarea datelor pentru panoul de administrare:
 * - date pentru formularele de editare (GET)
 * - catalog (categorii și produse)
 * - date administrative (utilizatori, comenzi)
 * - calcul statistici agregate
 */

// ── 1. Încărcare date pentru editare (GET) ──────────────────────────────────
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

// ── 2. Interogări date catalog ─────────────────────────────────────────────
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

// ── 3. Interogări utilizatori și solicitări ─────────────────────────────────
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

// ── 4. Calcul statistici ───────────────────────────────────────────────────
$numarCategorii   = count($categorii);
$numarProduse     = count($produse);
$numarActive      = count(array_filter($produse, static fn($produs) => (int) $produs['is_active'] === 1));
$numarSolicitari  = count($solicitariAdmin);
$numarUtilizatori = count($utilizatori);
