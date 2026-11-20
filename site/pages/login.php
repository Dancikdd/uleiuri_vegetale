<?php
require_once __DIR__ . '/../components/config.php';
if ($utilizatorCurent) {
    header('Location: contul-meu.php');
    exit;
}

$root = '../';
$paginaActiva = '';
$titluPagina = 'Logare';
$erori = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!token_csrf_valid()) {
        $erori[] = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
    } else {
        $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
        $parola = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $erori[] = 'Introdu o adresă de e-mail validă.';
        }
        if ($parola === '') {
            $erori[] = 'Introdu parola.';
        }

        if (!$erori) {
            try {
                require_once __DIR__ . '/../components/database.php';
                $stmt = database()->prepare(
                    'SELECT id, name, email, password_hash, role FROM users WHERE email = :email LIMIT 1'
                );
                $stmt->execute(['email' => $email]);
                $user = $stmt->fetch();
                if ($user && password_verify($parola, $user['password_hash'])) {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'name' => $user['name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                    ];
                    unset($_SESSION['csrf_token']);
                    header('Location: contul-meu.php');
                    exit;
                }
                $erori[] = 'E-mailul sau parola nu sunt corecte.';
            } catch (Throwable $e) {
                error_log('Login database error: ' . $e->getMessage());
                $erori[] = 'Autentificarea nu este disponibilă momentan.';
            }
        }
    }
}

require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Logare</h1>
    <?php if ($erori): ?>
        <div class="mesaj mesaj-eroare" role="alert">
            <?php foreach ($erori as $eroare): ?>
                <p><?= htmlspecialchars($eroare, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form class="formular formular-lat" action="login.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required maxlength="254" autocomplete="username"
               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        <label for="password">Parolă</label>
        <input type="password" id="password" name="password" required autocomplete="current-password">
        <button class="buton" type="submit">Logare</button>
    </form>
    <p><a href="auth.php">Înapoi la alegere</a></p>
    <p>Conturi demonstrative: <code>admin@uleiuri.local</code>, <code>ana@example.com</code>, <code>mihai@example.com</code>. Parola: <code>Lab4Demo2026!</code>.</p>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
