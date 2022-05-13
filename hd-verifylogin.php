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
  authToken VARCHAR(100)
  )';
if ($mysqli->query($sql) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$getUsernameAndPassword = $mysqli->prepare('SELECT adminUsername, adminPassword FROM websiteData');
$getUsernameAndPassword->execute();
$result = $getUsernameAndPassword->get_result();
$getUsernameAndPassword->close();

if ($result->num_rows > 0){
  while($row = $result->fetch_assoc()){
    if ($_POST['username'] === $row['adminUsername'] && password_verify($_POST['password'], $row['adminPassword'])){
      $_SESSION['username'] = '';
      $_SESSION['password'] = '';
      $setAdminKey = $mysqli->prepare('UPDATE websiteData SET adminSessionKey=?');
      $sessionkey = bin2hex(random_bytes(15));
      $setAdminKey->bind_param("s", password_hash($sessionkey, PASSWORD_DEFAULT));
      $setAdminKey->execute();
      $setAdminKey->close();
      $_SESSION['adminKey'] = $sessionkey;
      header('Location:index.php');
    }
    else {
      header('Location:hd-login.php?error=yes');
    }
  }
}


 ?>
