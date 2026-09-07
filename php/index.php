<?php
 // Sarcina 2
    $denumireProiect = "Sistem de gestionare a unui magazin de uleiuri vegetale";
    $autor = "Ursan Daniel";
    $grupa = "PAPP-231";
    $utilizatori = "Administrator, Client";
    $entitati = "Uleiuri, Clienti";
    $descriere = "O aplicatie care va gestiona produsele ca uleiuri vegetale de toate tipurile";
    const VERSION = "1.0";    
    
//Sarcina 3
   $denumireUlei = "Ulei de floarea soarelui";
   $cantitateStoc = 20;
   $tipUlei = "rafinat";
   $pretPeLitru = 35.99;
   $esteBio = true;

// Sarcina 4
    const TVA = 8;
    const STOC_MINIM_ALERTA = 5;

//Sarcina 5
    $pretCuTVA = $pretPeLitru + ($pretPeLitru * TVA / 100);
    $valoareStoc = $cantitateStoc * $pretPeLitru;

    $cantitatePesteMinim = ($cantitateStoc > STOC_MINIM_ALERTA)
        ? $cantitateStoc - STOC_MINIM_ALERTA
        : 0;

    $necesitaReaprovizionare = ($cantitateStoc <= STOC_MINIM_ALERTA);
?>

<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= $denumireProiect ?></title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1><?= $denumireProiect ?></h1>
        <p><?= $descriere ?></p>
        <p><strong>Entități planificate:</strong> <?= $entitati ?></p>
        <p><strong>Utilizatori principali:</strong> <?= $utilizatori ?></p>
 
        <h2>Detalii produs</h2>
        <p>Denumire: <?= $denumireUlei ?></p>
        <p>Tip: <?= $tipUlei ?></p>
        <p>Certificat bio: <?= $esteBio ? "Da" : "Nu" ?></p>
        <p>Stoc disponibil: <?= $cantitateStoc ?> litri</p>
 
        <h2>Rezultatele calculelor</h2>
        <p>Preț fără TVA: <span class="rezultat"><?= number_format($pretPeLitru, 2) ?> lei/litru</span></p>
        <p>Preț cu TVA (<?= TVA ?>%): <span class="rezultat"><?= number_format($pretCuTVA, 2) ?> lei/litru</span></p>
        <p>Valoarea totală a stocului: <span class="rezultat"><?= number_format($valoareStoc, 2) ?> lei</span></p>
        <p>Cantitate peste pragul minim: <span class="rezultat"><?= $cantitatePesteMinim ?> litri</span></p>
        <p>Stare stoc:
            <?php if ($necesitaReaprovizionare): ?>
                <span class="alerta">Necesită reaprovizionare!</span>
            <?php else: ?>
                <span class="rezultat">Stoc suficient</span>
            <?php endif; ?>
        </p>
 
        <footer>
            Autor: <?= $autor ?> | Grupa: <?= $grupa ?> | Versiunea: <?= VERSION ?>
        </footer>
    </div>
</body>
</html>
 
<?php


