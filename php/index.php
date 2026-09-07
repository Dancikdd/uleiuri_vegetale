<?php
 // Sarcina 2
    $denumireProiect = "Sistem de gestionare a unui magazin de uleiuri vegetale";
    $autor = "Ursan Daniel";
    $grupa = "PAPP-231";
    $entitate = "Student";
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



