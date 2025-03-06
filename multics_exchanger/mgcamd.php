<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';
session_start(); // Solo per salvare, non obbligatorio

function generate_random_string($length = 8) {
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    return substr(str_shuffle($characters), 0, $length);
}

// Funzione per verificare la connessione a un IP/DNS e una porta
function check_line_connection($host, $port, $timeout = CONNECTION_TIMEOUT) {
    $connection = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($connection) {
        fclose($connection);
        return true; // Connessione riuscita
    }
    error_log("Errore connessione a $host:$port - Errore: $errstr ($errno)");
    return false; // Connessione fallita
}

$message = '';
$show_loading = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hostname = trim($_POST['hostname']);
    $port = trim($_POST['port']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $contact = trim($_POST['contact']);

    if (!empty($hostname) && filter_var($port, FILTER_VALIDATE_INT) && $port >= 1 && $port <= 65535 && !empty($username) && !empty($password) && !empty($contact)) {
        if (filter_var($hostname, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $hostname)) {
            $show_loading = true;
            $message = 'Inizio verifica...'; // Debug: messaggio iniziale

            // Verifica se la linea  online
            if (!check_line_connection($hostname, $port)) {
                $message = "Errore: La linea specificata non  raggiungibile o non  online.";
            } else {
                $mgcamd_line = "N: $hostname $port $username $password 01 02 03 04 05 06 07 08 09 10 11 12 13 14 #$contact";
                $new_username = generate_random_string(8);
                $new_password = generate_random_string(12);
                $mg_user_line = "MG: $new_username $new_password #$contact";
                $my_mgcamd_line = "N: " . SERVER_IP . " " . SERVER_PORT_MGCAMD . " $new_username $new_password 01 02 03 04 05 06 07 08 09 10 11 12 13 14";
                $file = MGCAMD_FILE;

                $content = "$mgcamd_line\n$mg_user_line\n";
                if (file_put_contents($file, $content, FILE_APPEND)) {
                    $message = "Linea mgcamd accettata! Ecco la mia linea: <strong>$my_mgcamd_line</strong>";
                    if (isset($_SESSION['user_id'])) {
                        $stmt = $pdo->prepare("INSERT INTO user_lines (user_id, type, line) VALUES (:user_id, 'mgcamd', :line)");
                        $stmt->execute(['user_id' => $_SESSION['user_id'], 'line' => $my_mgcamd_line]);
                    }
                } else {
                    $message = "Errore: non posso scrivere nel file. Verifica i permessi di " . MGCAMD_FILE;
                    error_log("Errore scrittura in " . MGCAMD_FILE . " - Permessi insufficienti o file mancante");
                }
            }
        } else {
            $message = "Errore: Hostname non valido.";
        }
    } else {
        $message = "Errore: inserisci tutti i campi richiesti.";
    }
}
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scambio Mgcamd - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-5">
            <img src="images/logo.jpg" alt="Logo" class="mb-3" style="max-width: 200px;">
            <h1 class="display-4">Scambio Mgcamd</h1>
            <p class="lead">Scambia la tua linea mgcamd con il nostro server!</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="hostname" class="form-label">Hostname o IP:</label>
                                <input type="text" class="form-control" id="hostname" name="hostname" required placeholder="es. testmg.example.com">
                            </div>
                            <div class="mb-3">
                                <label for="port" class="form-label">Porta:</label>
                                <input type="number" class="form-control" id="port" name="port" required min="1" max="65535" placeholder="es. 55555">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="es. testmg">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="text" class="form-control" id="password" name="password" required placeholder="es. mgpass">
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contatto (es. Telegram):</label>
                                <input type="text" class="form-control" id="contact" name="contact" required placeholder="es. @exampleuser">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Scambia Mgcamd</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messaggio di caricamento -->
    <div id="loading" class="loading-overlay">
        <span>Checking validation line</span>
        <span class="spinner"></span>
    </div>

    <!-- Popup per l'output -->
    <div id="outputPopup" class="popup-overlay">
        <div class="message"></div>
        <button onclick="copyToClipboard()">Copia</button>
    </div>

    <!-- Overlay per oscurare lo sfondo -->
    <div id="overlay" class="overlay"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funzione per mostrare il popup con caricamento
        function showLoading() {
            const loading = document.getElementById('loading');
            const overlay = document.getElementById('overlay');
            if (loading && overlay) {
                loading.style.display = 'flex';
                overlay.style.display = 'block';
                console.log('Caricamento mostrato');
            } else {
                console.error('Elementi loading o overlay non trovati.');
            }
        }

        // Funzione per mostrare il popup con il messaggio
        function showPopup(message) {
            const loading = document.getElementById('loading');
            const popup = document.getElementById('outputPopup');
            const overlay = document.getElementById('overlay');
            if (loading && popup && overlay) {
                loading.style.display = 'none';
                popup.querySelector('.message').innerHTML = message;
                popup.style.display = 'flex';
                overlay.style.display = 'block';
                console.log('Popup mostrato con messaggio:', message);
            } else {
                console.error('Elementi popup, loading o overlay non trovati.');
            }
        }

        function copyToClipboard() {
            const message = document.querySelector('#outputPopup .message').innerText;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(message).then(() => {
                    alert('Testo copiato negli appunti!');
                }).catch(err => {
                    console.error('Errore nella copia con Clipboard API: ', err);
                    // Fallback per copiare usando execCommand
                    fallbackCopyText(message);
                });
            } else {
                // Fallback per browser che non supportano navigator.clipboard
                fallbackCopyText(message);
            }
        }

        function fallbackCopyText(text) {
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();

            try {
                const successful = document.execCommand('copy');
                const msg = successful ? 'Testo copiato negli appunti!' : 'Impossibile copiare il testo.';
                alert(msg);
            } catch (err) {
                console.error('Errore nella copia con fallback: ', err);
                alert('Impossibile copiare il testo. Assicurati che il browser supporti questa funzionalit.');
            }

            document.body.removeChild(textArea);
        }

        // Chiudi il popup e l'overlay quando si clicca sull'overlay
        document.getElementById('overlay')?.addEventListener('click', function() {
            const popup = document.getElementById('outputPopup');
            const overlay = document.getElementById('overlay');
            const loading = document.getElementById('loading');
            if (popup && overlay) {
                popup.style.display = 'none';
                overlay.style.display = 'none';
                loading.style.display = 'none'; // Assicurati di nascondere anche il caricamento
                console.log('Popup, overlay e caricamento chiusi');
            }
        });

        // Chiudi il popup con il tasto Esc
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const popup = document.getElementById('outputPopup');
                const overlay = document.getElementById('overlay');
                const loading = document.getElementById('loading');
                if (popup && overlay) {
                    popup.style.display = 'none';
                    overlay.style.display = 'none';
                    loading.style.display = 'none'; // Assicurati di nascondere anche il caricamento
                    console.log('Popup, overlay e caricamento chiusi con Esc');
                }
            }
        });

        // Verifica se gli elementi esistono prima di manipolarli
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.getElementById('loading') || !document.getElementById('outputPopup') || !document.getElementById('overlay')) {
                console.error('Elementi HTML mancanti per caricamento o popup.');
            }

            // Esegui i popup salvati dal PHP dopo il caricamento
            <?php if (!empty($message)): ?>
                showPopup("<?php echo addslashes($message); ?>");
            <?php endif; ?>
            <?php if ($show_loading): ?>
                showLoading();
            <?php endif; ?>

            // Timeout di sicurezza per nascondere il caricamento se rimane visibile troppo a lungo
            setTimeout(() => {
                const loading = document.getElementById('loading');
                if (loading && loading.style.display === 'flex') {
                    loading.style.display = 'none';
                    console.log('Caricamento nascosto dopo timeout di sicurezza (5 secondi)');
                }
            }, 5000); // 5 secondi di timeout (valore originale)
        });
    </script>
</body>
</html>