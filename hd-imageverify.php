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

$emptyCheck = 'SELECT * FROM websiteData';
$result = $mysqli->query($emptyCheck);
if ($obj = $result->fetch_object()){
}
else {
  $addIdent = 'INSERT INTO websiteData (identifier) VALUES ("ident")';
  if ($mysqli->query($addIdent) === FALSE){
    echo 'error adding ident';
    exit();
  }
}

$checkAdminKey = $mysqli->prepare('SELECT adminSessionKey FROM websiteData');
$checkAdminKey->execute();
$result = $checkAdminKey->get_result();
$checkAdminKey->close();

if($result->num_rows > 0){
  while($row = $result->fetch_assoc()){
    if (!password_verify($_POST['adminKey'], $row['adminSessionKey'])){
      echo '80:NOAPI';
      exit();
    }
  }
}

if ($_POST['set_header'] === '1'){
try {

    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['file']['error']) ||
        is_array($_FILES['file']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here.
    if ($_FILES['file']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // DO NOT TRUST $_FILES['upfile']['mime'] VALUE !!
    // Check MIME Type by yourself.
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    if (false === $ext = array_search(
        $finfo->file($_FILES['file']['tmp_name']),
        array(
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
        ),
        true
    )) {
        throw new RuntimeException('Invalid file format.');
    }

    // You should name it uniquely.
    // DO NOT USE $_FILES['upfile']['name'] WITHOUT ANY VALIDATION !!
    // On this example, obtain safe unique name from its binary data.
    if (!move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . '/uploads/' . $_FILES['file']['name']))
    {
        throw new RuntimeException('Failed to move uploaded file.');
    }

    echo '200:' . '/uploads/' . $_FILES['file']['name'];

    $saveToDatabase = $mysqli->prepare('UPDATE websiteData SET headerImage=?');
    $saveToDatabase->bind_param('s', $_FILES['file']['name']);
    $saveToDatabase->execute();
    $saveToDatabase->close();
} catch (RuntimeException $e) {

    echo $e->getMessage();

}
}
else if ($_POST['set_title'] === '1'){
  if (strlen($_POST['website_name']) > 50){
    echo '144: Too many characters.';
  }
  else {
    echo '200: OK';
  }
}

else if ($_POST['submit_form'] === '1'){
  $errors = '';
  if (strlen($_POST['username']) === 0){
    $errors = $errors . 'No username was submitted. ';
  }
  if (strlen($_POST['password']) === 0){
    $errors = $errors . 'No password was submitted. ';
  }
  if (strlen($_POST['websiteName']) === 0){
    $errors = $errors . 'No website name was set. ';
  }
  //Check if header image set
  if ($_POST['usingHeaderImage'] === '1'){
    $checkHeaderImage ='SELECT headerImage FROM websiteData';
    $result = $mysqli->query($checkHeaderImage);
    if ($result->num_rows > 0){
      while($row = $result->fetch_assoc()) {
        if (strlen($row['headerImage']) === 0){
        $errors = $errors . 'No header image was set, but the Use website logo in headers box was ticked. ';
        }
      }
    }
    else {
      $errors = $errors . 'No header image was set, but the Use website logo in headers box was ticked. ';
    }
  }
  if (strlen($errors) === 0){
    $stmt = $mysqli->prepare('UPDATE websiteData SET websiteName=?, headerImageUsed=?, adminUsername=?, adminPassword=?, websiteColor=?');
    $securePassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $stmt->bind_param('sssss', $_POST['websiteName'], $_POST['usingHeaderImage'], $_POST['username'], $securePassword, $_POST['site_color']);
    if ($stmt->execute() !== FALSE){
      echo "200:OK";

    }
    else {
      echo 'Database error.';
    }
  }
  else {
    echo $errors;
  }
}
else if ($_POST['committeeSubmit'] === '1'){
  $stmt = $mysqli->prepare('SELECT * FROM committees WHERE committeeName=?');
  $stmt->bind_param("s", $_POST['committee']);
  $stmt->execute();
  $result = $stmt->get_result();
  $stmt->close();

  if ($result->num_rows > 0){
    echo '80:ERROR';
  }
  else {
    echo '200:OK';
  }
}
else if ($_POST['update-person'] === '1'){
  $stmt = $mysqli->prepare('UPDATE people SET color=? WHERE identifier=?');
  $stmt->bind_param('ss', $_POST['color'], $_POST['person-name']);
  $stmt->execute();
  $stmt->close();
  echo '200:OK';
}

?>
