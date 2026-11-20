<?php
function incarca_configurare_locala(): void
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;

    $envPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
    if (!is_file($envPath)) {
        return;
    }

    $values = @parse_ini_file($envPath, false, INI_SCANNER_RAW);
    if ($values === false) {
        throw new RuntimeException('Fișierul .env nu poate fi citit.');
    }

    foreach (['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASSWORD'] as $key) {
        if (getenv($key) !== false || !array_key_exists($key, $values)) {
            continue;
        }
        $value = trim((string) $values[$key]);
        if (strlen($value) >= 2
            && (($value[0] === '"' && substr($value, -1) === '"')
                || ($value[0] === "'" && substr($value, -1) === "'"))) {
            $value = substr($value, 1, -1);
        }
        putenv($key . '=' . $value);
    }
}

/** Return a reusable PDO connection to MySQL. */
function database(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    incarca_configurare_locala();
    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    if (!$host || !$name || !$user) {
        throw new RuntimeException('Configurarea MySQL lipsește. Verifică fișierul .env.');
    }
    if ($password === false) {
        $password = '';
    }

    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $host, $port, $name);
    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log('MySQL connection error: ' . $e->getMessage());
        throw new RuntimeException('Nu s-a putut conecta aplicația la baza MySQL. Verifică setările conexiunii.');
    }

    return $pdo;
}
