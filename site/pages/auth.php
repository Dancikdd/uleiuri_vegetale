<?php
$root         = '../';
$paginaActiva = '';
$titluPagina  = 'Autentificare';
require __DIR__ . '/../components/header.php';
?>

<section class="pagina pagina-login">
    <h1>Autentificare</h1>
    <p>Introduceți datele contului pentru a vă conecta.</p>

    <!-- Doar interfața: verificarea utilizatorului și a parolei nu se realizează în această lucrare -->
    <form class="formular" action="#" method="post">
        <label for="email">E-mail</label>
        <input type="email" id="email" name="email" placeholder="Nume@gmail.com" required>

        <label for="parola">Parolă</label>
        <input type="password" id="parola" name="parola" placeholder="Introduceti o parola" required>

        <button type="submit" class="buton">Conectare</button>
    </form>
</section>

<?php require __DIR__ . '/../components/footer.php'; ?>