<?php
require_once __DIR__ . '/../components/config.php';
$root         = '../';
$paginaActiva = 'produse';
$titluPagina  = 'Produse';

$produse = [
    ['nume' => 'Ulei de floarea-soarelui', 'tip' => 'rafinat',        'pret' => 35.99, 'bio' => false],
    ['nume' => 'Ulei de măsline',          'tip' => 'extravirgin',    'pret' => 89.90, 'bio' => true],
    ['nume' => 'Ulei de dovleac',          'tip' => 'presat la rece', 'pret' => 74.50, 'bio' => true],
    ['nume' => 'Ulei de in',               'tip' => 'presat la rece', 'pret' => 58.00, 'bio' => true],
];

require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Produse</h1>
    <p>Alegeți dintre uleiurile disponibile în magazinul nostru.</p>

    <form class="formular formular-cautare" action="<?= $root ?>pages/produse.php" method="get">
        <input type="hidden" name="rol" value="<?= htmlspecialchars($rolCurent, ENT_QUOTES, 'UTF-8') ?>">
        <label for="cautare">Caută după denumire</label>
        <input type="search" id="cautare" name="q"
               value="<?= htmlspecialchars(is_string($_GET['q'] ?? '') ? ($_GET['q'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?>"
               placeholder="Ex.: măsline">
        <button type="submit" class="buton">Caută</button>
    </form>

    <?php
    $termenCautare = $_GET['q'] ?? '';
    if (!is_string($termenCautare)) {
        $termenCautare = '';
    }
    $produseAfisate = array_filter($produse, static function ($produs) use ($termenCautare) {
        return $termenCautare === ''
            || stripos($produs['nume'], $termenCautare) !== false;
    });
    ?>
    <?php if ($termenCautare !== ''): ?>
        <p class="mesaj">Rezultatele căutării pentru „<?= htmlspecialchars($termenCautare, ENT_QUOTES, 'UTF-8') ?>”.</p>
    <?php endif; ?>

    <div class="carduri">
            <?php foreach ($produseAfisate as $p): ?>

                <?php
                $bio = "";

                if ($p['bio']) {
                    $bio = " · BIO";
                }
                ?>

                <article class="card">
                    <h3><?= $p['nume'] ?></h3>

                    <p>
                        Tip: <?= $p['tip'] ?><?= $bio ?>
                    </p>

                    <p class="pret">
                        <?= number_format($p['pret'], 2) ?> lei/litru
                    </p>
                </article>

            <?php endforeach; ?>

    </div>
    <?php if (!$produseAfisate): ?>
        <p class="mesaj">Nu am găsit produse care să corespundă căutării.</p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>