<?php

session_start();

$_SESSION['adminKey'] = '';

header('Location: ./index.php');

?>
