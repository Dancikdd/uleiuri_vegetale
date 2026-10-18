<?php
require_once __DIR__ . '/../components/config.php';
$root         = '../';
$paginaActiva = 'roluri';
$titluPagina  = 'Roluri și drepturi de acces';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Roluri și drepturi de acces</h1>
    <p>Rolul selectat este <strong><?= htmlspecialchars(ROLURI[$rolCurent], ENT_QUOTES, 'UTF-8') ?></strong>.
        Se simulează prin parametrul URL <code>rol</code>; nu există conturi, autentificare reală sau bază de date.</p>

    <form class="formular" action="../pages/roluri.php" method="get">
        <label for="rol">Alegeți un rol pentru demonstrație</label>
        <select id="rol" name="rol">
            <?php foreach (ROLURI as $cheie => $nume): ?>
                <option value="<?= htmlspecialchars($cheie, ENT_QUOTES, 'UTF-8') ?>"
                    <?= $cheie === $rolCurent ? 'selected' : '' ?>>
                    <?= htmlspecialchars($nume, ENT_QUOTES, 'UTF-8') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="buton">Aplică rolul</button>
    </form>

    <h2 class="subtitlu">Matricea drepturilor</h2>
    <div class="tabel-responsiv">
        <table>
            <thead>
                <tr><th>Rol</th><th>Pagini și acțiuni disponibile</th></tr>
            </thead>
            <tbody>
                <tr><th>Vizitator</th><td>Acasă, catalogul de produse, căutare GET, informații și contact.</td></tr>
                <tr><th>Client</th><td>Toate paginile vizitatorului, formular de comandă POST și pagina demonstrativă „Contul meu”.</td></tr>
                <tr><th>Administrator</th><td>Pagini de client și panoul demonstrativ de administrare.</td></tr>
            </tbody>
        </table>
    </div>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
