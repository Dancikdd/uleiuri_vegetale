<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Antrenament</title>
</head>
<body>
    <h1>Exerciții de antrenament</h1>

<?php
//antrenament prima sarcina

const CATEGORIE_PRINCIPALA = "Uleiuri vegetale";

define('MONEDA', 'MDL');

const TIPURI_ULEI = ["Floarea soarelui", "Măsline", "Cocos", "Dovleac"];

    $propozitie = "Magazinul nostru ofera o gama variata de uleiuri vegetale \" " . 
    CATEGORIE_PRINCIPALA . " \", preturile find afisate in " . MONEDA . ", iar
    tipurile de ulei disponibile sunt: " . implode(", ", TIPURI_ULEI) . ".";
?>

<h2>1. Constante</h2>
    <p><?= $propozitie ?></p>

    <?php
    // sarcina 2
    $cantitateComandata = 20; 
    $capacitateSticla   = 7;   
 
    $suma = $cantitateComandata + $capacitateSticla;
    $diferenta = $cantitateComandata - $capacitateSticla;
    $produs = $cantitateComandata * $capacitateSticla;
    $impartire = $cantitateComandata / $capacitateSticla;
    $rest = $cantitateComandata % $capacitateSticla;
    $putere = $cantitateComandata ** 2;
 
    ?>

    <h2>2. Operatori aritmetici și modulo</h2>
    <p>Cantitate comandată: <?= $cantitateComandata ?> litri | Capacitate sticlă: <?= $capacitateSticla ?> litri</p>
    <p>Adunare (+): <?= $suma ?></p>
    <p>Scădere (-): <?= $diferenta ?></p>
    <p>Inmultire (*): <?= $produs ?></p>
    <p>Impartire (/): <?= number_format($impartire, 3) ?></p>
    <p>Modulo (%): <?= $rest ?> litri rămași (nu completează o sticlă întreagă)</p>
    <p>Ridicare la putere (**): <?= $putere ?></p>

    <?php
    //sarscina 3
    $cantitateStoc1 = "50";  
    $cantitateStoc2 = 50;   
 
    $comparatieLaxa   = ($cantitateStoc1 == $cantitateStoc2);   
    $comparatieStricta = ($cantitateStoc1 === $cantitateStoc2); 

    /*Diferenta este ca operatorul == compara doar valorile fara tipul
    iar operatorul === compara si tipul variabilelor
    */

    ?>

    <h2>3. Comparare </h2>
    <p>$cantitateStoc1 = "50" (string), $cantitateStoc2 = 50 (int)</p>
    <p>Comparație cu " == " : <?= $comparatieLaxa ? "true" : "false" ?></p>
    <p>Comparație cu " === " : <?= $comparatieStricta ? "true" : "false" ?></p>


    <?php
    // sarcina 4
    $cantitateComanda = 20; 
    $pragReducere      = 10; 
 
    $procentReducere = ($cantitateComanda >= $pragReducere) ? 10 : 0;
 
    $mesajReducere = ($procentReducere > 0)
        ? "Comanda beneficiază de o reducere de {$procentReducere}%."
        : "Comanda nu atinge pragul pentru reducere.";

        /* Operatorul ternar ? : e o 
        variantă scurtă a lui if/else, scrisă pe o singură linie.
        ? daca true, : daca false
        */

    ?>
    <h2>4. Expresie compusă cu operator ternar</h2>
    <p>Cantitate comandă: <?= $cantitateComanda ?> litri (prag reducere: <?= $pragReducere ?> litri)</p>
    <p><?= $mesajReducere ?></p>

    <?php
    // sarcina 5
    $numeProdus = "Ulei de măsline extravirgin";
    $pretProdus = 49.99;
 
    $propozitieConcatenare = "Produsul " . $numeProdus . " costă " . $pretProdus . " lei pe litru.";
 
    $propozitieInterpolare = "Produsul $numeProdus costă $pretProdus lei pe litru.";
 
    /* ambele sunt la fel dar au sintaxe diferite, concatenarea folosete 
    operatorul . pentru a uni stringuri iar interpolarea permite aceasta
    direct in ghilimele, as spune ca interpolarea este mai usor de citit si mai simplu.
    */
    ?>
    <h2>5. Concatenare vs interpolare</h2>
    <p>Cu concatenare: <?= $propozitieConcatenare ?></p>
    <p>Cu interpolare: <?= $propozitieInterpolare ?></p>


    <?php
    //sarcina 6
    $pretPeLitru       = 45.90;
    $cantitateVanduta  = 2750;
 
    $sumaTotala = $pretPeLitru * $cantitateVanduta;
 
    $sumaFormatata = number_format($sumaTotala, 3, '.' , '.');
    ?>
    <h2>6. Formatare numerică</h2>
    <p>Sumă totală vânzări lunare: <?= $sumaFormatata ?> lei</p>
 
</body>
</html>
