<?php
if (isset($_POST['host']) === true && empty($_POST['host']) === false) {
   $host= $_POST['host'];
   echo gethostbyname($host);
}