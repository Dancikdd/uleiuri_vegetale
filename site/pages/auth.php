<?php
$root         = '../';
$paginaActiva = '';
$titluPagina  = 'Autentificare';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Autentificare</h1>
    <p>Proiectul nu implementează autentificare reală. Pentru demonstrarea drepturilor de acces, selectați un rol de test.</p>
    <p><a class="buton buton-link" href="<?= url_cu_rol('../pages/roluri.php') ?>">Alege rolul demonstrativ</a></p>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
