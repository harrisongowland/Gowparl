<?php

session_start();
require_once(__DIR__ . '/hd-config.php');

$dbHost = DB_HOST;
$dbUser = DB_USER;
$dbName = DB_NAME;
$dbPass = DB_PASSWORD;

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if ($mysqli -> connect_errno){
  header('Location:hd-error.php');
  exit();
}

$acquireAdminKey = 'SELECT adminSessionKey, adminPassword FROM websiteData';
$adkRes = $mysqli->query($acquireAdminKey);
if ($adkRes->num_rows > 0){
  //There is a session key.
  while ($row = $adkRes->fetch_assoc()){
    if (strlen($row['adminSessionKey']) === 0){
      echo 'Error: no API key found. Please reinstall.';
    }
    else if (password_verify($_POST['adminKey'], $row['adminSessionKey'])){
      regenerateKey($mysqli);
    }
    else {
      echo 'Authorisation error: you do not have permission to perform this function. Please try again later.';
    }
  }
}
else {
  echo 'Error: no API key found. Please reinstall.';
}


function regenerateKey($database){
  $updateAdminKey = $database->prepare('UPDATE websiteData SET identifier=?');
  $newKey = bin2hex(random_bytes(15));
  $hashedKey = password_hash($newKey, PASSWORD_DEFAULT);
  $updateAdminKey->bind_param("s", $hashedKey);
  $updateAdminKey->execute();
  $updateAdminKey->close();

  echo 'Your new API key is ' . $newKey . ' - please keep it safe as we will not be able to show it to you again. If you forget it, you will need to reset the key from this page.';
}
?>
