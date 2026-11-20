<?php
require_once __DIR__ . '/config.php';

$root         = $root ?? '';           
$titluPagina  = $titluPagina ?? 'Acasă';
$paginaActiva = $paginaActiva ?? '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titluPagina) ?> | <?= NUME_PROIECT ?></title>
    <link rel="stylesheet" href="<?= $root ?>css/style.css">
</head>
<body>

<header class="site-header">
    <button class="icon-btn" id="menuToggle" type="button"
            aria-label="Deschide meniul" aria-expanded="false" aria-controls="sidebar">
        <span class="hamburger"></span>
    </button>

    <a class="brand" href="<?= url_cu_rol($root . 'index.php') ?>"><?= NUME_PROIECT ?></a>

    <a class="icon-btn" href="<?= url_cu_rol($root . ($utilizatorCurent ? 'pages/contul-meu.php' : 'pages/auth.php')) ?>"
       aria-label="<?= $utilizatorCurent ? 'Profilul meu' : 'Autentificare sau creare cont' ?>">
        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="8" r="4"></circle>
            <path d="M4 21c0-4.4 3.6-8 8-8s8 3.6 8 8"></path>
        </svg>
    </a>
</header>

<div class="overlay" id="overlay"></div>
<nav class="sidebar" id="sidebar" aria-label="Meniu principal" aria-hidden="true">
    <div class="sidebar-cap">
        <span>Meniu</span>
        <button class="icon-btn" id="menuClose" type="button" aria-label="Închide meniul">&times;</button>
    </div>
<ul>
    <?php foreach (PAGINI as $cheie => $pagina): ?>
        <?php if ($cheie === 'comanda') { continue; } ?>

        <li>
            <a href="<?= url_cu_rol($root . $pagina['cale']) ?>"
                <?php
                if ($cheie === $paginaActiva) {
                    echo 'class="activ"';
                }
                ?>>
                <?= $pagina['titlu'] ?>
            </a>
        </li>

    <?php endforeach; ?>
    <?php if ($rolCurent === 'client' || $rolCurent === 'administrator'): ?>
        <li><a href="<?= url_cu_rol($root . 'pages/contul-meu.php') ?>">Contul meu</a></li>
    <?php endif; ?>
    <?php if ($rolCurent === 'administrator'): ?>
        <li><a href="<?= url_cu_rol($root . 'pages/administrare.php') ?>">Administrare</a></li>
    <?php endif; ?>
</ul>
</nav>

<main>
