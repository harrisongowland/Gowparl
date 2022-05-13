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

$sql = 'CREATE TABLE IF NOT EXISTS websiteData (
  websiteName VARCHAR(50),
  headerImage VARCHAR(100),
  headerImageUsed INT(1),
  adminUsername VARCHAR(50),
  adminPassword VARCHAR(200),
  websiteColor VARCHAR(15),
  adminSessionKey VARCHAR(200),
  identifier VARCHAR(255),
  authToken VARCHAR(100),
  guildID VARCHAR(100)
  )';
if ($mysqli->query($sql) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$authorise = $mysqli->prepare('SELECT identifier FROM websiteData');
$authorise->execute();
$res = $authorise->get_result();
$authorise->close();

if ($res->num_rows > 0){
  while ($row = $res->fetch_assoc()){
    if (password_verify($_POST['apiKey'], $row['identifier'])){
      echo '200:OK';
    }
    else {
      echo 'no';
    }
  }
}
else {
  echo '80:NOAPI';
}
