<?php
require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/product_images.php';
$root         = '../';
$paginaActiva = 'produse';
$titluPagina  = 'Produse';
$termenCautare = $_GET['q'] ?? '';
if (!is_string($termenCautare)) {
    $termenCautare = '';
}

$produse = [];
$eroareBazaDate = false;
try {
    require_once __DIR__ . '/../components/database.php';
    $sql = 'SELECT i.id, i.name, i.description, i.image_path, i.price, i.quantity_available,
                   c.name AS category_name
            FROM items i
            JOIN categories c ON c.id = i.category_id
            WHERE i.is_active = 1';
    if ($termenCautare !== '') {
        $sql .= ' AND (i.name LIKE :name_term OR c.name LIKE :category_term)';
    }
    $sql .= ' ORDER BY i.name';

    $stmt = database()->prepare($sql);
    if ($termenCautare !== '') {
        $likeTerm = '%' . $termenCautare . '%';
        $stmt->execute(['name_term' => $likeTerm, 'category_term' => $likeTerm]);
    } else {
        $stmt->execute();
    }
    $produse = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('Product catalog database error: ' . $e->getMessage());
    $eroareBazaDate = true;
}

require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Produse</h1>
    <p>Alegeți dintre uleiurile disponibile în magazinul nostru.</p>

    <form class="formular formular-cautare" action="<?= $root ?>pages/produse.php" method="get">
        <label for="cautare">Caută după denumire</label>
        <input type="search" id="cautare" name="q"
               value="<?= htmlspecialchars(is_string($_GET['q'] ?? '') ? ($_GET['q'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Ex.: măsline">
        <button type="submit" class="buton">Caută</button>
    </form>

    <?php if ($eroareBazaDate): ?>
        <p class="mesaj mesaj-eroare" role="alert">Catalogul nu este disponibil momentan. Verificați conexiunea la baza de date.</p>
    <?php endif; ?>
    <?php if ($termenCautare !== ''): ?>
        <p class="mesaj">Rezultatele căutării pentru „<?= htmlspecialchars($termenCautare, ENT_QUOTES, 'UTF-8') ?>”.</p>
    <?php endif; ?>

    <div class="carduri">
            <?php foreach ($produse as $p): ?>
                <a class="produs-card-link" href="produs.php?id=<?= (int) $p['id'] ?>">
                    <article class="card produs-card">
                        <div class="produs-card-imagine">
                            <img src="<?= $root . htmlspecialchars(cale_imagine_produs($p['image_path'] ?? null, $p['name']), ENT_QUOTES, 'UTF-8') ?>"
                                 alt="<?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                        </div>
                        <div class="produs-card-continut">
                            <span class="produs-categorie"><?= htmlspecialchars($p['category_name'], ENT_QUOTES, 'UTF-8') ?></span>
                            <h2><?= htmlspecialchars($p['name'], ENT_QUOTES, 'UTF-8') ?></h2>
                            <p class="produs-card-descriere"><?= htmlspecialchars($p['description'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="produs-card-inferior">
                                <span class="pret"><?= number_format((float) $p['price'], 2) ?> lei</span>
                                <span class="produs-card-stoc <?= (int) $p['quantity_available'] > 0 ? 'in-stoc' : 'fara-stoc' ?>">
                                    <?= (int) $p['quantity_available'] > 0 ? 'În stoc' : 'Indisponibil' ?>
                                </span>
                            </div>
                            <span class="produs-card-detalii">Vezi detaliile <span aria-hidden="true">→</span></span>
                        </div>
                    </article>
                </a>

            <?php endforeach; ?>

    </div>
    <?php if (!$eroareBazaDate && !$produse): ?>
        <p class="mesaj">Nu am găsit produse care să corespundă căutării.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
