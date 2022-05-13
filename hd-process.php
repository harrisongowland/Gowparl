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
      header('Location:index.php');
    }
    else if (password_verify($_SESSION['adminKey'], $row['adminSessionKey'])){
      //all good ig
    }
    else {
      header('Location:index.php');
    }
  }
}
else {
  header('Location:index.php');
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
      <p id="websiteTitle" class="title">
        Gowparl Setup
      </p>
    </div>
    <div id="logoSection">
      <img id='gowparlLogo' src='https://bulma.io/images/placeholders/128x128.png' style='max-width:400px; height:auto;width:60%'/>
    </div>
    </div>
  </section>

  <div class="p-6">
    <h1 class="title">Process Discord conversation</h1>
  </div>
</body>

<script>

var headerBG = document.getElementById('headerBG');
var gowparlLogo = document.getElementById('gowparlLogo');
var logoSection = document.getElementById('logoSection');
var titleSection = document.getElementById('titleSection');

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
