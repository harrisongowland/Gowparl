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

$acquireAdminKey = 'SELECT adminSessionKey, adminPassword FROM websiteData';
$adkRes = $mysqli->query($acquireAdminKey);
if ($adkRes->num_rows > 0){
  //There is a session key.
  while ($row = $adkRes->fetch_assoc()){
    if (strlen($row['adminSessionKey']) === 0 || strlen($row['adminPassword']) === 0){
      $setAdminKey = $mysqli->prepare('UPDATE websiteData SET adminSessionKey=?');
      $bytes = random_bytes(15);
      echo bin2hex($bytes);
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
else {
  $setAdminKey = $mysqli->prepare('INSERT INTO websiteData (adminSessionKey) VALUES (?)');
  $bytes = random_bytes(15);
  $_SESSION['adminKey'] = bin2hex($bytes);
  $setAdminKey->bind_param('s', password_hash($_SESSION['adminKey'], PASSWORD_DEFAULT));
  $setAdminKey->execute();
  $setAdminKey->close();
}

?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gowparl Setup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.3/css/bulma.min.css">
    <script src="https://kit.fontawesome.com/f2375e8543.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  </head>
  <body>
    <section id='headerBG' class="hero is-link">
    <div class="hero-body">
      <div id="titleSection">
      <p id="websiteTitle" class="title">
        Gowparl Setup
      </p>
      <p id="webSubtitle" class="subtitle">
        A Hansard-like page generator by <strong><a href='https://www.twitter.com/HarrisonGowland'>Harrison Gowland</a></strong>
      </p>
    </div>
    <div id="logoSection">
      <img id='gowparlLogo' src='https://bulma.io/images/placeholders/128x128.png' style='max-width:400px; height:auto;width:60%'/>
    </div>
    </div>
  </section>
  <div class='px-6 pt-4'>
    <div class="field">
  <label class="label">Website name (e.g. 'Record of Parliamentary Debates')</label>
  <div class="control">
    <input id='websiteNameInput' class="input" type="text" placeholder="Website name">
  </div>
  <p id="validWebsite" class="help is-success">This website name is valid</p>
  <p id="invalidWebsite" class="help is-danger">This website name is invalid</p>
</div>

<?php

$acquireApiKey = 'SELECT identifier FROM websiteData';
$apiRes = $mysqli->query($acquireApiKey);
if ($apiRes->num_rows > 0){
  while ($row = $apiRes->fetch_assoc()){
    if (strlen($row['identifier']) === 0){
      $setApiKey = $mysqli->prepare('UPDATE websiteData SET identifier=?');
      $apiKey = bin2hex(random_bytes(20));
      echo $apiKey;
      $hashedApiKey = password_hash($apiKey, PASSWORD_DEFAULT);
      $setApiKey->bind_param('s', $hashedApiKey);
      $setApiKey->execute();
      $setApiKey->close();
    }
  }
}
else {
  $setApiKey = $mysqli->prepare('INSERT INTO websiteData (identifier) VALUES (?)');
  $apiKey = bin2hex(random_bytes(20));
  $hashedApiKey = password_hash($apiKey, PASSWORD_DEFAULT);

  echo $apiKey;
  $setApiKey->bind_param('s', $hashedApiKey);
  $setApiKey->execute();
  $setApiKey->close();
}

$getAdminUsername = $mysqli->prepare('SELECT adminUsername FROM websiteData');
$getAdminUsername->execute();
$result = $getAdminUsername->get_result();
if ($result->num_rows > 0){
  while ($row = $result->fetch_assoc()){
    if (strlen($row['adminUsername']) === 0){
      //No admin account set up. Display API key.
      echo '<article class="message is-info">
  <div class="message-header">
    <p>API key</p>
  </div>
  <div class="message-body">
  This API key will allow you to connect the Gowparl Discord bot to this instance. For your security, we will only show this API key to you during setup. Please store it somewhere safe.<br /><strong>' . $apiKey . '</strong>
      </div>
</article>';
      break;
    }
  }
}

?>

<div class="field">
  <label class="label">Username</label>
  <div class="control has-icons-left has-icons-right">
    <input id="username" class="input" type="text" placeholder="Username">
    <span class="icon is-small is-left">
      <i class="fas fa-user"></i>
    </span>
  </div>
</div>

<div class="field">
  <label class="label">Password</label>
  <div class="control has-icons-left has-icons-right">
    <input id="password" class="input" type="password" placeholder="Password">
    <span class="icon is-small is-left">
      <i class="fas fa-lock"></i>
    </span>
  </div>
</div>

<div class="field">
  <label class="label">Colour palette</label>
  <div class="select">
  <select id='colorSelect'>
    <option value="1">Purpley-blue</option>
    <option value="2">Green</option>
    <option value="3">
      Red
    </option>
    <option value="4">
      Yellow
    </option>
    <option value="5">
      Blue
    </option>
  </select>
</div>
</div>

<div class="field">
  <label class="checkbox">
    <input type="checkbox" id='headerLogo'>
    Use website logo in headers
  </label>
</div>

<div class="field" id="fileUpload">
  <div class="file">
  <label class="file-label">
    <input class="file-input" type="file" name="resume" id='headerUpload'>
    <span class="file-cta">
      <span class="file-icon">
        <i class="fas fa-upload"></i>
      </span>
      <span class="file-label">
        Choose header image
      </span>
    </span>
  </label>
</div>
</div>

<div class="field is-grouped">
  <div class="control">
    <button id='submitButton' class="button is-link">Create site</button>
  </div>
</div>
  </div>
  </body>
</html>

<script>

var headerLogo = document.getElementById('headerLogo');
var titleSection = document.getElementById('titleSection');
var logoSection = document.getElementById('logoSection');
var gowparlLogo = document.getElementById('gowparlLogo');

var websiteName = document.getElementById('websiteNameInput');

var username = document.getElementById('username');
var password = document.getElementById('password');

var fileUpload = document.getElementById('fileUpload');
var headerUpload = document.getElementById('headerUpload');

var colorSelect = document.getElementById('colorSelect');

var submitButton = document.getElementById('submitButton');

var validWebsite = document.getElementById('validWebsite');
var invalidWebsite = document.getElementById('invalidWebsite');

validWebsite.style.display = 'none';
invalidWebsite.style.display = 'none';
fileUpload.style.display = 'none';
logoSection.style.display = 'none';

submitButton.addEventListener('click', (event) => {
  var form_data = new FormData();
  form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>')
  form_data.append('username', username.value);
  form_data.append('password', password.value);
  form_data.append('websiteName', websiteName.value);
  if (headerLogo.checked){
    form_data.append('usingHeaderImage', '1');
  }
  else {
    form_data.append('usingHeaderImage', '0');
  }
  form_data.append('site_color', colorSelect.options[colorSelect.selectedIndex].value);
  form_data.append('submit_form', '1');
  $.ajax({
    url: 'hd-imageverify.php',
    dataType: 'text',
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    type: 'post',
    success: function(php_script_response){
      if (php_script_response.startsWith('200')){
        window.location.href = './index.php';
      }
      else {
        alert(php_script_response);
      }
    }
  });
})

headerLogo.addEventListener('change', (event) => {
  if (event.currentTarget.checked){
    fileUpload.style.display = 'block';
    titleSection.style.display = 'none';
    logoSection.style.display = 'block';
  } else {
    fileUpload.style.display = 'none';
    titleSection.style.display = 'block';
    logoSection.style.display = 'none';
  }
})

var headerSrc = '<?php

$precheck = 'SHOW TABLES LIKE "websiteData"';
$precheckres = $mysqli->query($precheck);

if ($precheckres->num_rows > 0){

$imageCheck = $mysqli->query('SELECT headerImage FROM websiteData');
if ($imageCheck !== FALSE){
  if ($imageCheck->num_rows > 0){
    while ($row = $imageCheck->fetch_assoc()){
      if (strlen($row['headerImage']) === 0){
      }
      else {
      echo '/gowparl/uploads/' . $row['headerImage'];
    }
    }
  }
}
}

?>';

if (headerSrc !== ''){
  gowparlLogo.src = headerSrc;
}

websiteName.addEventListener('input', (event) => {
  if (websiteName.value.length === 0){
    document.getElementById('webSubtitle').style.display = 'block';
    document.getElementById('websiteTitle').innerHTML = 'Gowparl Setup';
  }
  else {
    document.getElementById('webSubtitle').style.display = 'none';
    document.getElementById('websiteTitle').innerHTML = websiteName.value;
  }
})

websiteName.addEventListener('change', (event) => {
  if (websiteName.value.length === 0){
    resetWebsiteNameBox();
  }
  else {
  var form_data = new FormData();
  form_data.append('website_name', websiteName.value);
  form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>')
  form_data.append('set_title', '1');
  $.ajax({
    url: 'hd-imageverify.php',
    dataType: 'text',
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    type: 'post',
    success: function(php_script_response){
      if (php_script_response.startsWith('200:')){
        validWebsite.style.display = 'block';
        invalidWebsite.style.display = 'none';
        websiteName.classList.add('is-success');
        websiteName.classList.remove('is-danger');
      }
      else{
          validWebsite.style.display = 'none';
          invalidWebsite.style.display = 'block';
          websiteName.classList.add('is-danger');
          websiteName.classList.remove('is-success');
          if (php_script_response.startsWith('144:')){
            invalidWebsite.innerHTML = 'Website name is too long. The name cannot be longer than fifty characters.';
          }
      }
    }
  });
}
})

function resetWebsiteNameBox(){
  websiteName.classList.remove('is-success');
  websiteName.classList.remove('is-danger');
  validWebsite.style.display = 'none';
  invalidWebsite.style.display = 'none';
}

headerUpload.addEventListener('change', (event) => {
  var file_data = headerUpload.files[0];
  var form_data = new FormData();
  form_data.append('file', file_data);
  form_data.append('set_header', '1');
  form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>')
  $.ajax({
    url: 'hd-imageverify.php',
    dataType: 'text',
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    type: 'post',
    success: function(php_script_response){
      if (php_script_response.startsWith('200:')){
        gowparlLogo.src = '/gowparl/' + php_script_response.split(':')[1];
        websiteName.classList.remove('is-success');
      }
      else {

      }
    }
  })
});

colorSelect.addEventListener('change', (event) => {
  clearHeaderColors();
  switch(colorSelect.options[colorSelect.selectedIndex].value){
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
})

function inputToURL(inputElement){
  var file = inputElement.files[0];
  return window.URL.createObjectURL(file);
}

function clearHeaderColors(){
  headerBG.classList.remove('is-link');
  headerBG.classList.remove('is-info');
  headerBG.classList.remove('is-warning');
  headerBG.classList.remove('is-danger');
  headerBG.classList.remove('is-primary');
}


</script>
