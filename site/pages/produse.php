<?php
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

    <div class="carduri">
        <?php foreach ($produse as $p): ?>
            <article class="card">
                <h3><?= $p['nume'] ?></h3>
                <p>Tip: <?= $p['tip'] ?><?= $p['bio'] ? ' &middot; <strong class="bio">BIO</strong>' : '' ?></p>
                <p class="pret"><?= number_format($p['pret'], 2) ?> lei/litru</p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>