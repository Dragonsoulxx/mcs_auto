<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();
session_destroy();
header("Location: index.php?lang=" . (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'en']) ? $_GET['lang'] : 'it'));
exit();
?>