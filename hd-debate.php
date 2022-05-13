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

$sql3 = 'CREATE TABLE IF NOT EXISTS debates (
  identifier VARCHAR(100),
  debateName VARCHAR(100),
  committee VARCHAR(100),
  file VARCHAR(29),
  peopleData VARCHAR(1000),
  colorData VARCHAR(1000),
  roleData VARCHAR(1000),
  timeInfo INT(11)
  )';
if ($mysqli->query($sql3) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$sql4 = 'CREATE TABLE IF NOT EXISTS people (
  identifier VARCHAR(100),
  name VARCHAR(100),
  position VARCHAR(100),
  color VARCHAR(100)
)';
if ($mysqli->query($sql4) === FALSE){
  header('Location:hd-error.php');
  exit();
}

$emptyCheck = 'SELECT * FROM websiteData';
$result = $mysqli->query($emptyCheck);
if (!($obj = $result->fetch_object())){
  header('Location:hd-setup.php');
}

$sanitycheck = $mysqli->prepare('SELECT * FROM debates WHERE identifier=?');
$sanitycheck->bind_param("s", $_GET['id']);
$sanitycheck->execute();
$result = $sanitycheck->get_result();
$sanitycheck->close();

if ($result->num_rows > 0){
  //This debate exists
}
else {
  //header('Location:index.php');
}



?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.3.0/css/font-awesome.css" rel="stylesheet" type='text/css'>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://kit.fontawesome.com/f2375e8543.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.1/chart.min.js"></script>
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
  <?php

  $getHeaderColor = $mysqli->prepare('SELECT websiteColor FROM websiteData');
  $getHeaderColor->execute();
  $colorRes = $getHeaderColor->get_result();
  $getHeaderColor->close();

  if ($colorRes->num_rows > 0){
    while($row = $colorRes->fetch_assoc()){
      switch($row['websiteColor']) {
        case '1':
          echo '<section class="hero is-small is-link">';
          break;
        case '2':
          echo '<section class="hero is-small is-primary">';
          break;
        case '3':
          echo '<section class="hero is-small is-danger">';
          break;
        case '4':
          echo '<section class="hero is-small is-warning">';
          break;
        case '5':
          echo '<section class="hero is-small is-info">';
          break;
      }
    }
  }
  else {
    echo '<section class="hero is-small">';
  }
  ?>
  <div class="hero-body">
    <p id="debateName" class="title pl-5">
      <?php

      $getDebateName = $mysqli->prepare('SELECT debateName FROM debates WHERE identifier=?');
      $getDebateName->bind_param('s', $_GET['id']);
      $getDebateName->execute();
      $debResult = $getDebateName->get_result();
      $getDebateName->close();

      if ($debResult->num_rows > 0){
        while ($row = $debResult->fetch_assoc()){
          echo $row['debateName'];
          break;
        }
      }
      else {
        echo 'Error getting debate name';
      }

      ?>
    </p>
    <p id="debateCommittee" class="subtitle pl-5">
      <?php

      $getCommittee = $mysqli->prepare('SELECT committee FROM debates WHERE identifier=?');
      $getCommittee->bind_param('s', $_GET['id']);
      $getCommittee->execute();
      $commResult = $getCommittee->get_result();
      $getCommittee->close();

      if ($commResult->num_rows > 0){
        while ($row = $commResult->fetch_assoc()){
          echo $row['committee'];
          break;
        }
      }
      else {
        echo 'Error getting committee name';
      }

      ?>
    </p>
  </div>
</section>
<div class="container">
  <div class="card mt-2 p-4">
    <?php

    $getFileName = $mysqli->prepare('SELECT file, timeInfo FROM debates WHERE identifier=?');
    $getFileName->bind_param("s", $_GET['id']);
    $getFileName->execute();
    $result = $getFileName->get_result();
    $getFileName->close();

    $index = 0;

    if($result->num_rows > 0){
      while($row = $result->fetch_assoc()){
        $getVotes = $mysqli->prepare('SELECT votes, timeInfo FROM posts WHERE identifier=?');
        $getVotes->bind_param("s", $_GET['id']);
        $getVotes->execute();
        $voteResult = $getVotes->get_result();
        $getVotes->close();
        $votes = '';
        if ($voteResult->num_rows > 0){
          while($row2 = $voteResult->fetch_assoc()){
            $votes = $row2['votes'];
            echo '<h3 class="title"><i class="fa fa-clock"></i>  Discussion convened: ' . date('jS F Y h:i:s', $row['timeInfo']) . '</h3>';
            break;
          }
        }
        $voteContents = json_decode($votes, true);
        $fileContents = file_get_contents('./debates/' . $row['file']);
        $messages = explode('/', $fileContents);
        $previousWriter = '';
        $position = '';
        $name = '';
        $getData = $mysqli->prepare('SELECT peopleData, roleData, colorData FROM debates WHERE identifier=?');
        $getData->bind_param("s", $_GET['id']);
        $getData->execute();
        $result = $getData->get_result();
        $getData->close();

        if ($result->num_rows > 0){
          while ($row=$result->fetch_assoc()){
            $peopleData = json_decode($row['peopleData'], true);
            $roleData = json_decode($row['roleData'], true);
            $colorData = json_decode($row['colorData'], true);
          }
        }
        foreach ($messages as $message){
          if (strlen($message[1]) > 0){
          $messageData = explode(';', $message);
          if ($messageData[0] === 'vote'){
            $forVotes = '';
            foreach ($voteContents[$messageData[3]]['forVotes'] as $voter){
              foreach ($peopleData as $person){
                if ($person['rawName'] === $voter){
                  foreach ($roleData as $role){
                    if ($role['rawName'] === $voter){
                      $forVotes = $forVotes . $person['parsedName'] . ' <small>(' . $role["parsedRole"] . ')</small>, ';
                    }
                  }
                }
              }
            }
          if ($forVotes === ''){
            $forVotes = "There were no votes in favour.";
          }
          else {
            $forVotes = substr($forVotes, 0, -2);
          }
          $againstVotes = '';
          foreach ($voteContents[$messageData[3]]['agaisntVotes'] as $voter){
            foreach ($peopleData as $person){
              if ($person['rawName'] === $voter){
                foreach ($roleData as $role){
                  if ($role['rawName'] === $voter){
                    $againstVotes = $againstVotes . $person['parsedName'] . ' <small>(' . $role["parsedRole"] . ')</small>, ';
                  }
                }
              }
            }
          }
          if ($againstVotes === ''){
            $againstVotes = "There were no votes against.";
          }
          else {
            $againstVotes = substr($againstVotes, 0, -2);
          }
          $abstentions = '';
          foreach ($voteContents[$messageData[3]]['abstentions'] as $voter){
            foreach ($peopleData as $person){
              if ($person['rawName'] === $voter){
                foreach ($roleData as $role){
                  if ($role['rawName'] === $voter){
                    $abstentions = $abstentions . $person['parsedName'] . ' <small>(' . $role["parsedRole"] . ')</small>, ';
                  }
                }
              }
            }
          }
          if ($abstentions === ''){
            $abstentions = "There were no abstentions.";
          }
          else {
            $abstentions = substr($abstentions, 0, -2);
          }

          $voteCaller = '';
          $voteCallerPos = '';
          $found = False;
          foreach ($peopleData as $person){
            if ($person['rawName'] === $messageData[2]){
              //This is the vote caller
              foreach ($roleData as $role){
                if ($role['rawName'] === $messageData[2]){
                  $voteCaller = $person['parsedName'];
                  $voteCallerPos = $role['parsedRole'];
                  $found = True;
                  break;
                }
              }
              if ($found === True){
                break;
              }
            }
          }
          //This is a vote. Display the chart.
          echo '<article class="media">
          <figure class="media-left">
          </figure>
          <div class="media-content">
          <div class="content">
          <p>
          <h2 class="title">Vote called by ' . $voteCaller . ', ' . $voteCallerPos . '</h2>
          <h4 class="subtitle">Subject: ' . $messageData[1] . '</h4>
          </p><br />
          <div class="columns is-mobile">
          <div class="column is-half">
          <canvas id="chart' . $messageData[3] . '"></canvas>
          </div>
          <div class="column is-half">
          <article class="message is-primary">
          <div class="message-header">
          <p class="m-0">Votes in favour</p>
          <button class="button is-primary voteRevealButton" data-target="for-' . $messageData[3] . '"><i class="fa fa-arrow-down"></i></button>
          </div>
          <div class="message-body voteReveal" style="display:none" data-targetee="for-' . $messageData[3] . '">
          '. $forVotes . '
          </div>
          </article>
          <article class="message is-danger">
          <div class="message-header">
          <p class="m-0">Votes against</p>
          <button class="button is-danger voteRevealButton" data-target="against-' . $messageData[3] . '"><i class="fa fa-arrow-down"></i></button>
          </div>
          <div class="message-body voteReveal" style="display:none" data-targetee="against-' . $messageData[3] . '">
          '. $againstVotes . '
          </div>
          </article>
          <article class="message is-info">
          <div class="message-header">
          <p class="m-0">Abstentions</p>
          <button class="button is-info voteRevealButton" data-target="abstain-' . $messageData[3] . '"><i class="fa fa-arrow-down"></i></button>
          </div>
          <div class="message-body voteReveal" style="display:none" data-targetee="abstain-' . $messageData[3] . '">
          '. $abstentions . '
          </div>
          </article>
          </div>
          </div>
          </div>
          </div>
          </article>
          <script>
          const ctx' . $messageData[3] . ' = document.getElementById("chart' . $messageData[3] . '");
          const myChart' . $messageData[3] . ' = new Chart(ctx' . $messageData[3] . ', {
            type: "bar",
            title: "Voting results",
            data: {
              labels: ["Yes", "No", "Abstain"],
              datasets: [{
                data: [' . count($voteContents[$messageData[3]]["forVotes"]) . ', ' . count($voteContents[$messageData[3]]["againstVotes"]) . ', ' . count($voteContents[$messageData[3]]["abstentions"]) . '],
                backgroundColor: [
                  "rgba(0, 255, 0, 0.2)",
                  "rgba(255, 0, 0, 0.2)",
                  "rgba(0, 0, 255, 0.2)"
                ]
              }]
            },
            options: {
              plugins: {
                legend: {
                  display: false
                }
              },
              indexAxis: "y",
              scales: {
                y: {
              beginAtZero: true
            }
          }
          }
          });

          </script>';
          }
          else {
            $date = explode(' ', $messageData[2]);
            $position = '';
            $name = '';

            foreach ($peopleData as $person){
              if ($person['rawName'] === $messageData[0]){
                foreach ($roleData as $role){
                  if ($role['rawName'] === $messageData[0]){
                    $position = $role['parsedRole'];
                    $name = $person['parsedName'];
                  }
                }
              }
            }
            if ($previousWriter === $messageData[0]){
              if ($usingColor === True){
              echo '<p class="pl-2" style="border-left:6px solid ' . $currColor . '">
              ' . $messageData[1] . '
              </p>';
              }
              else {
                echo '<p class="pl-2">
                ' . $messageData[1] . '
                </p>';
              }
            }
            else {
              $positionHTML = '';
              if (strlen($position) !== 0){
                $positionHTML = '<small>(' . $position . ')</small>';
              }
              $messageDeEscaped = str_replace('#esc#', ';', $messageData[1]);
              $messageDeEscaped = str_replace('#efs#', '/', $messageDeEscaped);
              $usingColor = False;
              $currColor = '';
              foreach ($colorData as $color){
                if ($color['rawName'] === $messageData[0]){
                  if ($color['useColor'] === 'true'){
                    $usingColor = True;
                    $currColor = $color['color'];
                  }
                }
              }
              if ($usingColor === True){
              echo '<article class="media mt-1 mb-0 pl-2" style="border-left:6px solid ' . $currColor . '">
              <div class="media-content">
                <div class="content">
                  <p>
                  <strong>' . $name . '</strong> ' . $date[1] . '<br /></b4>' . $positionHTML . '
                  <br>
                  ' . $messageDeEscaped . '  </p>
                  </div>
                  </div>
                  </article>';
                }
                else {
                  echo '<article class="media mt-1 mb-0 pl-2">
                  <div class="media-content">
                    <div class="content">
                      <p>
                      <strong>' . $name . '</strong> ' . $date[1] . '<br /></b4>' . $positionHTML . '
                      <br>
                      ' . $messageDeEscaped . '  </p>
                      </div>
                      </div>
                      </article>';
                }
            }
            $previousWriter = $messageData[0];
            $index = $index + 1;
          }
        }
      }
    }
  }
    else {
      header('Location:index.php');
    }

    ?>
  </div>
</div>

  </body>

  <script>

  var headerBG = document.getElementById('headerBG');
  var gowparlLogo = document.getElementById('gowparlLogo');
  var logoSection = document.getElementById('logoSection');
  var titleSection = document.getElementById('titleSection');

  $('.voteRevealButton').click(function(){
    var test = this;
    console.log(this.dataset);
    $('.voteReveal').each(function(){
      var reveal = this;
      var index = test.dataset.target.split('-');
      if (reveal.dataset.targetee === test.dataset.target){
        if (reveal.style.display === "block"){
          console.log('already revealed');
          reveal.style.display = "none";
        }
        else {
          reveal.style.display = "block";
        }
      }
      else {
        var testIndex = reveal.dataset.targetee.split('-');
        if (index[1] === testIndex[1]){
          //This is one to hide
          reveal.style.display = "none";
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
