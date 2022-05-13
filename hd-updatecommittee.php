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

$acquireAdminKey = 'SELECT adminSessionKey FROM websiteData';
$adkRes = $mysqli->query($acquireAdminKey);
if ($adkRes->num_rows > 0){
  //There is a session key.
  while ($row = $adkRes->fetch_assoc()){
    if (!(password_verify($_POST['adminKey'], $row['adminSessionKey']))){
      echo '80:API';
      exit();
    }
  }
}


$getAllCommittees = $mysqli->prepare('SELECT * FROM committees WHERE committeeName=?');
$getAllCommittees->bind_param("s", $_POST['oldCommittee']);
$getAllCommittees->execute();
$result=$getAllCommittees->get_result();
$getAllCommittees->close();

if ($result->num_rows > 0){
  $updateCommittees = $mysqli->prepare('UPDATE committees SET committeeName=? WHERE committeeName=?');
  $updateCommittees->bind_param('ss', $_POST['newCommittee'], $_POST['oldCommittee']);
  $updateCommittees->execute();
  $updateCommittees->close();

  $updateDebates = $mysqli->prepare('UPDATE debates SET committee=? WHERE committee=?');
  $updateDebates->bind_param('ss', $_POST['newCommittee'], $_POST['oldCommittee']);
  $updateDebates->execute();
  $updateDebates->close();

  echo '200:OK';
  exit();
}
else {
  echo '80:NOEXIST';
  exit();
}

?>
