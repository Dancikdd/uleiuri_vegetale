<?php
require_once __DIR__ . '/../components/config.php';
$root         = '../';
$paginaActiva = '';
$titluPagina  = 'Contul meu';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <?php if (!in_array($rolCurent, ['client', 'administrator'], true)): ?>
        <h1>Acces restricționat</h1>
        <p class="mesaj mesaj-eroare">Pagina „Contul meu” este disponibilă rolurilor Client și Administrator.</p>
        <p><a href="<?= url_cu_rol('../pages/roluri.php') ?>">Selectați un rol demonstrativ</a>.</p>
    <?php else: ?>
        <h1>Contul meu</h1>
        <p>Această pagină este disponibilă rolului <strong><?= htmlspecialchars(ROLURI[$rolCurent], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
        <h2 class="subtitlu">Comenzi demonstrative</h2>
        <p>Nu există comenzi salvate. Formularul de comandă prelucrează datele doar pentru solicitarea curentă.</p>
        <p><a class="buton buton-link" href="<?= url_cu_rol('../pages/comanda.php') ?>">Trimite o comandă demonstrativă</a></p>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
