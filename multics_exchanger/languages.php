<?php
header('Content-Type: text/html; charset=UTF-8');
// File per la gestione delle traduzioni
$languages = [
    'it' => [
        'home_title' => 'Multics Exchanger - Homepage',
        'home_heading' => 'Multics Exchanger',
        'home_lead' => 'Scambia le tue linee con il nostro server in pochi clic!',
        'login' => 'Accedi',
        'register' => 'Registrati',
        'dashboard' => 'Vai alla Dashboard',
        'logout' => 'Esci',
        'welcome' => 'Benvenuto, ',
        'cache_tab' => 'Scambio Cache',
        'mgcamd_tab' => 'Scambio Mgcamd',
        'newcamd_tab' => 'Scambio Newcamd',
        'cache_title' => 'Scambio Cache',
        'mgcamd_title' => 'Scambio Mgcamd',
        'newcamd_title' => 'Scambio Newcamd',
        'ip_dns' => 'IP o DNS:',
        'port' => 'Porta:',
        'hostname' => 'Hostname o IP:',
        'ports' => 'Porte (separate da virgole):',
        'username' => 'Username:',
        'password' => 'Password:',
        'contact' => 'Contatto (es. Telegram):',
        'profiles' => 'Seleziona i profili:',
        'submit_cache' => 'Scambia Cache',
        'submit_mgcamd' => 'Scambia Mgcamd',
        'submit_newcamd' => 'Scambia Newcamd',
        'about' => 'Chi Siamo',
        'about_lead' => 'Scopri di pi su Multics Exchanger',
        'terms' => 'Termini di Servizio',
        'privacy' => 'Privacy Policy',
        'contact_us' => 'Contatti',
        'footer_copyright' => ' %s %s. Tutti i diritti riservati.',
        'login_title' => 'Accedi',
        'login_heading' => 'Accedi',
        'error_login' => 'Username o password errati.',
        'error_empty_fields' => 'Inserisci username e password.',
        'no_account' => 'Non hai un account?',
        'placeholder_username' => 'Inserisci il tuo username',
        'placeholder_password' => 'Inserisci la tua password',
        'register_title' => 'Registrati',
        'register_heading' => 'Registrati',
        'error_password_mismatch' => 'Le password non coincidono.',
        'error_username_taken' => 'Questo username  gi in uso.',
        'error_registration' => 'Errore durante la registrazione. Riprova.',
        'have_account' => 'Hai gi un account?',
        'confirm_password' => 'Conferma Password:',
        'placeholder_confirm_password' => 'Conferma la password',
        'dashboard_title' => 'Dashboard',
        'dashboard_heading' => 'Dashboard',
        'dashboard_lead' => 'Le tue linee salvate',
        'back_home' => 'Torna alla Homepage',
        'line_title' => 'Linea %s',
        'line' => 'Linea:',
        'date' => 'Data:',
        'no_lines' => 'Non hai ancora salvato linee. Torna alla homepage per scambiarne di nuove!',
        'share_text' => 'Scopri Multics Exchanger per scambiare linee in modo semplice e sicuro!',
        'terms_lead' => 'Consulta i termini di utilizzo di Multics Exchanger',
        'terms_heading' => 'Termini di Servizio',
        'terms_content' => 'Benvenuto su Multics Exchanger. Utilizzando questo servizio, accetti i seguenti termini... (aggiungi dettagli specifici).',
        'privacy_lead' => 'Consulta la nostra politica sulla privacy',
        'privacy_heading' => 'Privacy Policy',
        'privacy_content' => 'Multics Exchanger si impegna a proteggere i tuoi dati personali. Questa policy descrive come raccogliamo, usiamo e proteggiamo le informazioni... (aggiungi dettagli specifici).',
        'contact_lead' => 'Contattaci per supporto o domande',
        'contact_heading' => 'Contattaci',
        'contact_content' => 'Puoi contattarci tramite questo form. Risponderemo il prima possibile.',
        'contact_name' => 'Nome:',
        'contact_email' => 'Email:',
        'contact_message' => 'Messaggio:',
        'placeholder_name' => 'Inserisci il tuo nome',
        'placeholder_email' => 'Inserisci la tua email',
        'placeholder_message' => 'Scrivi il tuo messaggio qui',
        'submit_contact' => 'Invia',
    ],
    'en' => [
        'home_title' => 'Multics Exchanger - Homepage',
        'home_heading' => 'Multics Exchanger',
        'home_lead' => 'Exchange your lines with our server in just a few clicks!',
        'login' => 'Login',
        'register' => 'Register',
        'dashboard' => 'Go to Dashboard',
        'logout' => 'Logout',
        'welcome' => 'Welcome, ',
        'cache_tab' => 'Cache Exchange',
        'mgcamd_tab' => 'Mgcamd Exchange',
        'newcamd_tab' => 'Newcamd Exchange',
        'cache_title' => 'Cache Exchange',
        'mgcamd_title' => 'Mgcamd Exchange',
        'newcamd_title' => 'Newcamd Exchange',
        'ip_dns' => 'IP or DNS:',
        'port' => 'Port:',
        'hostname' => 'Hostname or IP:',
        'ports' => 'Ports (comma-separated):',
        'username' => 'Username:',
        'password' => 'Password:',
        'contact' => 'Contact (e.g., Telegram):',
        'profiles' => 'Select Profiles:',
        'submit_cache' => 'Exchange Cache',
        'submit_mgcamd' => 'Exchange Mgcamd',
        'submit_newcamd' => 'Exchange Newcamd',
        'about' => 'About Us',
        'about_lead' => 'Learn more about Multics Exchanger',
        'terms' => 'Terms of Service',
        'privacy' => 'Privacy Policy',
        'contact_us' => 'Contact Us',
        'footer_copyright' => ' %s %s. All rights reserved.',
        'login_title' => 'Login',
        'login_heading' => 'Login',
        'error_login' => 'Invalid username or password.',
        'error_empty_fields' => 'Please enter username and password.',
        'no_account' => 'Dont have an account?',
        'placeholder_username' => 'Enter your username',
        'placeholder_password' => 'Enter your password',
        'register_title' => 'Register',
        'register_heading' => 'Register',
        'error_password_mismatch' => 'Passwords do not match.',
        'error_username_taken' => 'This username is already taken.',
        'error_registration' => 'Error during registration. Please try again.',
        'have_account' => 'Already have an account?',
        'confirm_password' => 'Confirm Password:',
        'placeholder_confirm_password' => 'Confirm your password',
        'dashboard_title' => 'Dashboard',
        'dashboard_heading' => 'Dashboard',
        'dashboard_lead' => 'Your saved lines',
        'back_home' => 'Back to Homepage',
        'line_title' => 'Line %s',
        'line' => 'Line:',
        'date' => 'Date:',
        'no_lines' => 'You havent saved any lines yet. Go back to the homepage to exchange new ones!',
        'share_text' => 'Discover Multics Exchanger to exchange lines easily and securely!',
        'terms_lead' => 'Review the Terms of Service for Multics Exchanger',
        'terms_heading' => 'Terms of Service',
        'terms_content' => 'Welcome to Multics Exchanger. By using this service, you agree to the following terms... (add specific details).',
        'privacy_lead' => 'Review our Privacy Policy',
        'privacy_heading' => 'Privacy Policy',
        'privacy_content' => 'Multics Exchanger is committed to protecting your personal data. This policy describes how we collect, use, and protect your information... (add specific details).',
        'contact_lead' => 'Contact us for support or questions',
        'contact_heading' => 'Contact Us',
        'contact_content' => 'You can contact us through this form. We will respond as soon as possible.',
        'contact_name' => 'Name:',
        'contact_email' => 'Email:',
        'contact_message' => 'Message:',
        'placeholder_name' => 'Enter your name',
        'placeholder_email' => 'Enter your email',
        'placeholder_message' => 'Write your message here',
        'submit_contact' => 'Submit',
    ]
];

// Imposta la lingua predefinita (italiano) o rileva dalla query string
if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $languages)) {
    $lang = $_GET['lang'];
} else {
    $lang = 'it'; // Lingua predefinita
}

function __($key, $placeholders = []) {
    global $languages, $lang;
    $translation = $languages[$lang][$key] ?? $key;
    foreach ($placeholders as $placeholder => $value) {
        $translation = str_replace("%$placeholder%", $value, $translation);
    }
    return $translation;
}