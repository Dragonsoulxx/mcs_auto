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

function load_profiles($file_path = PROFILES_FILE) {
    $profiles = [];
    if (!file_exists($file_path)) {
        error_log("File profili non trovato: $file_path");
        return $profiles;
    }
    $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $base_port = null;
    $port_counter = null;
    $current_profile = null; // Inizializzazione della variabile

    foreach ($lines as $line) {
        $line = trim($line);
        if (preg_match('/^\[(.*?)\]$/', $line, $matches)) {
            $profile_name = trim($matches[1]);
            if ($base_port === null && $profile_name === 'Focus') {
                $current_profile = $profile_name;
            } elseif ($base_port !== null) {
                $port_counter++;
                $profiles[$profile_name] = $port_counter;
            } else {
                $profiles[$profile_name] = null;
            }
        } elseif ($current_profile === 'Focus' && preg_match('/^PORT\s*:\s*(\d+)$/', $line, $matches)) {
            $base_port = (int)$matches[1];
            $port_counter = $base_port;
            $profiles['Focus'] = $base_port;
            $current_profile = null; // Reset dopo aver trovato la porta
        }
    }

    if ($base_port === null) {
        $base_port = BASE_PORT_NEWCAMD;
        $port_counter = $base_port;
        $profiles['Focus'] = $base_port;
    }

    foreach ($profiles as $name => $port) {
        if ($port === null) {
            $port_counter++;
            $profiles[$name] = $port_counter;
        }
    }

    return $profiles;
}

$profiles = load_profiles();
$message = '';
$show_loading = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $hostname = trim($_POST['hostname']);
    $ports_input = trim($_POST['ports']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $contact = trim($_POST['contact']);
    $selected_profiles = isset($_POST['profiles']) ? $_POST['profiles'] : [];

    $ports = array_filter(array_map('trim', explode(',', $ports_input)));

    if (!empty($hostname) && !empty($ports) && !empty($username) && !empty($password) && !empty($contact) && !empty($selected_profiles)) {
        if (filter_var($hostname, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $hostname)) {
            $valid_ports = true;
            foreach ($ports as $port) {
                if (!filter_var($port, FILTER_VALIDATE_INT) || $port < 1 || $port > 65535) {
                    $valid_ports = false;
                    break;
                }
            }

            $show_loading = true;
            $message = 'Inizio verifica...'; // Debug: messaggio iniziale

            // Verifica se le porte sono raggiungibili
            foreach ($ports as $port) {
                if (!check_line_connection($hostname, $port)) {
                    $valid_ports = false;
                    $message = "Errore: Una o pi porte specificate non sono raggiungibili o non sono online.";
                    break;
                }
            }

            if ($valid_ports) {
                $new_username = generate_random_string(8);
                $new_password = generate_random_string(12);
                $file = NEWCAMD_FILE;

                $newcamd_lines = [];
                foreach ($ports as $port) {
                    $newcamd_lines[] = "N: $hostname $port $username $password 01 02 03 04 05 06 07 08 09 10 11 12 13 14 #$contact";
                }

                $user_ports = '{' . implode(',', array_map(function($profile) use ($profiles) { return $profiles[$profile]; }, $selected_profiles)) . '}';
                $newcamd_user_line = "USER: $new_username $new_password $user_ports #$contact";

                $content = implode("\n", $newcamd_lines) . "\n$newcamd_user_line\n";
                if (file_put_contents($file, $content, FILE_APPEND)) {
                    $my_newcamd_lines = [];
                    foreach ($selected_profiles as $profile) {
                        $port = $profiles[$profile];
                        $my_newcamd_lines[] = "N: " . SERVER_IP . " $port $new_username $new_password 01 02 03 04 05 06 07 08 09 10 11 12 13 14";
                        if (isset($_SESSION['user_id'])) {
                            $stmt = $pdo->prepare("INSERT INTO user_lines (user_id, type, line) VALUES (:user_id, 'newcamd', :line)");
                            $stmt->execute(['user_id' => $_SESSION['user_id'], 'line' => $my_newcamd_lines[count($my_newcamd_lines) - 1]]);
                        }
                    }
                    $message = "Linea newcamd accettata! Ecco le mie linee:<br><strong>" . implode('<br>', $my_newcamd_lines) . "</strong>";
                } else {
                    $message = "Errore: non posso scrivere nel file. Verifica i permessi di " . NEWCAMD_FILE;
                    error_log("Errore scrittura in " . NEWCAMD_FILE . " - Permessi insufficienti o file mancante");
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
    <title>Scambio Newcamd - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-5">
            <img src="images/logo.jpg" alt="Logo" class="mb-3" style="max-width: 200px;">
            <h1 class="display-4">Scambio Newcamd</h1>
            <p class="lead">Scambia la tua linea newcamd con il nostro server!</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="hostname" class="form-label">Hostname o IP:</label>
                                <input type="text" class="form-control" id="hostname" name="hostname" required placeholder="es. testnc.example.com">
                            </div>
                            <div class="mb-3">
                                <label for="ports" class="form-label">Porte (separate da virgole):</label>
                                <input type="text" class="form-control" id="ports" name="ports" required placeholder="es. 6666,7777,8888">
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username:</label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="es. testnc">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password:</label>
                                <input type="text" class="form-control" id="password" name="password" required placeholder="es. ncpass">
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contatto (es. Telegram):</label>
                                <input type="text" class="form-control" id="contact" name="contact" required placeholder="es. @exampleuser">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Seleziona i profili:</label>
                                <?php foreach ($profiles as $name => $port): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="profiles[]" value="<?php echo htmlspecialchars($name); ?>" id="profile_<?php echo htmlspecialchars($name); ?>">
                                        <label class="form-check-label" for="profile_<?php echo htmlspecialchars($name); ?>">
                                            <?php echo "$name (Porta: $port)"; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Scambia Newcamd</button>
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