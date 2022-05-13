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

$sanitycheck = $mysqli->prepare('SELECT * FROM posts WHERE identifier=?');
$sanitycheck->bind_param("s", $_GET['id']);
$sanitycheck->execute();
$sanResult = $sanitycheck->get_result();
$sanitycheck->close();

if ($sanResult->num_rows === 0){
  header('Location:index.php');
}

//Add all users from file
$getfilename = $mysqli->prepare('SELECT fileName FROM posts WHERE identifier=?');
$getfilename->bind_param('s', $_GET['id']);
$getfilename->execute();
$fileRes = $getfilename->get_result();
$getfilename->close();

if ($fileRes->num_rows > 0){
  while ($row = $fileRes->fetch_assoc()){
    $filecontents = file_get_contents('./debates/' . $row['file']);
    $messages = explode('/', $filecontents);
    foreach ($messages as $message){
      $messageData = explode(';', $message);
      $checkUserExists = $mysqli->prepare('SELECT * FROM people WHERE identifier=?');
      $checkUserExists->bind_param('s', $messageData[0]);
      $checkUserExists->execute();
      $checkUserRes = $checkUserExists->get_result();
      $checkUserExists->close();

      if ($checkUserRes->num_rows === 0){
        //No user by that name exists yet
        $addUser = $mysqli->prepare('INSERT INTO people (identifier) VALUES (?)');
        $addUser->bind_param('s', $messageData[0]);
        $addUser->execute();
        $addUser->close();
      }
    }
  }
}
else {
  //SOmething went wrong
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
    <link rel="stylesheet" href="./style.css" />
    <script src="https://kit.fontawesome.com/f2375e8543.js" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
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

  <h3 class="pl-6 pr-6 pt-4 title">Process discussion</h3>
  <h3 class="pl-6 pr-6 pt-2 mb-2 subtitle">Overview</h3>
  <div class="control pl-6 pr-6 mb-2">
      <button id="messagesModal" class="button is-info">Review messages</button>
    </div>
  <div class="field pl-6 pr-6">
  <label class="label">Name of discussion (e.g.: 'Debate on election regulations bill')</label>
  <div class="control">
    <input id="discussionName" class="input" type="text" placeholder="Debate name" value="<?php
    $checkForDebName = $mysqli->prepare('SELECT debateName FROM debates WHERE identifier=?');
    $checkForDebName->bind_param("s", $_GET['id']);
    $checkForDebName->execute();
    $debNameResult = $checkForDebName->get_result();
    $checkForDebName->close();


    if ($debNameResult->num_rows > 0){
      while ($row=$debNameResult->fetch_assoc()){
        echo $row['debateName'];
        break;
      }
    }
    ?>">
  </div>
</div><div id="committeeHolder" class="field pl-6 pr-6 pt-2">
<label class="label">Committee (e.g. 'Committee of the Whole House')</label>
<div class="control ">
  <input id="committee" class="input" type="text" placeholder="Committee" value="">
</div>
<p id="validComm" style="display:none" class="help is-success">This committee name is valid</p>
<p id="invalidComm" style="display: none" class="help is-danger">This committee name is invalid</p>
</div>
<div id="existingCommittee" style="display: none" class="field pl-6 pr-6">
  <label class="label">Existing committee</label>
<div class="select">
  <select id="committeeSelection">
    <option>Select committee</option>
    <?php

    $committeeGet = $mysqli->prepare('SELECT * FROM committees');
    $committeeGet->execute();
    $result = $committeeGet->get_result();
    $committeeGet->close();

    if ($result->num_rows > 0){
      while ($row = $result->fetch_assoc()){
        echo '<option value="' . $row['committeeName'] . '">
        ' . $row['committeeName'] . '</option>';
      }
    }

    ?>
  </select>
</div>
</div>
<div class="field pl-6 pr-6">
<label class="checkbox">
  <input id="existingComm" type="checkbox">
  Use existing committee
</label>
</div>
<h3 class="pl-6 pr-6 pt-2 mb-2 subtitle">Speaker information</h3>
<div class="pl-6 pr-6">
  <div class="card p-3">
    <?php
    $ident = $_GET['id'];
    $getCurrentFile = $mysqli->prepare('SELECT * FROM posts WHERE identifier=?');
    $getCurrentFile->bind_param("s", $ident);
    $getCurrentFile->execute();
    $result = $getCurrentFile->get_result();
    $getCurrentFile->close();
    $fileName = '';
    if ($result->num_rows > 0){
      while($row = $result->fetch_assoc()){
        $fileName = $row['fileName'];
        $rawVotes = json_decode($row['votes'], true);
        break;
      }
    }
    else {
      header('Location:index.php');
    }
    $fileContents = file_get_contents('./debates/' . $fileName);
    $messages = explode('/', $fileContents);
    $speakers = array();
    foreach ($messages as $message){
      $messageData = explode(';', $message);
      if (!in_array($messageData[2], $speakers) && strlen($messageData[2]) > 0 && $messageData[0] === 'vote'){
        echo '<div>
        <h3 class="subtitle mb-2">' . $messageData[2] . '</h3>
        <div class="field">
          <label class="label">Name (leave blank to use Discord handle)</label>
            <div class="control">
              <input class="input nameInput" data-user=' . $messageData[2] . ' type="text" placeholder="Name">
              </div>
            </div>
            <div class="field">
              <label class="label">Role (e.g. President, Chair, etc.)</label>
                <div class="control">
                  <input class="input roleInput" data-role=' . $messageData[2] . ' type="text" placeholder="Role">
                  </div>
                </div>
                <label class="checkbox">
                  <input class="colorCheckbox" data-target=' . $messageData[2] . ' type="checkbox">
                  Display party colour
                </label>
                <div class="field colorDisplay" style="display: none" data-targetee=' . $messageData[2] . '>
                <label class="label">Party colour</label>
                <div class="control">
                  <input class="color colorSetter" data-targetee=' . $messageData[2] . ' type="color" />
                </div>
                </div>
        </div>
        <hr/>';
        array_push($speakers, $messageData[2]);
      }
      if (!in_array($messageData[0], $speakers) && strlen($messageData[1] > 0) && $messageData[0] !== 'vote'){
      //[0]: Sender; [1]: Message; [2]: Sent on
      echo '<div>
      <h3 class="subtitle mb-2">' . $messageData[0] . '</h3>
      <div class="field">
        <label class="label">Name (leave blank to use Discord handle)</label>
          <div class="control">
            <input class="input nameInput" data-user=' . $messageData[0] . ' type="text" placeholder="Name">
            </div>
          </div>
          <div class="field">
            <label class="label">Role (e.g. President, Chair, etc.)</label>
              <div class="control">
                <input class="input roleInput" data-role=' . $messageData[0] . ' type="text" placeholder="Role">
                </div>
              </div>
              <label class="checkbox">
                <input class="colorCheckbox" data-target=' . $messageData[0] . ' type="checkbox">
                Display party colour
              </label>
              <div class="field colorDisplay" style="display: none" data-targetee=' . $messageData[0] . '>
              <label class="label">Party colour</label>
              <div class="control">
                <input class="color colorSetter" data-targetee=' . $messageData[0] . ' type="color" />
              </div>
              </div>
      </div>
      <hr/>';
      array_push($speakers, $messageData[0]);
      }
    }
    if (count($speakers) === 0){
      echo '<div>
      <h4>No speakers found in this upload.</h4>
      </div>';
    }
    ?>
  </div>
</div>
<div class="control pl-6 pr-6 pt-2"><div class="buttons">
  <button id="submitButton" class="button is-primary">Submit</button>
  <button id="deleteButton" class="button is-danger">Delete Record</button>
</div>
  </div>

  <div id="reviewMessages" class="modal">
    <div class="modal-background"></div>

    <div class="modal-content">
      <div class="box">
        <h3 class="subtitle">Message review</h3>
        <?php

        $ident = $_GET['id'];
        $getCurrentFile = $mysqli->prepare('SELECT * FROM posts WHERE identifier=?');
        $getCurrentFile->bind_param("s", $ident);
        $getCurrentFile->execute();
        $result = $getCurrentFile->get_result();
        $getCurrentFile->close();
        $fileName = '';
        $votes = '';
        if ($result->num_rows > 0){
          while($row = $result->fetch_assoc()){
            $fileName = $row['fileName'];
            $votes = $row['votes'];
            break;
          }
        }
        $voteContents = json_decode($votes, true);
        $fileContents = file_get_contents('./debates/' . $fileName);
        $messages = explode('/', $fileContents);
        $speakers = array();
        foreach ($messages as $message){
          $messageData = explode(';', $message);
          $messageDeEscaped = str_replace('#esc#', ';', $messageData[1]);
          $messageDeEscaped = str_replace('#efs#', '/', $messageDeEscaped);
          if ($messageData[0] === "vote"){

            echo '<article class="media">
            <figure class="media-left">
            </figure>
            <div class="media-content">
            <div class="content">
            <p>
            <strong>A vote was called on the subject of ' . $messageData[1] . ' by ' . $messageData[2] . '</strong><br />
            <canvas id="chart' . $messageData[3] . '" width="400" height="400"></canvas>
            <script>
            const ctx' . $messageData[3] . ' = document.getElementById("chart' . $messageData[3] . '");
            const myChart' . $messageData[3] . ' = new Chart(ctx' . $messageData[3] . ', {
              type: "bar",
              data: {
                labels: ["Yes", "No", "Abstain"],
                datasets: [{
                  label: "Votes",
                  data: [' . count($voteContents[$messageData[3]]["forVotes"]) . ', ' . count($voteContents[$messageData[3]]["againstVotes"]) . ', ' . count($voteContents[$messageData[3]]["abstentions"]) . '],
                  backgroundColor: [
                    "rgba(0, 255, 0, 0.2)",
                    "rgba(255, 0, 0, 0.2)",
                    "rgba(0, 0, 255, 0.2)"
                  ]
                }]
              },
              options: {
                indexAxis: "y",
                scales: {
                  y: {
                beginAtZero: true
              }
            }
            }
            });
            </script>
            <br>
            </p>
            </div>
            </div>
            </article>';
          }
          else {
          echo '<article class="media">
  <figure class="media-left">
  </figure>
  <div class="media-content">
    <div class="content">
      <p>
        <strong>' . $messageData[0] . '</strong> <small>' . $messageData[2] . '</small>
        <br>
        ' . $messageDeEscaped . '
      </p>
    </div>
  </div>
</article>';
        }
      }

        ?>
      </div>
    </div>

    <button id="closeModal" class="modal-close is-large" aria-label="close"></button>
  </div>
</body>

<script>

var headerBG = document.getElementById('headerBG');
var gowparlLogo = document.getElementById('gowparlLogo');
var logoSection = document.getElementById('logoSection');
var titleSection = document.getElementById('titleSection');

var newCommitteeInput = document.getElementById('committee');
var newCommittee = document.getElementById('committeeHolder');
var existingCommitteePanel = document.getElementById('existingCommittee');
var existingCommitteeCheck = document.getElementById('existingComm');

var modalButton = document.getElementById('messagesModal');
var closeModal = document.getElementById('closeModal');

var submitButton = document.getElementById('submitButton');
var deleteButton = document.getElementById('deleteButton');

submitButton.addEventListener('click', function(){
  var form_data = new FormData();
  form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>');
  if (existingCommitteeCheck.checked === true){
    if (document.getElementById('committeeSelection').selectedIndex === 0){
      alert('You need to choose an existing committee.');
      return;
    }
    form_data.append('committee', document.getElementById('committeeSelection').options[document.getElementById('committeeSelection').selectedIndex].value);
    form_data.append('newCommittee', '0');
  }

  else {
    form_data.append('newCommittee', '1');
    form_data.append('committee', committee.value);
  }
  form_data.append('debateName', document.getElementById('discussionName').value);
  form_data.append('identifier', '<?php echo $_GET['id'] ?>')
  form_data.append('file', '<?php

  $getFile = $mysqli->prepare("SELECT fileName FROM posts WHERE identifier=?");
  $getFile->bind_param("s", $_GET["id"]);
  $getFile->execute();
  $result = $getFile->get_result();
  $getFile->close();

  if ($result->num_rows > 0){
    while ($row = $result->fetch_assoc()){
      echo $row["fileName"];
      break;
    }
  }
  else {
    echo 'error';
  }

  ?>');
  var peopleData = '[';
  $('.nameInput').each(function(){
    var currentInput = this;
    var data = '{ "rawName": "' + currentInput.dataset.user + '", "parsedName": "' + currentInput.value + '"},';
    peopleData = peopleData + data;
  })
  peopleData = peopleData.slice(0, -1);
  peopleData = peopleData + ']';
  var roleData = '[';
  $('.roleInput').each(function() {
    var currentInput = this;
    var data = '{ "rawName": "' + currentInput.dataset.role + '", "parsedRole": "' + currentInput.value + '"},';
    roleData = roleData + data;
  })
  roleData = roleData.slice(0, -1);
  roleData = roleData + ']';
  var colorData = '[';
  $('.colorCheckbox').each(function() {
    var currentDisplay = this;
    $('.colorSetter').each(function (){
      var currentSetter = this;
      if (currentSetter.dataset.targetee === currentDisplay.dataset.target){
        //This is the One
        var data = '{ "rawName": "' + currentDisplay.dataset.target + '", "useColor": "' + currentDisplay.checked + '", "color": "' + currentSetter.value +'"},';
        colorData = colorData + data;
        return false;
      }
    })
  });
  colorData = colorData.slice(0, -1);
  colorData = colorData + ']';
  form_data.append('people', peopleData);
  form_data.append('roles', roleData);
  form_data.append('colors', colorData);
  $.ajax({
    url: './hd-submitdebate.php',
    dataType: 'text',
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    type: 'post',
    success: function(php_script_response){
      if (php_script_response.startsWith('200')){
        //It worked! Transfer
        window.location.href='/gowparl/hd-debate.php?id=' + '<?php echo $_GET['id'] ?>'
      }
      else {
        const response = php_script_response.split(':');
        if (response[1] === 'TOOLONG'){
          alert('Error: One of the elements was longer than 100 characters.');
        }
        else if (response[1] === 'TOOSHORT'){
          alert('Error: One of the elements had no characters.');
        }
        else if (response[1] === "TAKEN"){
          alert('Error: That committee name is taken.');
        }
      }
    }
  })
})

deleteButton.addEventListener('click', function() {
  if (confirm('This will also delete any debate connected to this record. Are you sure?')){
    var form_data = new FormData();
    form_data.append('adminKey', "<?php echo $_SESSION['adminKey'] ?>");
    form_data.append('identifier', "<?php echo $_GET['id'] ?>");
    $.ajax({
      url: './hd-deleterecord.php',
      dataType: 'text',
      cache: false,
      contentType: false,
      processData: false,
      data: form_data,
      type: 'post',
      success: function(php_script_response){
        if (php_script_response === '200:OK'){
          window.location.href = './index.php';
        }
        else if (php_script_response === '80:NOAPI'){
          alert('You do not have the authority to perform this function. Please try again later.');
        }
      }
    })
  }
});

$('.colorSetter').each(function (){
  var setter = this;
  setter.addEventListener('change', function(){
    var form_data = new FormData();
    form_data.append('update-person', '1');
    form_data.append('person-name', setter.dataset.targetee);
    form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>');
    form_data.append('color', setter.value);
    $.ajax({
      url: './hd-imageverify.php',
      dataType: 'text',
      cache: false,
      contentType: false,
      processData: false,
      data: form_data,
      type: 'post',
      success: function(php_script_response){
        console.log(php_script_response);
      }
    })
  })
})

$(".colorCheckbox").each(function () {
  var checkbox = this;
  checkbox.addEventListener('change', function() {
    if (checkbox.checked){
      $(".colorDisplay").each(function () {
        var displayer = this;
        if (displayer.dataset.targetee === checkbox.dataset.target){
          displayer.style.display = "block";
          var form_data = new FormData();
          form_data.append('update-person', '1');
          form_data.append('person-name', checkbox.dataset.target);
          $('.colorSetter').each(function () {
            var setter = this;
            if (setter.dataset.targetee = checkbox.dataset.target){
              form_data.append('color', setter.value);
              form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>')
              return false;
            }
          })
          $.ajax({
            url: './hd-imageverify.php',
            dataType: 'text',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(php_script_response){
              console.log(php_script_response);
            }
          })
          return false;
        }
      })
    }
    else {
      $('.colorDisplay').each(function () {
        var displayer = this;
        if (displayer.dataset.targetee === checkbox.dataset.target){
          displayer.style.display = "none";
          var form_data = new FormData();
          form_data.append('update-person', '1');
          form_data.append('person-name', checkbox.dataset.target);
          form_data.append('color', 'none');
          form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>')
          $.ajax({
            url: './hd-imageverify.php',
            dataType: 'text',
            cache: false,
            contentType: false,
            processData: false,
            data: form_data,
            type: 'post',
            success: function(php_script_response){
              console.log(php_script_response);
            }
          })
          return false;
        }
      })
    }
  })
})

modalButton.addEventListener('click', function() {
  document.getElementById('reviewMessages').classList.add('is-active');
})

closeModal.addEventListener('click', function(){
    document.getElementById('reviewMessages').classList.remove('is-active');
})

existingCommitteeCheck.addEventListener('change', function () {
  if (existingCommitteeCheck.checked) {
    existingCommitteePanel.style.display = "block";
    newCommittee.style.display = "none";
  }
  else {
    existingCommitteePanel.style.display = "none";
    newCommittee.style.display = "block";
  }
})

newCommitteeInput.addEventListener('change', function () {
  var form_data = new FormData();
  form_data.append('committee', newCommitteeInput.value);
  form_data.append('adminKey', '<?php echo $_SESSION['adminKey'] ?>')
  form_data.append('committeeSubmit', '1');
  if (newCommitteeInput.value.length > 100){
      document.getElementById('validComm').style.display = 'none';
      document.getElementById('invalidComm').style.display = 'block';
      document.getElementById('invalidComm').innerHTML = 'This committee name is too long. Committee titles cannot be longer than 100 characters.';
      newCommitteeInput.classList.add('is-danger');
      newCommitteeInput.classList.remove('is-success');
      return;
  }
  $.ajax({
    url: './hd-imageverify.php',
    dataType: 'text',
    cache: false,
    contentType: false,
    processData: false,
    data: form_data,
    type: 'post',
    success: function(php_script_response){
      if (php_script_response.startsWith('200')){
        document.getElementById('validComm').style.display = 'block';
        document.getElementById('invalidComm').style.display = 'none';
        newCommitteeInput.classList.add('is-success');
        newCommitteeInput.classList.remove('is-danger');
      }
      else {
        document.getElementById('validComm').style.display = 'none';
        document.getElementById('invalidComm').style.display = 'block';
        document.getElementById('invalidComm').innerHTML = 'This committee name is taken.';
        newCommitteeInput.classList.add('is-danger');
        newCommitteeInput.classList.remove('is-success');
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
