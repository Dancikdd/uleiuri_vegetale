<?php

$root = '/site/';

const NUME_PROIECT = 'Uleiuri Vegetale';
const VERSIUNE     = '2.0';
const AUTOR        = 'Ursan Daniel, PAPP-231';

const ROLURI = [
    'vizitator' => 'Vizitator',
    'client' => 'Client',
    'administrator' => 'Administrator',
];

$rolCurent = $_GET['rol'] ?? 'vizitator';
if (!is_string($rolCurent) || !isset(ROLURI[$rolCurent])) {
    $rolCurent = 'vizitator';
}

function url_cu_rol($cale)
{
    global $rolCurent;
    return $cale . (strpos($cale, '?') === false ? '?' : '&') . 'rol=' . rawurlencode($rolCurent);
}

const PAGINI = [
    'acasa'    => ['titlu' => 'Acasă',      'cale' => 'index.php'],
    'produse'  => ['titlu' => 'Produse',    'cale' => 'pages/produse.php'],
    'comanda'  => ['titlu' => 'Comandă',    'cale' => 'pages/comanda.php'],
    'despre'   => ['titlu' => 'Despre noi', 'cale' => 'pages/despre.php'],
    'contact'  => ['titlu' => 'Contact',    'cale' => 'pages/contact.php'],
    'roluri'   => ['titlu' => 'Roluri',     'cale' => 'pages/roluri.php'],
];