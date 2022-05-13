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

$sql5 = 'CREATE TABLE IF NOT EXISTS people (
  identifier VARCHAR(100),
  name VARCHAR(100),
  position VARCHAR(100),
  color VARCHAR(100)
)';
if ($mysqli->query($sql5) === FALSE){
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
    <link rel="stylesheet" href="./style.css" />
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
      <img id='gowparlLogo' src='' style='max-width:400px; height:auto;width:60%'/>
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

<div class="p-6 mainPage">
  <h3 class="title" style="text-align:center">Latest discussions</h3>
  <?php

  $getCommittees = $mysqli->prepare('SELECT * FROM committees');
  $getCommittees->execute();
  $commResults = $getCommittees->get_result();
  $getCommittees->close();

  $validCommittees = array();

  if ($commResults->num_rows > 0){
    while($row = $commResults->fetch_assoc()){
      $currentCommittee = $row['committeeName'];
      $checkDebates = $mysqli->prepare('SELECT * FROM debates WHERE committee=?');
      $checkDebates->bind_param('s', $currentCommittee);
      $checkDebates->execute();
      $debResults = $checkDebates->get_result();
      $checkDebates->close();

      if ($debResults->num_rows > 0){
        //Valid committee
        array_push($validCommittees, $currentCommittee);
      }
    }
  }

  $opening = True;
  for ($x = 0; $x <= count($validCommittees) -1; $x++){
    $getAllCommDebates = $mysqli->prepare('SELECT * FROM debates WHERE committee=?  ORDER BY timeInfo DESC');
    $getAllCommDebates->bind_param('s', $validCommittees[$x]);
    $getAllCommDebates->execute();
    $allDebResults = $getAllCommDebates->get_result();
    $getAllCommDebates->close();

    $tileContents = '';
    $tileIndex = 0;

    if ($allDebResults->num_rows > 0){
      while($row = $allDebResults->fetch_assoc()){
        if ($tileIndex === 5){
          //This is the Limit
          $tileContents = $tileContents . '<a href="./hd-committeelist.php?name=' . $row['committee'] . '"><p class="mt-1">
            More...
          </p></a>';
          break;
        }
        $tileContents = $tileContents . '<a href="./hd-debate.php?id=' . $row['identifier'] . '"><div class="card selectable m-1">
  <div class="card-content">
    <div class="content">
    <p>
      <strong>' . $row['debateName'] . '</strong>
      <br>
      ' . date('jS F Y h:i:s', $row['timeInfo']) . '
    </p>  </div>
  </div>
</div></a>';
      $tileIndex = $tileIndex + 1;
      }
    }

    if ($opening){
      echo '<div class="tile is-ancestor">
        <div class="tile is-parent">
          <article class="tile is-child box">
            <p class="title">' . $validCommittees[$x] . '</p>
            <p class="subtitle">Latest sessions:</p>
            ' . $tileContents . '
          </article>
        </div>';
      $opening = False;
    } else {
      echo '
        <div class="tile is-parent">
          <article class="tile is-child box">
            <p class="title">' . $validCommittees[$x] . '</p>
            <p class="subtitle">Latest sessions:</p>
            ' . $tileContents . '
          </article>
        </div>
        </div>';
      $opening = True;
    }
  }
  if ($opening == False){
    echo '</div>';
  }

  ?>
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
