<?php
require_once __DIR__ . '/../components/config.php';
if ($utilizatorCurent) {
    header('Location: contul-meu.php');
    exit;
}
$root = '../';
$paginaActiva = '';
$titluPagina = 'Cont';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Contul tău</h1>
    <?php if (isset($_GET['iesire'])): ?>
        <p class="mesaj mesaj-succes" role="status">Ai ieșit din cont.</p>
    <?php endif; ?>
    <p>Alege cum vrei să continui.</p>
    <div class="carduri">
        <article class="card">
            <h2>Ai deja un cont?</h2>
            <p>Autentifică-te pentru a vedea profilul și comenzile tale.</p>
            <a class="buton buton-link" href="login.php">Logare</a>
        </article>
        <article class="card">
            <h2>Ești nou aici?</h2>
            <p>Creează un cont de client pentru a plasa comenzi.</p>
            <a class="buton buton-link" href="signup.php">Sign up</a>
        </article>
    </div>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
