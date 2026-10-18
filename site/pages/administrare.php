<?php
require_once __DIR__ . '/../components/config.php';
$root         = '../';
$paginaActiva = '';
$titluPagina  = 'Administrare';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <?php if ($rolCurent !== 'administrator'): ?>
        <h1>Acces restricționat</h1>
        <p class="mesaj mesaj-eroare">Panoul de administrare este disponibil doar rolului Administrator.</p>
        <p><a href="<?= url_cu_rol('../pages/roluri.php') ?>">Selectați rolul Administrator pentru demonstrație</a>.</p>
    <?php else: ?>
        <h1>Panou de administrare</h1>
        <p>Pagina este afișată deoarece rolul simulat este Administrator. Drepturile nu sunt protejate prin autentificare reală.</p>
        <h2 class="subtitlu">Catalog demonstrativ</h2>
        <ul class="lista">
            <li><strong>Produse în catalog:</strong> 4</li>
            <li><strong>Acțiuni permise în exemplu:</strong> consultarea catalogului și vizualizarea paginilor demonstrative.</li>
            <li><strong>Persistența datelor:</strong> dezactivată; nu este conectată o bază de date.</li>
        </ul>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
