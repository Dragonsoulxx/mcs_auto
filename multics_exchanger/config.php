<?php
header('Content-Type: text/html; charset=UTF-8');
// Configurazione del database
define('DB_HOST', 'localhost');
define('DB_NAME', 'multics_exchanger');
define('DB_USER', 'multics_user');
define('DB_PASS', 'insert db password');

// Configurazione server
define('SERVER_IP', '80.211.129.193');
define('SERVER_PORT_CACHE', 55555); /------ change port
define('SERVER_PORT_MGCAMD', 48710); /------ change port
define('BASE_PORT_NEWCAMD', 9600); /------ change port

// Timeout per la connessione
if (!defined('CONNECTION_TIMEOUT')) {
    define('CONNECTION_TIMEOUT', 10);  // Valore originale, non modificato
}

// Percorsi dei file
define('CACHE_FILE', '/var/www/multics_exchanger/cache.cfg'); /---------change directory
define('MGCAMD_FILE', '/var/www/multics_exchanger/mgcamd.cfg');  /---------change directory
define('NEWCAMD_FILE', '/var/www/multics_exchanger/newcamd.cfg');  /---------change directory
define('PROFILES_FILE', '/var/www/multics_exchanger/profili.cfg');  /---------change directory

// Nome dell'applicazione
define('APP_NAME', 'Multics Exchanger');

// Connessione al database
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("Errore connessione al database: " . $e->getMessage());
    die("Errore: Impossibile connettersi al database. Controlla i log.");
}
?>
