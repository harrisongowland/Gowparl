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
    if (strlen($row['adminSessionKey']) === 0 || strlen($row['adminPassword']) === 0){
      $setAdminKey = $mysqli->prepare('UPDATE websiteData SET adminSessionKey=?');
      $_SESSION['adminKey'] = random_bytes(15);
      $setAdminKey->bind_param('s', password_hash($_SESSION['adminKey'], PASSWORD_DEFAULT));
      $setAdminKey->execute();
      $setAdminKey->close();
    }
    else if (password_verify($_SESSION['adminKey'], $row['adminSessionKey'])){
      //This is the administrator
    }
    else{
      header('Location:index.php');
      exit();
    }
  }
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

$emptyCheck = 'SELECT * FROM websiteData';
$result = $mysqli->query($emptyCheck);
if (!($obj = $result->fetch_object())){
  header('Location:hd-setup.php');
}

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <script src="https://kit.fontawesome.com/f2375e8543.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  </head>
  <body>
    <section id='headerBG' class="hero">
    <div class="hero-body">
      <div id="titleSection">
      <a href='./index.php'><p id="websiteTitle" class="title">
        Gowparl Setup
      </p></a>
    </div>
    <div id="logoSection">
      <img id='gowparlLogo' src='https://bulma.io/images/placeholders/128x128.png' style='max-width:400px; height:auto;width:60%'/>
    </div>
    </div>
  </section>

  <?php

  $getAdminSessionKey = $mysqli->prepare('SELECT adminSessionKey FROM websiteData');
  $getAdminSessionKey->execute();
  $result = $getAdminSessionKey->get_result();
  $getAdminSessionKey->close();

  if ($result->num_rows > 0){
    while ($row = $result->fetch_assoc()){
      if (password_verify($_SESSION['adminKey'], $row['adminSessionKey'])){
        echo '<nav class="navbar" role="navigation" aria-label="main navigation" style="box-shadow: 0px 10px 10px rgba(224, 224, 224, 0.5);">
        <div class="navbar-brand">

          <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
          </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
          <div class="navbar-start">
            <a class="navbar-item" href="./index.php">
              Main Page
            </a>

            <a class="navbar-item" href="./hd-records.php">
              Uploaded Records
            </a>

            <a class="navbar-item" href="./hd-settings.php">
              Settings
            </a>
          </div>

          <div class="navbar-end">
            <div class="navbar-item">
              <div class="buttons">
                <a href="./hd-logout.php" class="button is-light">
                  Log Out
                </a>
              </div>
            </div>
          </div>
        </div>
      </nav>';
      }
      else {
        echo '<nav class="navbar" role="navigation" aria-label="main navigation" style="box-shadow: 0px 10px 10px rgba(224, 224, 224, 0.5);">
        <div class="navbar-brand">

          <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
          </a>
        </div>

        <div id="navbarBasicExample" class="navbar-menu">
          <div class="navbar-start">
            <a class="navbar-item" href="./index.php">
              Main Page
            </a>
          </div>

          <div class="navbar-end">
            <div class="navbar-item">
              <div class="buttons">
                <a href="./hd-login.php" class="button is-light">
                  Log In
                </a>
              </div>
            </div>
          </div>
        </div>
      </nav>';
      }
    }
  }

  ?>

<div class="tile is ancestor">
  <div class="tile is-4">
    <div class="card" style="height:100%">
      <header class="card-header">
      <div class="card-content">
        <div class="content">
          <p>
            <strong>Records from connected Discord bot</strong>
          </p>
          This list is a timestamped collection of all the recorded conversations reported to this site by the Gowparl Discord bot. Click on one to process it.
          <button id="recordsProcessed" class="button is-info mt-2">Show processed records</button>
        </div>
      </div>
    </div>
  </div>
  <div class="tile is-8">
    <div id="meetingContainer" style="width:100%">
  <?php

    $getDebates = $mysqli->prepare('SELECT * FROM posts');
    $getDebates->execute();
    $result = $getDebates->get_result();
    $getDebates->close();
    $processed = '<span class="is-danger"> Not processed</span>';
    if ($result->num_rows > 0){
      while ($row = $result->fetch_assoc()){
        if ($row['processed'] === '1'){
          echo '<a class="recordCard" data-processed=1 href="./hd-upload.php?id=' . $row['identifier'] . '"><div class="card p-2">
          <header class="card-header">
            <p class="card-header-title">
              ' . $row["debateName"] . ' (Processed)
            </p>
          </header>
        </div></a>';
      } else {
        echo '<a href="./hd-upload.php?id=' . $row['identifier'] . '" data-processed=0><div class="card p-2">
        <header class="card-header">
          <p class="card-header-title">
            ' . $row["debateName"] . ' (Not processed)
          </p>
        </header>
      </div></a>';
    }
    }
    }
    else {
      echo '<div class="card p-2">
      <header class="card-header">
        <p class="card-header-title">
          No records found.
        </p>
      </header>
    </div>';
    }

  ?>
  </div>
  </div>
  </div>
</body>

<script>

var headerBG = document.getElementById('headerBG');
var gowparlLogo = document.getElementById('gowparlLogo');
var logoSection = document.getElementById('logoSection');
var titleSection = document.getElementById('titleSection');

var recordsProcessed = document.getElementById('recordsProcessed');
var showingProcessedRecords = 0;

$('.recordCard').each(function(){
  var currentCard = this;
  if (currentCard.dataset.processed === "1"){
    currentCard.childNodes[0].style.display = "none";
  }
})

recordsProcessed.addEventListener('click', function(){
  if (showingProcessedRecords === 0){
    showingProcessedRecords = 1;
    recordsProcessed.innerHTML = 'Hide processed records';
  }
  else {
    showingProcessedRecords = 0;
    recordsProcessed.innerHTML = 'Show processed records';
  }
  $('.recordCard').each(function(){
    var currentCard = this;
    if (currentCard.dataset.processed === "1"){
        if (showingProcessedRecords === 0){
          currentCard.childNodes[0].style.display = "none";
        }
        else {
          currentCard.childNodes[0].style.display = "flex";
        }
    }
  })
})

function setupPage(){

  var title = '<?php

    $titlequery = 'SELECT websiteName FROM websiteData';
    $result = $mysqli->query($titlequery);
    if ($result->num_rows > 0){
      while ($row = $result->fetch_assoc()){
        echo $row['websiteName'];
      }
    }

  ?>'

  document.title = title + " - Main Page";
  document.getElementById('websiteTitle').innerHTML = title;

  var check = '<?php

    $logoquery = 'SELECT headerImageUsed FROM websiteData';
    $result = $mysqli->query($logoquery);
    if ($result->num_rows > 0){
      while ($row = $result->fetch_assoc()){
        echo $row['headerImageUsed'];
      }
    }

  ?>';
  if (check === '0'){
    logoSection.style.display = 'none';
    titleSection.style.display = 'block';
  }
  else {
    logoSection.style.display = 'block';
    titleSection.style.display = 'none';
    gowparlLogo.src = '<?php

    $getlogo = 'SELECT headerImage from websiteData';
    $result = $mysqli->query($getlogo);
    if ($result->num_rows > 0){
      while($row = $result->fetch_assoc()){
        echo '/gowparl/uploads/' . $row['headerImage'];
      }
    }

    ?>';
  }

  //Set color
  var pageColor = '<?php

  $getcolor = 'SELECT websiteColor from websiteData';
  $result = $mysqli->query($getcolor);
  if ($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
      echo $row['websiteColor'];
    }
  }

  ?>'

  switch(pageColor) {
    case '1':
      headerBG.classList.add('is-link');
      break;
    case '2':
      headerBG.classList.add('is-primary');
      break;
    case '3':
      headerBG.classList.add('is-danger');
      break;
    case '4':
      headerBG.classList.add('is-warning');
      break;
    case '5':
      headerBG.classList.add('is-info');
      break;
  }
}

setupPage();

</script>
</html>
