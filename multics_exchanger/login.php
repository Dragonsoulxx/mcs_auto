<?php
header('Content-Type: text/html; charset=UTF-8');
require_once 'config.php';
require_once 'languages.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php?lang=$lang");
            exit();
        } else {
            $error = __('error_login');
        }
    } else {
        $error = __('error_empty_fields');
    }
}
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Accedi a Multics Exchanger per gestire le tue linee in modo sicuro.">
    <meta name="keywords" content="Multics, Exchanger, login, accesso, sicurezza">
    <meta name="author" content="Tuo Nome/Team">
    <title><?php echo __('login_title'); ?> - <?php echo APP_NAME; ?></title>
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
            <a class="navbar-brand" href="index.php?lang=<?php echo $lang; ?>">
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
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="card-title text-center"><?php echo __('login_heading'); ?></h2>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST" class="mt-4">
                            <div class="mb-3">
                                <label for="username" class="form-label"><?php echo __('username'); ?></label>
                                <input type="text" class="form-control" id="username" name="username" required placeholder="<?php echo __('placeholder_username'); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label"><?php echo __('password'); ?></label>
                                <input type="password" class="form-control" id="password" name="password" required placeholder="<?php echo __('placeholder_password'); ?>">
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?php echo __('login'); ?></button>
                            <p class="text-center mt-3"><?php echo __('no_account'); ?> <a href="register.php?lang=<?php echo $lang; ?>" class="text-primary"><?php echo __('register'); ?></a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer sticky -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        <p><?php printf(__('footer_copyright'), date('Y'), APP_NAME); ?> | <a href="terms.php?lang=<?php echo $lang; ?>" class="text-white"><?php echo __('terms'); ?></a> | <a href="privacy.php?lang=<?php echo $lang; ?>" class="text-white"><?php echo __('privacy'); ?></a> | <a href="contact.php?lang=<?php echo $lang; ?>" class="text-white"><?php echo __('contact_us'); ?></a></p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>