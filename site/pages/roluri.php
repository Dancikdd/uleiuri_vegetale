<?php
require_once __DIR__ . '/../components/config.php';
$root = '../';
$paginaActiva = 'roluri';
$titluPagina = 'Roluri și drepturi de acces';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Roluri și drepturi de acces</h1>
    <p>Rolul sesiunii curente: <strong><?= htmlspecialchars(ROLURI[$rolCurent], ENT_QUOTES, 'UTF-8') ?></strong>.</p>
    <p>Rolul este preluat din contul autentificat și nu poate fi schimbat prin URL.</p>

    <h2 class="subtitlu">Matricea drepturilor</h2>
    <div class="tabel-responsiv">
        <table>
            <thead><tr><th>Acțiune</th><th>Vizitator</th><th>Client</th><th>Administrator</th></tr></thead>
            <tbody>
                <tr><td>Consultarea produselor active</td><td>Da</td><td>Da</td><td>Da</td></tr>
                <tr><td>Plasarea unei comenzi</td><td>Nu</td><td>Da</td><td>Da</td></tr>
                <tr><td>Consultarea comenzilor proprii</td><td>Nu</td><td>Da</td><td>Da</td></tr>
                <tr><td>Consultarea tuturor comenzilor</td><td>Nu</td><td>Nu</td><td>Da</td></tr>
                <tr><td>Gestionarea categoriilor și elementelor</td><td>Nu</td><td>Nu</td><td>Da</td></tr>
                <tr><td>Aprobarea sau respingerea comenzilor</td><td>Nu</td><td>Nu</td><td>Da</td></tr>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
