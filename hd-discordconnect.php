<?php
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

$sreq = $mysqli->prepare('SELECT identifier FROM websiteData');
$sreq->execute();
$result = $sreq->get_result();
if ($result->num_rows > 0){
  while ($row=$result->fetch_assoc()){
    if (password_verify($_POST['apiKey'], $row['identifier'])){
      //This is a match
      echo '200:OK';
    }
    else {
      echo '80:ERROR';
    }
  }
}

?>
