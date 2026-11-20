<?php
require_once __DIR__ . '/../components/config.php';
if ($utilizatorCurent) {
    header('Location: contul-meu.php');
    exit;
}

$root = '../';
$paginaActiva = '';
$titluPagina = 'Creare cont';
$erori = [];
$nume = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume = trim(is_string($_POST['name'] ?? null) ? $_POST['name'] : '');
    $email = trim(is_string($_POST['email'] ?? null) ? $_POST['email'] : '');
    $parola = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
    $confirmare = is_string($_POST['password_confirm'] ?? null) ? $_POST['password_confirm'] : '';

    if (!token_csrf_valid()) {
        $erori['general'] = 'Formularul a expirat. Reîncarcă pagina și încearcă din nou.';
    }
    if (preg_match('/\A.{1,100}\z/us', $nume) !== 1) {
        $erori['name'] = 'Numele este obligatoriu și poate avea maximum 100 de caractere.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $erori['email'] = 'Introdu o adresă de e-mail validă.';
    }
    if (strlen($parola) < 8 || strlen($parola) > 200) {
        $erori['password'] = 'Parola trebuie să aibă între 8 și 200 de caractere.';
    } elseif ($parola !== $confirmare) {
        $erori['password_confirm'] = 'Parolele nu coincid.';
    }

    if (!$erori) {
        try {
            require_once __DIR__ . '/../components/database.php';
            $stmt = database()->prepare(
                "INSERT INTO users (name, email, password_hash, role)
                 VALUES (:name, :email, :password_hash, 'client')"
            );
            $stmt->execute([
                'name' => $nume,
                'email' => $email,
                'password_hash' => password_hash($parola, PASSWORD_DEFAULT),
            ]);

            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id' => (int) database()->lastInsertId(),
                'name' => $nume,
                'email' => $email,
                'role' => 'client',
            ];
            unset($_SESSION['csrf_token']);
            header('Location: contul-meu.php');
            exit;
        } catch (PDOException $e) {
            error_log('Sign-up database error: ' . $e->getMessage());
            $erori['general'] = (($e->errorInfo[1] ?? null) === 1062)
                ? 'Există deja un cont cu această adresă de e-mail.'
                : 'Contul nu a putut fi creat momentan.';
        } catch (Throwable $e) {
            error_log('Sign-up error: ' . $e->getMessage());
            $erori['general'] = 'Contul nu a putut fi creat momentan.';
        }
    }
}

require __DIR__ . '/../components/header.php';
?>

<section class="pagina">
    <h1>Sign up</h1>
    <?php if (isset($erori['general'])): ?>
        <p class="mesaj mesaj-eroare" role="alert"><?= htmlspecialchars($erori['general'], ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form class="formular formular-lat" action="signup.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf(), ENT_QUOTES, 'UTF-8') ?>">
        <label for="name">Nume</label>
        <input type="text" id="name" name="name" required maxlength="100"
               value="<?= htmlspecialchars($nume, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (isset($erori['name'])): ?><small class="mesaj-eroare"><?= htmlspecialchars($erori['name'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" required maxlength="254"
               value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>">
        <?php if (isset($erori['email'])): ?><small class="mesaj-eroare"><?= htmlspecialchars($erori['email'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
        <label for="password">Parolă (minimum 8 caractere)</label>
        <input type="password" id="password" name="password" required minlength="8" maxlength="200" autocomplete="new-password">
        <?php if (isset($erori['password'])): ?><small class="mesaj-eroare"><?= htmlspecialchars($erori['password'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
        <label for="password_confirm">Confirmă parola</label>
        <input type="password" id="password_confirm" name="password_confirm" required minlength="8" maxlength="200" autocomplete="new-password">
        <?php if (isset($erori['password_confirm'])): ?><small class="mesaj-eroare"><?= htmlspecialchars($erori['password_confirm'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?>
        <button class="buton" type="submit">Creează contul</button>
    </form>
    <p>Ai deja cont? <a href="login.php">Logare</a></p>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>
