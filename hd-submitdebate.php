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

if (strlen($_POST['identifier']) > 100 || strlen($_POST['debateName']) > 100 || strlen($_POST['committee']) > 100 || strlen($_POST['file']) > 29){
  echo '80:TOOLONG';
  exit();
}

if (strlen($_POST['identifier']) === 0 || strlen($_POST['debateName']) === 0 || strlen($_POST['committee']) === 0 || strlen($_POST['file']) === 0){
  echo '80:TOOSHORT';
  exit();
}

if ($_POST['newCommittee'] === '1'){
  $checkIfCommTaken = $mysqli->prepare('SELECT * FROM committees WHERE committeeName = ?');
  $checkIfCommTaken->bind_param('s', $_POST['committee']);
  $checkIfCommTaken->execute();
  $checkRes = $checkIfCommTaken->get_result();
  $checkIfCommTaken->close();

  if ($checkRes->num_rows > 0){
    //taken already.
    echo '80:TAKEN';
    exit();
  }

  $insertComm = $mysqli->prepare('INSERT INTO committees (committeeName) VALUES (?)');
  $insertComm->bind_param('s', $_POST['committee']);
  $insertComm->execute();
  $insertComm->close();
}

$getTimeInfo = $mysqli->prepare('SELECT timeInfo FROM posts WHERE identifier=?');
$getTimeInfo->bind_param('s', $_POST['identifier']);
$getTimeInfo->execute();
$getTime=$getTimeInfo->get_result();
$getTimeInfo->close();

$timestamp = 0;

if ($getTime->num_rows > 0){
  while($row=$getTime->fetch_assoc()){
    $convertthis = $row['timeInfo'];
    $dateAndTime = explode(' ', $convertthis);
    $dateData = explode('/', $dateAndTime[0]);
    $timeData = explode(':', $dateAndTime[1]);

    $timestamp = mktime(intval($timeData[0]), intval($timeData[1]), intval($timeData[2]), intval($dateData[1]), intval($dateData[0]), intval($dateData[2]));
  }
}
else {
  echo '80:TIMEERR';
  exit();
}


$existingDebate = $mysqli->prepare("SELECT * FROM debates WHERE identifier=?");
$existingDebate->bind_param("s", $_POST['identifier']);
$existingDebate->execute();
$result = $existingDebate->get_result();
$existingDebate->close();


if ($result->num_rows > 0){
  //This one already exists
  $updateDebate = $mysqli->prepare('UPDATE debates SET debateName=?, committee=?, peopleData=?, colorData=?, roleData=?, timeInfo=? WHERE identifier=?');
  $updateDebate->bind_param("ssssssi", $_POST['debateName'], $_POST['committee'], $_POST['people'], $_POST['colors'], $_POST['roles'], $timestamp, $_POST['identifier']);
  $updateDebate->execute();
  $updateDebate->close();

  echo "200:OK";
}
else {
  $addNewDebate = $mysqli->prepare('INSERT INTO debates (identifier, debateName, committee, file, peopleData, colorData, roleData, timeInfo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
  $addNewDebate->bind_param("sssssssi", $_POST['identifier'], $_POST['debateName'], $_POST['committee'], $_POST['file'], $_POST['people'], $_POST['colors'], $_POST['roles'], $timestamp);
  $addNewDebate->execute();
  $addNewDebate->close();

  $processPost = $mysqli->prepare('UPDATE posts SET processed="1" WHERE identifier=?');
  $processPost->bind_param('s', $_POST['identifier']);
  $processPost->execute();
  $processPost->close();

  echo "200:OK";
}



 ?>
