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

$acquireAdminKey = 'SELECT adminSessionKey, adminPassword FROM websiteData';
$adkRes = $mysqli->query($acquireAdminKey);
if ($adkRes->num_rows > 0){
  //There is a session key.
  while ($row = $adkRes->fetch_assoc()){
    if (strlen($row['adminSessionKey']) === 0 || strlen($row['adminPassword']) === 0){
        header('Location:hd-setup.php');
        exit();
    }
    else if (password_verify($_SESSION['adminKey'], $row['adminSessionKey'])){
      //This is the administrator
        header('Location:index.php');
        exit();
    }
    else{
      //There is an administrator but they aren't logged in
    }
  }
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

  <h3 class="pl-6 pr-6 pt-4 subtitle">Administrator login</h3>
  <div class="field pl-6 pr-6">
  <label class="label">Username</label>
  <div class="control has-icons-left has-icons-right">
    <input id="usernameEntry" class="input" type="text" placeholder="Username" >
    <span class="icon is-small is-left">
      <i class="fas fa-user"></i>
    </span>
  </div>
</div><div class="field pl-6 pr-6 pt-2">
<label class="label">Password</label>
<div class="control has-icons-left has-icons-right">
  <input id="passwordEntry" class="input" type="password" placeholder="Password" value="">
  <span class="icon is-small is-left">
    <i class="fas fa-user"></i>
  </span>
</div>
</div>
<div class="control pl-6 pr-6 pt-2">
    <button id="submitButton" class="button is-link">Log in</button>
  </div>
  <?php

  if ($_GET['error'] === 'yes') {
    //This is an error
    echo '<div class="pl-6 pr-6 pt-4">
    <article class="message is-danger">
    <div class="message-header">
      <p>Invalid login information</p>
      <button class="delete" aria-label="delete"></button>
    </div>
    <div class="message-body">
      Your username and password do not match the details we have on file.
    </div>
  </article>
  </div>';
  }

  ?>
</body>


<script>

var headerBG = document.getElementById('headerBG');
var gowparlLogo = document.getElementById('gowparlLogo');
var logoSection = document.getElementById('logoSection');
var titleSection = document.getElementById('titleSection');

document.getElementById('submitButton').addEventListener("click", () => {
  console.log('clicked');
  checkUsernameAndPassword();
});

function checkUsernameAndPassword(){
  console.log('Checking username and password');
  var redirect = './hd-verifylogin.php';
  $.redirectPost(redirect, {username: usernameEntry.value, password: passwordEntry.value});
}

$.extend(
{
    redirectPost: function(location, args)
    {
        var form = $('<form></form>');
        form.attr("method", "post");
        form.attr("action", location);

        $.each( args, function( key, value ) {
            var field = $('<input></input>');

            field.attr("type", "hidden");
            field.attr("name", key);
            field.attr("value", value);

            form.append(field);
        });
        $(form).appendTo('body').submit();
    }
});

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
