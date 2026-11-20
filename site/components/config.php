<?php

$projectRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..');
$documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
$root = '';

if ($projectRoot !== false && $documentRoot !== false) {
    $projectRootNormalized = rtrim(str_replace('\\', '/', $projectRoot), '/');
    $documentRootNormalized = rtrim(str_replace('\\', '/', $documentRoot), '/');

    if (stripos($projectRootNormalized, $documentRootNormalized . '/') === 0) {
        $root = '/' . trim(substr($projectRootNormalized, strlen($documentRootNormalized)), '/') . '/';
    }
}

const NUME_PROIECT = 'Uleiuri Vegetale';
const VERSIUNE     = '2.0';
const AUTOR        = 'Ursan Daniel, PAPP-231';

const ROLURI = [
    'vizitator' => 'Vizitator',
    'client' => 'Client',
    'administrator' => 'Administrator',
];

$utilizatorCurent = null;
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('uleiuri_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
    $utilizatorCurent = $_SESSION['user'];
}
$rolCurent = $utilizatorCurent['role'] ?? 'vizitator';
if (!is_string($rolCurent) || !isset(ROLURI[$rolCurent])) {
    $utilizatorCurent = null;
    $rolCurent = 'vizitator';
}

function token_csrf(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function token_csrf_valid(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && is_string($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function pagina_indisponibila(): void
{
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><html lang="ro"><meta charset="utf-8"><title>Pagina nu a fost găsită</title>'
        . '<body><h1>Pagina nu a fost găsită</h1><p>Pagina solicitată nu este disponibilă.</p></body></html>';
    exit;
}

function cere_roluri($roluriPermise): void
{
    global $utilizatorCurent;
    $roluriPermise = (array) $roluriPermise;
    if (!$utilizatorCurent || !in_array($utilizatorCurent['role'] ?? null, $roluriPermise, true)) {
        pagina_indisponibila();
    }
}

function url_cu_rol($cale)
{
    return $cale;
}

const PAGINI = [
    'acasa'    => ['titlu' => 'Acasă',      'cale' => 'index.php'],
    'produse'  => ['titlu' => 'Produse',    'cale' => 'pages/produse.php'],
    'comanda'  => ['titlu' => 'Comandă',    'cale' => 'pages/comanda.php'],
    'despre'   => ['titlu' => 'Despre noi', 'cale' => 'pages/despre.php'],
    'contact'  => ['titlu' => 'Contact',    'cale' => 'pages/contact.php'],
    'roluri'   => ['titlu' => 'Roluri',     'cale' => 'pages/roluri.php'],
];
