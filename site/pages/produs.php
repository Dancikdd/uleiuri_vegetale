<?php
require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/product_images.php';

$root = '../';
$paginaActiva = 'produse';
$idProdus = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$idProdus || $idProdus < 1) {
    pagina_indisponibila();
}

try {
    require_once __DIR__ . '/../components/database.php';
    $stmt = database()->prepare(
        'SELECT i.id, i.name, i.description, i.image_path, i.price, i.quantity_available,
                c.name AS category_name
         FROM items i
         JOIN categories c ON c.id = i.category_id
         WHERE i.id = :id AND i.is_active = 1
         LIMIT 1'
    );
    $stmt->execute(['id' => $idProdus]);
    $produs = $stmt->fetch();
} catch (Throwable $e) {
    error_log('Product detail database error: ' . $e->getMessage());
    http_response_code(503);
    $produs = null;
    $eroare = 'Detaliile produsului nu sunt disponibile momentan.';
}

if (!$produs && !isset($eroare)) {
    pagina_indisponibila();
}

$titluPagina = $produs ? $produs['name'] : 'Produs indisponibil';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina produs-detalii-pagina">
    <p class="produs-breadcrumb"><a href="produse.php">← Înapoi la produse</a></p>
    <?php if (isset($eroare)): ?>
        <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroare, ENT_QUOTES, 'UTF-8') ?></p>
    <?php elseif ($produs): ?>
        <article class="produs-detalii">
            <div class="produs-detalii-imagine">
                <img src="<?= $root . htmlspecialchars(cale_imagine_produs($produs['image_path'] ?? null, $produs['name']), ENT_QUOTES, 'UTF-8') ?>"
                     alt="<?= htmlspecialchars($produs['name'], ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="produs-detalii-info">
                <span class="produs-categorie"><?= htmlspecialchars($produs['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <h1><?= htmlspecialchars($produs['name'], ENT_QUOTES, 'UTF-8') ?></h1>
                <p class="produs-detalii-pret"><?= number_format((float) $produs['price'], 2) ?> lei</p>
                <p class="produs-detalii-stoc <?= (int) $produs['quantity_available'] > 0 ? 'in-stoc' : 'fara-stoc' ?>">
                    <?= (int) $produs['quantity_available'] > 0
                        ? 'Disponibil în stoc: ' . (int) $produs['quantity_available'] . ' buc.'
                        : 'Momentan indisponibil' ?>
                </p>
                <div class="produs-descriere">
                    <h2>Despre produs</h2>
                    <p><?= nl2br(htmlspecialchars($produs['description'] ?: 'Nu a fost adăugată o descriere.', ENT_QUOTES, 'UTF-8')) ?></p>
                </div>
                <?php if ((int) $produs['quantity_available'] > 0): ?>
                    <?php if ($utilizatorCurent): ?>
                        <a class="buton buton-link" href="comanda.php?item_id=<?= (int) $produs['id'] ?>">Plasează comanda</a>
                    <?php else: ?>
                        <a class="buton buton-link" href="auth.php">Autentifică-te pentru a comanda</a>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="buton" type="button" disabled>Indisponibil</button>
                <?php endif; ?>
            </div>
        </article>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
