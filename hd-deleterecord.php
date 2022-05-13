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

$sql2 = 'CREATE TABLE IF NOT EXISTS posts (
  debateName VARCHAR(500),
  identifier VARCHAR(10),
  fileName VARCHAR(29),
  processed VARCHAR(1),
  votes VARCHAR(10000),
  timeInfo VARCHAR (30)
  )';
if ($mysqli->query($sql2) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$sql3 = 'CREATE TABLE IF NOT EXISTS committees (
  committeeName VARCHAR(100)
  )';
if ($mysqli->query($sql3) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$sql4 = 'CREATE TABLE IF NOT EXISTS debates (
  identifier VARCHAR(100),
  debateName VARCHAR(100),
  committee VARCHAR(100),
  file VARCHAR(29),
  peopleData VARCHAR(1000),
  colorData VARCHAR(1000),
  roleData VARCHAR(1000),
  timeInfo INT(11)
  )';
if ($mysqli->query($sql4) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$checkAuthority = $mysqli->prepare('SELECT adminSessionKey FROM websiteData');
$checkAuthority->execute();
$authorityRes = $checkAuthority->get_result();
$checkAuthority->close();

if ($authorityRes->num_rows > 0){
  while($row=$authorityRes->fetch_assoc()){
    if (password_verify($_POST['adminKey'], $row['adminSessionKey'])){
      $deleteRecord = $mysqli->prepare('DELETE FROM posts WHERE identifier=?');
      $deleteRecord->bind_param('s', $_POST['identifier']);
      $deleteRecord->execute();
      $deleteRecord->close();

      $deleteDebate = $mysqli->prepare('DELETE FROM debates WHERE identifier=?');
      $deleteDebate->bind_param('s', $_POST['identifier']);
      $deleteDebate->execute();
      $deleteDebate->close();

      echo '200:OK';
    }
    else {
      echo '80:NOAPI';
    }
  }
}
else {
  echo '80:NOAPI';
}

?>
