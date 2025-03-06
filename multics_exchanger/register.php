<?php
// Includi il file di configurazione per la connessione al database
require_once 'config.php';

// Avvia la sessione (se necessaria per la registrazione)
session_start();

// Verifica il metodo della richiesta
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Mostra il form se è una richiesta GET
    ?>
    <!DOCTYPE html>
    <html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Registrazione - Multics Exchanger</title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar">
            <a href="index.php" class="navbar-brand">Multics Exchanger</a>
            <ul class="nav-menu">
                <li><a href="index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="login.php">Login</a></li>
            </ul>
        </nav>

        <!-- Contenuto principale -->
        <main class="container">
            <div class="registration-form">
                <h1>Registrazione</h1>
                <form method="POST" action="register.php">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username" required placeholder="Inserisci il tuo username">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" required placeholder="Inserisci la tua password">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" required placeholder="Inserisci la tua email">
                    </div>
                    
                    <button type="submit" class="btn-submit">Registrati</button>
                </form>
                <p>Already have an account? <a href="login.php" class="link">Login here</a></p>
            </div>
        </main>

        <!-- Footer sticky -->
        <footer class="footer">
            <p>© 2025 Multics Exchanger - Tutti i diritti riservati</p>
        </footer>
    </body>
    </html>
    <?php
    exit;
}

// Elaborazione della richiesta POST per la registrazione
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';

// Validazione dei dati
if (empty($username) || empty($password) || empty($email)) {
    die("Tutti i campi (username, password, email) sono obbligatori.");
}

// Connessione al database usando PDO (definita in config.php)
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES utf8mb4"); // Forza UTF-8 per le query
} catch (PDOException $e) {
    error_log("Errore di connessione al database: " . $e->getMessage());
    die("Si è verificato un errore di connessione al database. Controlla i log.");
}

// Verifica se username o email sono già registrati
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if ($stmt->fetchColumn() > 0) {
        die("Username o email già registrati.");
    }
} catch (PDOException $e) {
    error_log("Errore nella verifica dei duplicati: " . $e->getMessage());
    die("Si è verificato un errore durante la verifica. Controlla i log.");
}

// Inserisci il nuovo utente nella tabella users
try {
    $stmt = $pdo->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $email]);
    echo "Registrazione completata con successo!";
} catch (PDOException $e) {
    error_log("Errore nella registrazione: " . $e->getMessage());
    die("Si è verificato un errore durante la registrazione. Controlla i log.");
}

// Opzionale: Aggiungi una linea predefinita nella tabella user_lines per il nuovo utente
try {
    $user_id = $pdo->lastInsertId(); // Ottiene l'ID dell'utente appena inserito
    $stmt = $pdo->prepare("INSERT INTO user_lines (user_id, line_data) VALUES (?, ?)");
    $stmt->execute([$user_id, "Linea predefinita per $username"]); // Esempio di linea predefinita
    echo " Linea predefinita aggiunta con successo!";
} catch (PDOException $e) {
    error_log("Errore nell'inserimento della linea predefinita: " . $e->getMessage());
    // Non interrompe l'esecuzione, ma logga l'errore
}

// Opzionale: Reindirizza l'utente a una pagina di successo o login
header("Location: login.php?success=1");
exit();
?>