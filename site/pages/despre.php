<?php
$root         = '../';
$paginaActiva = 'despre';
$titluPagina  = 'Despre noi';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Despre noi</h1>
    <p><?= NUME_PROIECT ?> este un magazin care vinde uleiuri vegetale de calitate: de la uleiul de floarea-soarelui pentru gătit zilnic, până la uleiuri presate la rece pentru salate.</p>
    <p>Scopul nostru este ca fiecare client să găsească rapid uleiul potrivit, iar administratorul să poată gestiona simplu stocul și comenzile.</p>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>