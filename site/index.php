<?php
$root         = '';
$paginaActiva = 'acasa';
$titluPagina  = 'Acasă';

$slide = [
    ['img' => 'img/floarea-soarelui.jpg', 'titlu' => 'Ulei de floarea-soarelui', 'text' => 'Rafinat, potrivit pentru prăjit și coacere.'],
    ['img' => 'img/masline.jpg',          'titlu' => 'Ulei de măsline',          'text' => 'Extravirgin, presat la rece, pentru salate.'],
    ['img' => 'img/dovleac.jpeg',          'titlu' => 'Ulei de dovleac',          'text' => 'Presat la rece, cu gust intens de nucă.'],
];

require __DIR__ . '/components/header.php';
?>

<section class="carusel" aria-roledescription="carusel" aria-label="Produse recomandate">
    <?php foreach ($slide as $i => $s): ?>
        <figure class="slide<?= $i === 0 ? ' activ' : '' ?>">
            <img src="<?= $root . $s['img'] ?>" alt="<?= $s['titlu'] ?>">
            <figcaption>
                <h2><?= $s['titlu'] ?></h2>
                <p><?= $s['text'] ?></p>
            </figcaption>
        </figure>
    <?php endforeach; ?>

    <button class="sageata prev" type="button" aria-label="Imaginea anterioară">&#10094;</button>
    <button class="sageata next" type="button" aria-label="Imaginea următoare">&#10095;</button>

    <div class="puncte">
        <?php foreach ($slide as $i => $s): ?>
            <button class="punct<?= $i === 0 ? ' activ' : '' ?>" type="button" aria-label="Imaginea <?= $i + 1 ?>"></button>
        <?php endforeach; ?>
    </div>
</section>

<section class="intro">
    <h1>Bine ați venit la <?= NUME_PROIECT ?></h1>
    <p>Magazinul nostru oferă uleiuri vegetale de toate tipurile: rafinate, presate la rece și certificate bio.</p>
</section>

<section class="carduri">
    <article class="card"><h3>Produse naturale</h3><p>Uleiuri obținute din materie primă atent selecționată.</p></article>
    <article class="card"><h3>Certificat bio</h3><p>Găsiți ușor produsele cu certificare ecologică.</p></article>
    <article class="card"><h3>Livrare rapidă</h3><p>Comenzile sunt pregătite și livrate în 1–2 zile.</p></article>
</section>

<?php require __DIR__ . '/components/footer.php'; ?>
