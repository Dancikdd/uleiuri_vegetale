<?php
require_once __DIR__ . '/../components/config.php';
cere_roluri(['client', 'administrator']);
$root = '../';
$paginaActiva = '';
$titluPagina = 'Profilul meu';
$solicitari = [];
$eroare = null;
$eroareLogout = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    if (!token_csrf_valid()) {
        http_response_code(400);
        $eroareLogout = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
    } else {
        $_SESSION = [];
        session_regenerate_id(true);
        header('Location: auth.php?iesire=1');
        exit;
    }
}

if ($utilizatorCurent) {
    try {
        require_once __DIR__ . '/../components/database.php';
        $stmt = database()->prepare(
            "SELECT r.id, r.created_at, r.status,
                    COALESCE(SUM(ri.quantity * ri.unit_price), 0) AS total,
                    GROUP_CONCAT(CONCAT(i.name, ' × ', ri.quantity)
                                 ORDER BY i.name SEPARATOR ', ') AS products
             FROM requests r
             LEFT JOIN request_items ri ON ri.request_id = r.id
             LEFT JOIN items i ON i.id = ri.item_id
             WHERE r.user_id = :user_id
             GROUP BY r.id, r.created_at, r.status
             ORDER BY r.created_at DESC"
        );
        $stmt->execute(['user_id' => $utilizatorCurent['id']]);
        $solicitari = $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('Own requests database error: ' . $e->getMessage());
        $eroare = 'Comenzile nu sunt disponibile momentan.';
    }
}

require __DIR__ . '/../components/header.php';
$eticheteStare = [
    'pending' => 'În așteptare',
    'approved' => 'Aprobată',
    'rejected' => 'Respinsă',
    'cancelled' => 'Anulată',
];
?>

<section class="pagina profil-dashboard">
    <?php if (!$utilizatorCurent): ?>
        <h1>Acces restricționat</h1>
        <p class="mesaj mesaj-eroare">Autentificați-vă pentru a consulta comenzile proprii.</p>
        <p><a class="buton buton-link" href="auth.php">Autentificare</a></p>
    <?php else: ?>
        <header class="profil-hero">
            <div>
                <span class="profil-eyebrow">Spațiul tău personal</span>
                <h1>Bun venit, <?= htmlspecialchars($utilizatorCurent['name'], ENT_QUOTES, 'UTF-8') ?>!</h1>
                <p>Aici găsești datele contului și istoricul comenzilor tale.</p>
            </div>
            <a class="buton buton-link profil-comanda-link" href="produse.php">Vezi produsele</a>
        </header>

        <?php if ($eroareLogout): ?>
            <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroareLogout, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <div class="profil-grid">
            <section class="profil-account">
                <div class="profil-section-heading"><div><span class="profil-eyebrow">Informații</span><h2>Datele contului</h2></div></div>
                <dl class="profil-details">
                    <div><dt>Nume</dt><dd><?= htmlspecialchars($utilizatorCurent['name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Adresă de e-mail</dt><dd><?= htmlspecialchars($utilizatorCurent['email'], ENT_QUOTES, 'UTF-8') ?></dd></div>
                    <div><dt>Tip cont</dt><dd><?= htmlspecialchars(ROLURI[$rolCurent] ?? 'Client', ENT_QUOTES, 'UTF-8') ?></dd></div>
                </dl>
                <form class="profil-logout" action="contul-meu.php" method="post">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
                    <button class="profil-logout-button" type="submit" name="logout" value="1">Deconectare</button>
                </form>
            </section>

            <section class="profil-orders">
                <div class="profil-section-heading">
                    <div><span class="profil-eyebrow">Istoric</span><h2>Comenzile mele</h2></div>
                    <span class="profil-order-count"><?= count($solicitari) ?> comenzi</span>
                </div>
                <?php if ($eroare): ?>
                    <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($eroare, ENT_QUOTES, 'UTF-8') ?></p>
                <?php elseif (!$solicitari): ?>
                    <div class="profil-empty">
                        <h3>Nu ai încă nicio comandă</h3>
                        <p>Comenzile plasate vor apărea aici, împreună cu statutul și detaliile lor.</p>
                        <a href="produse.php">Alege produse</a>
                    </div>
                <?php else: ?>
                    <div class="profil-order-list">
                        <?php foreach ($solicitari as $solicitare): ?>
                            <article class="profil-order-card">
                                <div class="profil-order-top">
                                    <h3>Comanda #<?= (int) $solicitare['id'] ?></h3>
                                    <span class="profil-order-status status-<?= htmlspecialchars($solicitare['status'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($eticheteStare[$solicitare['status']] ?? $solicitare['status'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </div>
                                <p class="profil-order-date">Plasată la <?= htmlspecialchars($solicitare['created_at'], ENT_QUOTES, 'UTF-8') ?></p>
                                <div class="profil-order-products">
                                    <span>Produse comandate</span>
                                    <p><?= htmlspecialchars($solicitare['products'] ?? 'Fără produse', ENT_QUOTES, 'UTF-8') ?></p>
                                </div>
                                <div class="profil-order-total"><span>Total</span><strong><?= number_format((float) $solicitare['total'], 2) ?> lei</strong></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
