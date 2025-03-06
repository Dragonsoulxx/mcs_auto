<?php require_once 'config.php'; require_once 'languages.php'; ?>
header('Content-Type: text/html; charset=UTF-8');
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Termini di Servizio di Multics Exchanger.">
    <meta name="keywords" content="Multics, Exchanger, termini, servizio, condizioni">
    <meta name="author" content="Tuo Nome/Team">
    <title><?php echo __('terms'); ?> - <?php echo APP_NAME; ?></title>
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
        <div class="text-center mb-5">
            <h1 class="display-4"><?php echo __('terms'); ?></h1>
            <p class="lead"><?php echo __('terms_lead'); ?></p>
        </div>
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow">
                    <div class="card-body">
                        <h2><?php echo __('terms_heading'); ?></h2>
                        <p><?php echo __('terms_content'); ?></p>
                        <!-- Aggiungi qui i termini specifici del servizio -->
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