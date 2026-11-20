<?php
require_once __DIR__ . '/../components/config.php';
cere_roluri(['client', 'administrator']);
$root = '../';
$paginaActiva = 'comanda';
$titluPagina = 'Comandă';
$produse = [];
$erori = [];
$mesajSucces = $_SESSION['request_success'] ?? null;
unset($_SESSION['request_success']);

$postedItemId = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['item_id'] ?? '')
    : ($_GET['item_id'] ?? '');
$itemId = is_string($postedItemId) ? (filter_var($postedItemId, FILTER_VALIDATE_INT) ?: 0) : 0;
$postedQuantity = $_POST['quantity'] ?? '';
$cantitate = is_string($postedQuantity) ? (filter_var($postedQuantity, FILTER_VALIDATE_INT) ?: 0) : 0;

if (in_array($rolCurent, ['client', 'administrator'], true)) {
    try {
        require_once __DIR__ . '/../components/database.php';
        $produse = database()->query(
            'SELECT id, name, price, quantity_available
             FROM items
             WHERE is_active = 1 AND quantity_available > 0
             ORDER BY name'
        )->fetchAll();
    } catch (Throwable $e) {
        error_log('Request product list error: ' . $e->getMessage());
        $erori['general'] = 'Catalogul nu este disponibil momentan.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $produsAccesatDinCatalog = false;
    foreach ($produse as $produsDisponibil) {
        if ((int) $produsDisponibil['id'] === $itemId) {
            $produsAccesatDinCatalog = true;
            break;
        }
    }
    if (!$produsAccesatDinCatalog) {
        header('Location: produse.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!in_array($rolCurent, ['client', 'administrator'], true)) {
        http_response_code(403);
        $erori['general'] = 'Autentificați-vă pentru a plasa o comandă.';
    } elseif (!token_csrf_valid()) {
        $erori['general'] = 'Formularul a expirat. Reîncărcați pagina și încercați din nou.';
    } else {
        if ($itemId < 1) {
            $erori['item_id'] = 'Selectați un produs.';
        }
        if ($cantitate < 1 || $cantitate > 1000) {
            $erori['quantity'] = 'Introduceți o cantitate între 1 și 1000.';
        }

        if (!$erori) {
            try {
                $db = database();
                $db->beginTransaction();

                $itemStmt = $db->prepare(
                    'SELECT id, price, quantity_available
                     FROM items
                     WHERE id = :id AND is_active = 1
                     FOR UPDATE'
                );
                $itemStmt->execute(['id' => $itemId]);
                $item = $itemStmt->fetch();

                if (!$item) {
                    $erori['item_id'] = 'Produsul selectat nu este disponibil.';
                } elseif ($cantitate > (int) $item['quantity_available']) {
                    $erori['quantity'] = 'Cantitatea depășește stocul disponibil ('
                        . (int) $item['quantity_available'] . ').';
                }

                if ($erori) {
                    $db->rollBack();
                } else {
                    $requestStmt = $db->prepare(
                        "INSERT INTO requests (user_id, status)
                         VALUES (:user_id, 'pending')"
                    );
                    $requestStmt->execute(['user_id' => $utilizatorCurent['id']]);
                    $requestId = (int) $db->lastInsertId();

                    $lineStmt = $db->prepare(
                        'INSERT INTO request_items (request_id, item_id, quantity, unit_price)
                         VALUES (:request_id, :item_id, :quantity, :unit_price)'
                    );
                    $lineStmt->execute([
                        'request_id' => $requestId,
                        'item_id' => $itemId,
                        'quantity' => $cantitate,
                        'unit_price' => $item['price'],
                    ]);

                    $db->commit();
                    $_SESSION['request_success'] = 'Comanda #' . $requestId . ' a fost înregistrată.';
                    header('Location: comanda.php?item_id=' . $itemId);
                    exit;
                }
            } catch (Throwable $e) {
                if (isset($db) && $db->inTransaction()) {
                    $db->rollBack();
                }
                error_log('Request creation error: ' . $e->getMessage());
                $erori['general'] = 'Comanda nu a putut fi salvată momentan.';
            }
        }
    }
}

require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Plasează o comandă</h1>
    <?php if ($utilizatorCurent): ?>
        <p>Comanda va fi salvată în contul <?= htmlspecialchars($utilizatorCurent['email'], ENT_QUOTES, 'UTF-8') ?> cu statutul „În așteptare”.</p>
        <?php if ($mesajSucces): ?>
            <p class="mesaj mesaj-succes" role="status"><?= htmlspecialchars($mesajSucces, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>
        <?php if (isset($erori['general'])): ?>
            <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($erori['general'], ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form class="formular formular-lat" action="comanda.php" method="post">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
            <label for="item_id">Produs</label>
            <select id="item_id" name="item_id" required>
                <option value="">Alegeți un produs</option>
                <?php foreach ($produse as $produs): ?>
                    <option value="<?= (int) $produs['id'] ?>" <?= $itemId === (int) $produs['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($produs['name'], ENT_QUOTES, 'UTF-8') ?> —
                        <?= number_format((float) $produs['price'], 2) ?> lei
                        (stoc: <?= (int) $produs['quantity_available'] ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (isset($erori['item_id'])): ?>
                <small class="mesaj-eroare"><?= htmlspecialchars($erori['item_id'], ENT_QUOTES, 'UTF-8') ?></small>
            <?php endif; ?>

            <label for="quantity">Cantitate</label>
            <input type="number" id="quantity" name="quantity" min="1" max="1000" required
                   value="<?= $cantitate > 0 ? $cantitate : 1 ?>">
            <?php if (isset($erori['quantity'])): ?>
                <small class="mesaj-eroare"><?= htmlspecialchars($erori['quantity'], ENT_QUOTES, 'UTF-8') ?></small>
            <?php endif; ?>

            <button type="submit" class="buton" <?= !$produse ? 'disabled' : '' ?>>Plasează comanda</button>
        </form>
        <p><a href="contul-meu.php">Consultă comenzile mele</a></p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
