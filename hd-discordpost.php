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

//Is this the correct api key?
$getApiKey = $mysqli->prepare('SELECT identifier FROM websiteData');
$getApiKey->execute();
$result = $getApiKey->get_result();
$getApiKey->close();

echo var_dump($_POST['votes']);

if ($result->num_rows > 0){
  while($row = $result->fetch_assoc()){
    if (password_verify($_POST['apiKey'], $row['identifier'])){
      $result = array_values(json_decode($_POST['messages'], true));
      echo $_POST['messages'];
      $dateTimeNoSlashes = str_replace('/', '', $_POST['datetime']);
      echo __DIR__ . '/debates/' . $dateTimeNoSlashes . '.txt';
      $myfile = fopen(__DIR__ . '/debates/' .  $dateTimeNoSlashes . '.txt', 'w');
      if ($myfile === false){
        echo 'Uh oh - no file permission';
      }
      for ($x = 0; $x < count($result); $x++){
        if (isset($result[$x]['message'])){
          if (strpos($result[$x]['message'], "!hdrecord") !== false){
            continue;
          }
        $messageEscaped = str_replace(';', '#esc#', $result[$x]['message']);
        $messageEscaped = str_replace('/', '#efs#', $messageEscaped);
        fwrite($myfile, $result[$x]['speaker'] . ';' . $messageEscaped . ';' . $result[$x]['createdAt'] . '/');
        }
        else if (isset($result[$x]['voteSubject'])){
          fwrite($myfile, 'vote;' . $result[$x]['voteSubject'] . ';' . $result[$x]['voteAuthor'] . ';' . $result[$x]['voteID'] . '/');
          echo 'Vote subject: ' . $result[$x]['voteSubject'] . ' called by ' . $result[$x]['voteAuthor'] . '. ';
        }
      }
      fclose($myfile);
      $sql2 = 'CREATE TABLE IF NOT EXISTS posts (
        debateName VARCHAR(500),
        identifier VARCHAR(10),
        fileName VARCHAR(29),
        processed VARCHAR(1),
        votes VARCHAR(10000),
        timeInfo VARCHAR (30)
        )';
      if ($mysqli->query($sql) === FALSE){
        header('Location:hd-error.php');
        exit();
      }
      $randomBytes = bin2hex(random_bytes(10));
      $fileName = $dateTimeNoSlashes . '.txt';
      $setPosts = $mysqli->prepare('INSERT INTO posts (debateName, identifier, fileName, processed, votes, timeInfo) VALUES (?, ?, ?, "0", ?, ?)');
      $setPosts->bind_param('sssss', $_POST['recordingname'], $randomBytes, $fileName, $_POST['votes'], $_POST['datetime']);
      $setPosts->execute();
      $setPosts->close();
      echo '200:OK';
      break;
    }
    else {
      echo '80:ERROR';
      break;
    }
  }
}
?>
