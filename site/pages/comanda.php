<?php
require_once __DIR__ . '/../components/config.php';
$root         = '../';
$paginaActiva = 'comanda';
$titluPagina  = 'Comandă';
$produseDisponibile = [
    'Ulei de floarea-soarelui',
    'Ulei de măsline',
    'Ulei de dovleac',
    'Ulei de in',
];
$erori = [];
$comanda = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($rolCurent, ['client', 'administrator'], true)) {
    $nume = trim(is_string($_POST['nume'] ?? null) ? $_POST['nume'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
    $produs = is_string($_POST['produs'] ?? null) ? $_POST['produs'] : '';

    if ($nume === '') {
        $erori[] = 'Introduceți numele.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erori[] = 'Introduceți o adresă de e-mail validă.';
    }
    if (!in_array($produs, $produseDisponibile, true)) {
        $erori[] = 'Selectați un produs din listă.';
    }

    if (!$erori) {
        $comanda = ['nume' => $nume, 'email' => $email, 'produs' => $produs];
    }
}

require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Formular de comandă</h1>
    <?php if (!in_array($rolCurent, ['client', 'administrator'], true)): ?>
        <p class="mesaj mesaj-eroare">Comenzile sunt disponibile rolurilor Client și Administrator în această demonstrație.</p>
        <p><a href="<?= url_cu_rol('../pages/roluri.php') ?>">Selectați un rol demonstrativ</a>.</p>
    <?php else: ?>
        <p>Completați formularul. Datele sunt validate și afișate doar în această solicitare, fără salvare permanentă.</p>

        <?php if ($erori): ?>
            <div class="mesaj mesaj-eroare" role="alert">
                <p>Verificați datele introduse:</p>
                <ul>
                    <?php foreach ($erori as $eroare): ?>
                        <li><?= htmlspecialchars($eroare, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($comanda): ?>
            <div class="mesaj mesaj-succes" role="status">
                <h2>Comandă demonstrativă prelucrată</h2>
                <p>Nume: <?= htmlspecialchars($comanda['nume'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>E-mail: <?= htmlspecialchars($comanda['email'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>Produs ales: <?= htmlspecialchars($comanda['produs'], ENT_QUOTES, 'UTF-8') ?></p>
                <p>Comanda nu a fost salvată.</p>
            </div>
        <?php endif; ?>

        <form class="formular formular-lat" action="<?= url_cu_rol('../pages/comanda.php') ?>" method="post">
            <label for="nume">Nume</label>
            <input type="text" id="nume" name="nume" required maxlength="100"
                   value="<?= htmlspecialchars(is_string($_POST['nume'] ?? '') ? ($_POST['nume'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?>">
            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required maxlength="254"
                   value="<?= htmlspecialchars(is_string($_POST['email'] ?? '') ? ($_POST['email'] ?? '') : '', ENT_QUOTES, 'UTF-8') ?>">
            <label for="produs">Produs dorit</label>
            <select id="produs" name="produs" required>
                <option value="">Alegeți un produs</option>
                <?php foreach ($produseDisponibile as $numeProdus): ?>
                    <option value="<?= htmlspecialchars($numeProdus, ENT_QUOTES, 'UTF-8') ?>"
                        <?= (($_POST['produs'] ?? '') === $numeProdus) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($numeProdus, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="buton">Prelucrează comanda</button>
        </form>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
