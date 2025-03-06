<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';
require_once 'languages.php';
session_start(); // Solo per salvare, non obbligatorio per lo scambio

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

// Messaggi per ogni sezione
$cache_message = '';
$mgcamd_message = '';
$newcamd_message = '';
$show_loading = false; // Flag per il caricamento

// Funzione per caricare i profili Newcamd
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

// Gestione scambio Cache
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['cache_submit'])) {
    $ip_dns = trim($_POST['ip_dns']);
    $port = trim($_POST['port']);

    if (!empty($ip_dns) && filter_var($port, FILTER_VALIDATE_INT) && $port >= 1 && $port <= 65535) {
        if (filter_var($ip_dns, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $ip_dns)) {
            $show_loading = true;
            $cache_message = 'Inizio verifica...'; // Debug: messaggio iniziale

            // Verifica se la linea  online
            if (!check_line_connection($ip_dns, $port)) {
                $cache_message = "Errore: La linea specificata non  raggiungibile o non  online.";
            } else {
                $cache_line = "CACHE PEER: $ip_dns $port";
                $my_cache_line = "CACHE PEER: " . SERVER_IP . " " . SERVER_PORT_CACHE;
                $file = CACHE_FILE;

                if (file_put_contents($file, "$cache_line\n", FILE_APPEND)) {
                    $cache_message = "Linea accettata! Ecco la mia cache: <strong>$my_cache_line</strong>";
                    if (isset($_SESSION['user_id'])) {
                        $stmt = $pdo->prepare("INSERT INTO user_lines (user_id, type, line) VALUES (:user_id, 'cache', :line)");
                        $stmt->execute(['user_id' => $_SESSION['user_id'], 'line' => $my_cache_line]);
                    }
                } else {
                    $cache_message = "Errore: non posso scrivere nel file. Verifica i permessi di " . CACHE_FILE;
                    error_log("Errore scrittura in " . CACHE_FILE . " - Permessi insufficienti o file mancante");
                }
            }
        } else {
            $cache_message = "Errore: IP o DNS non valido.";
        }
    } else {
        $cache_message = "Errore: inserisci un IP/DNS valido e una porta tra 1 e 65535.";
    }
}

// Gestione scambio Mgcamd
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mgcamd_submit'])) {
    $hostname = trim($_POST['hostname']);
    $port = trim($_POST['port']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $contact = trim($_POST['contact']);

    if (!empty($hostname) && filter_var($port, FILTER_VALIDATE_INT) && $port >= 1 && $port <= 65535 && !empty($username) && !empty($password) && !empty($contact)) {
        if (filter_var($hostname, FILTER_VALIDATE_IP) || preg_match('/^[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $hostname)) {
            $show_loading = true;
            $mgcamd_message = 'Inizio verifica...'; // Debug: messaggio iniziale

            // Verifica se la linea  online
            if (!check_line_connection($hostname, $port)) {
                $mgcamd_message = "Errore: La linea specificata non  raggiungibile o non  online.";
            } else {
                $mgcamd_line = "N: $hostname $port $username $password 01 02 03 04 05 06 07 08 09 10 11 12 13 14 #$contact";
                $new_username = generate_random_string(8);
                $new_password = generate_random_string(12);
                $mg_user_line = "MG: $new_username $new_password #$contact";
                $my_mgcamd_line = "N: " . SERVER_IP . " " . SERVER_PORT_MGCAMD . " $new_username $new_password 01 02 03 04 05 06 07 08 09 10 11 12 13 14";
                $file = MGCAMD_FILE;

                $content = "$mgcamd_line\n$mg_user_line\n";
                if (file_put_contents($file, $content, FILE_APPEND)) {
                    $mgcamd_message = "Linea mgcamd accettata! Ecco la mia linea: <strong>$my_mgcamd_line</strong>";
                    if (isset($_SESSION['user_id'])) {
                        $stmt = $pdo->prepare("INSERT INTO user_lines (user_id, type, line) VALUES (:user_id, 'mgcamd', :line)");
                        $stmt->execute(['user_id' => $_SESSION['user_id'], 'line' => $my_mgcamd_line]);
                    }
                } else {
                    $mgcamd_message = "Errore: non posso scrivere nel file. Verifica i permessi di " . MGCAMD_FILE;
                    error_log("Errore scrittura in " . MGCAMD_FILE . " - Permessi insufficienti o file mancante");
                }
            }
        } else {
            $mgcamd_message = "Errore: Hostname non valido.";
        }
    } else {
        $mgcamd_message = "Errore: inserisci tutti i campi richiesti.";
    }
}

// Gestione scambio Newcamd
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['newcamd_submit'])) {
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
            $newcamd_message = 'Inizio verifica...'; // Debug: messaggio iniziale

            // Verifica se le porte sono raggiungibili
            foreach ($ports as $port) {
                if (!check_line_connection($hostname, $port)) {
                    $valid_ports = false;
                    $newcamd_message = "Errore: Una o pi porte specificate non sono raggiungibili o non sono online.";
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
                    $newcamd_message = "Linea newcamd accettata! Ecco le mie linee:<br><strong>" . implode('<br>', $my_newcamd_lines) . "</strong>";
                } else {
                    $newcamd_message = "Errore: non posso scrivere nel file. Verifica i permessi di " . NEWCAMD_FILE;
                    error_log("Errore scrittura in " . NEWCAMD_FILE . " - Permessi insufficienti o file mancante");
                }
            }
        } else {
            $newcamd_message = "Errore: Hostname non valido.";
        }
    } else {
        $newcamd_message = "Errore: inserisci tutti i campi richiesti.";
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Scambia linee Cache, Mgcamd e Newcamd con Multics Exchanger in modo semplice e sicuro.">
    <meta name="keywords" content="Multics, Exchanger, Cache, Mgcamd, Newcamd, scambio linee">
    <meta name="author" content="Tuo Nome/Team">
    <title><?php echo __('home_title'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" type="image/jpeg" href="images/favicon.jpg">
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=YOUR_TRACKING_ID"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'YOUR_TRACKING_ID');
    </script>
</head>
<body>
    <!-- Navbar in alto -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="images/logo.jpg" alt="Logo di <?php echo APP_NAME; ?>" style="max-width: 50px; height: auto;">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a href="?lang=it" class="nav-link text-white <?php echo $lang === 'it' ? 'active' : ''; ?>">Italiano</a>
                    </li>
                    <li class="nav-item">
                        <a href="?lang=en" class="nav-link text-white <?php echo $lang === 'en' ? 'active' : ''; ?>">English</a>
                    </li>
                    <li class="nav-item">
                        <a href="about.php?lang=<?php echo $lang; ?>" class="nav-link text-white"><?php echo __('about'); ?></a>
                    </li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a href="login.php?lang=<?php echo $lang; ?>" class="nav-link btn btn-primary me-2"><?php echo __('login'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a href="register.php?lang=<?php echo $lang; ?>" class="nav-link btn btn-outline-primary"><?php echo __('register'); ?></a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <span class="nav-link text-white me-2"><?php echo __('welcome'); ?><?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                        </li>
                        <li class="nav-item">
                            <a href="dashboard.php?lang=<?php echo $lang; ?>" class="nav-link btn btn-success me-2"><?php echo __('dashboard'); ?></a>
                        </li>
                        <li class="nav-item">
                            <a href="logout.php?lang=<?php echo $lang; ?>" class="nav-link btn btn-danger"><?php echo __('logout'); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenuto principale con margine per la navbar fissa -->
    <div class="container mt-5 pt-5">
        <?php if (!empty($cache_message) || !empty($mgcamd_message) || !empty($newcamd_message)): ?>
            <div class="alert alert-<?php echo (strpos($cache_message . $mgcamd_message . $newcamd_message, 'Errore') === 0 ? 'danger' : 'success'); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($cache_message . ' ' . $mgcamd_message . ' ' . $newcamd_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        <?php endif; ?>

        <div class="text-center mb-5">
            <h1 class="display-4"><?php echo __('home_heading'); ?></h1>
            <p class="lead"><?php echo __('home_lead'); ?></p>
        </div>

        <!-- Tabs per gli scambi -->
        <ul class="nav nav-tabs" id="exchangeTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="cache-tab" data-bs-toggle="tab" data-bs-target="#cache" type="button" role="tab" aria-controls="cache" aria-selected="true"><?php echo __('cache_tab'); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mgcamd-tab" data-bs-toggle="tab" data-bs-target="#mgcamd" type="button" role="tab" aria-controls="mgcamd" aria-selected="false"><?php echo __('mgcamd_tab'); ?></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="newcamd-tab" data-bs-toggle="tab" data-bs-target="#newcamd" type="button" role="tab" aria-controls="newcamd" aria-selected="false"><?php echo __('newcamd_tab'); ?></button>
            </li>
        </ul>

        <!-- Contenuto delle tabs -->
        <div class="tab-content" id="exchangeTabsContent">
            <!-- Tab Cache -->
            <div class="tab-pane fade show active" id="cache" role="tabpanel" aria-labelledby="cache-tab">
                <div class="card shadow mt-3">
                    <div class="card-body">
                        <h2 class="card-title text-center"><?php echo __('cache_title'); ?></h2>
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="cache_ip_dns" class="form-label"><?php echo __('ip_dns'); ?></label>
                                <input type="text" class="form-control" id="cache_ip_dns" name="ip_dns" required placeholder="es. casa-esempio.eu">
                            </div>
                            <div class="mb-3">
                                <label for="cache_port" class="form-label"><?php echo __('port'); ?></label>
                                <input type="number" class="form-control" id="cache_port" name="port" required min="1" max="65535" placeholder="es. 55555">
                            </div>
                            <button type="submit" name="cache_submit" class="btn btn-primary w-100"><?php echo __('submit_cache'); ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab Mgcamd -->
            <div class="tab-pane fade" id="mgcamd" role="tabpanel" aria-labelledby="mgcamd-tab">
                <div class="card shadow mt-3">
                    <div class="card-body">
                        <h2 class="card-title text-center"><?php echo __('mgcamd_title'); ?></h2>
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="mgcamd_hostname" class="form-label"><?php echo __('hostname'); ?></label>
                                <input type="text" class="form-control" id="mgcamd_hostname" name="hostname" required placeholder="es. testmg.example.com">
                            </div>
                            <div class="mb-3">
                                <label for="mgcamd_port" class="form-label"><?php echo __('port'); ?></label>
                                <input type="number" class="form-control" id="mgcamd_port" name="port" required min="1" max="65535" placeholder="es. 55555">
                            </div>
                            <div class="mb-3">
                                <label for="mgcamd_username" class="form-label"><?php echo __('username'); ?></label>
                                <input type="text" class="form-control" id="mgcamd_username" name="username" required placeholder="es. testmg">
                            </div>
                            <div class="mb-3">
                                <label for="mgcamd_password" class="form-label"><?php echo __('password'); ?></label>
                                <input type="text" class="form-control" id="mgcamd_password" name="password" required placeholder="es. mgpass">
                            </div>
                            <div class="mb-3">
                                <label for="mgcamd_contact" class="form-label"><?php echo __('contact'); ?></label>
                                <input type="text" class="form-control" id="mgcamd_contact" name="contact" required placeholder="es. @exampleuser">
                            </div>
                            <button type="submit" name="mgcamd_submit" class="btn btn-primary w-100"><?php echo __('submit_mgcamd'); ?></button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Tab Newcamd -->
            <div class="tab-pane fade" id="newcamd" role="tabpanel" aria-labelledby="newcamd-tab">
                <div class="card shadow mt-3">
                    <div class="card-body">
                        <h2 class="card-title text-center"><?php echo __('newcamd_title'); ?></h2>
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="newcamd_hostname" class="form-label"><?php echo __('hostname'); ?></label>
                                <input type="text" class="form-control" id="newcamd_hostname" name="hostname" required placeholder="es. testnc.example.com">
                            </div>
                            <div class="mb-3">
                                <label for="newcamd_ports" class="form-label"><?php echo __('ports'); ?></label>
                                <input type="text" class="form-control" id="newcamd_ports" name="ports" required placeholder="es. 6666,7777,8888">
                            </div>
                            <div class="mb-3">
                                <label for="newcamd_username" class="form-label"><?php echo __('username'); ?></label>
                                <input type="text" class="form-control" id="newcamd_username" name="username" required placeholder="es. testnc">
                            </div>
                            <div class="mb-3">
                                <label for="newcamd_password" class="form-label"><?php echo __('password'); ?></label>
                                <input type="text" class="form-control" id="newcamd_password" name="password" required placeholder="es. ncpass">
                            </div>
                            <div class="mb-3">
                                <label for="newcamd_contact" class="form-label"><?php echo __('contact'); ?></label>
                                <input type="text" class="form-control" id="newcamd_contact" name="contact" required placeholder="es. @exampleuser">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?php echo __('profiles'); ?></label>
                                <?php foreach ($profiles as $name => $port): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="profiles[]" value="<?php echo htmlspecialchars($name); ?>" id="profile_<?php echo htmlspecialchars($name); ?>">
                                        <label class="form-check-label" for="profile_<?php echo htmlspecialchars($name); ?>">
                                            <?php echo "$name (Port: $port)"; ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" name="newcamd_submit" class="btn btn-primary w-100"><?php echo __('submit_newcamd'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sezione Social Sharing -->
        <div class="text-center my-5">
            <h3>Condividi con gli amici</h3>
            <div class="d-flex justify-content-center gap-3">
                <a href="https://twitter.com/intent/tweet?url=http%3A%2F%2F80.211.129.193%2Fmultics_exchanger%2F&text=<?php echo urlencode(__('share_text')); ?>" target="_blank" class="btn btn-outline-light"><i class="bi bi-twitter"></i> Twitter</a>
                <a href="https://www.facebook.com/sharer/sharer.php?u=http%3A%2F%2F80.211.129.193%2Fmultics_exchanger%2F" target="_blank" class="btn btn-outline-light"><i class="bi bi-facebook"></i> Facebook</a>
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

    <!-- Footer sticky -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p><?php printf(__('footer_copyright'), date('Y'), APP_NAME); ?> | <a href="terms.php?lang=<?php echo $lang; ?>" class="text-white"><?php echo __('terms'); ?></a> | <a href="privacy.php?lang=<?php echo $lang; ?>" class="text-white"><?php echo __('privacy'); ?></a> | <a href="contact.php?lang=<?php echo $lang; ?>" class="text-white"><?php echo __('contact_us'); ?></a></p>
    </footer>

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
            <?php if (!empty($cache_message)): ?>
                showPopup("<?php echo addslashes($cache_message); ?>");
            <?php endif; ?>
            <?php if (!empty($mgcamd_message)): ?>
                showPopup("<?php echo addslashes($mgcamd_message); ?>");
            <?php endif; ?>
            <?php if (!empty($newcamd_message)): ?>
                showPopup("<?php echo addslashes($newcamd_message); ?>");
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