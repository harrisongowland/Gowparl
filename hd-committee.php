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

  <h3 class="title ml-6 mr-6 mt-3">
    Rename committees
  </h3>
  <h3 class="subtitle ml-6 mr-6">
    Committees that aren't used in any debates are not listed.
  </h3>

  <?php

  $committeequery = $mysqli->prepare('SELECT * FROM committees');
  $committeequery->execute();
  $commRes = $committeequery->get_result();
  $committeequery->close();

  $debateQuery = $mysqli->prepare('SELECT * FROM debates');
  $debateQuery->execute();
  $debRes = $debateQuery->get_result();
  $debateQuery->close();

  $validCommittees = array();

  if ($debRes->num_rows > 0){
    while ($rowDebates = $debRes->fetch_assoc()){
      array_push($validCommittees, $rowDebates['committee']);
    }
  }
  else {
    echo '<p class="ml-6 mr-6">
    No results found.
    </p>';
  }

  if (count($validCommittees) === 0){
      echo '<p class="ml-6 mr-6">
      No results found.
      </p>';
    }
  else {
    if ($commRes->num_rows > 0){
      while ($row=$commRes->fetch_assoc()){
        if (in_array($row['committeeName'], $validCommittees)){
          echo '<div class="card ml-6 mr-6 mt-3 p-2">
            <h3 class="subtitle">' . $row["committeeName"] . '</h3>
            <div class="field">
              <label class="label">Rename</label>
                <div class="control">
                <input data-targetee="' . $row["committeeName"] . '"class="renameInput input" type="text" placeholder="New name">
                </div>
                </div>

                <div class="field">
                <div class="control">
                <button data-target="' . $row["committeeName"] . '" class="renameCommittee button is-link">Submit</button>
                </div>
                </div>
            </div>';
      }
    }
  }
}
  ?>

</body>

<script>

var headerBG = document.getElementById('headerBG');
var gowparlLogo = document.getElementById('gowparlLogo');
var logoSection = document.getElementById('logoSection');
var titleSection = document.getElementById('titleSection');

$('.renameCommittee').each(function (){
  var commButton = this;
  commButton.addEventListener('click', function(){
    $('.renameInput').each(function (){
      var commInput = this;
      if (commInput.dataset.targetee === commButton.dataset.target){
        //This is the one we're after
        if (commInput.value.length === 0){
          alert('You need to enter a new name for this committee.');
        }
        else {
          var form_data = new FormData();
          form_data.append('oldCommittee', commInput.dataset.targetee);
          form_data.append('newCommittee', commInput.value);
          form_data.append('adminKey', "<?php echo $_SESSION['adminKey']?>")
          $.ajax({
            url: './hd-updatecommittee.php',
            dataType: 'text',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(php_script_response){
              if (php_script_response.startsWith('200')){
                //The update was successful.
                location.reload();
              }
              else{
                var error = php_script_response.split(':')[1];
                if (error === 'NOEXIST'){
                  alert('Error: the requested committee to replace does not exist. Please refresh the page and try again.');
                }
                else if (error === 'API'){
                  alert('Authorisation error: you do not have permission to perform this request. Please try again later.');
                }
                else {
                  alert(php_script_response);
                }
              }
            }
          })
        }
        return false;
      }
    })
  });
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
