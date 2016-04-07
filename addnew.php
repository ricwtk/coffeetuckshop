<?php
  $mysqli = new mysqli("localhost", "root", "root", "epgcoffee");

  if (isset($_POST['name'])) {
    if ($_POST['name'] == '') {
      header("location:addnew.php?msg=name cannnot be empty");
      die();
    }
    if ($_POST['displayname'] == '') {
      header("location:addnew.php?msg=display name cannot be empty");
      die();
    }
    $res = $mysqli->query("select name from namelist where name = '" . $_POST['name'] . "'");
    $rows = $res->fetch_assoc();
    if ($res->num_rows > 0) {
      header("location:addnew.php?msg='" . $_POST['name'] . "' is taken");
      die();
    }
    # insert name to namelist
    $mysqli->query("insert into namelist (name, displayname) values ('" . $_POST['name'] . "', '" . $_POST['displayname'] . "')");
    # insert name to cashleft
    $mysqli->query("insert into cashleft (name, cashleft) values ('" . $_POST['name'] . "', 0)");
    # insert name into consumed
    $mysqli->query("insert into consumed (name, date, used) values ('" . $_POST['name'] . "', current_date, 0)");
    # refresh page to prevent resending
    header("Location: addnew.php?msg='" . $_POST['name'] . "' saved to list");
  }
  
?>
<!DOCTYPE html>
<html>
<head>
  <meta name="robots" content="noindex,nofollow">
  <meta name=viewport content="width=device-width, initial-scale=1">
  <title>EPG Begbroke Coffee Payment Update</title>
  <link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
  <link rel="stylesheet" type="text/css" href="style.css">
  <script type="text/javascript">
    function closeWindow() {
      window.open('','_parent','');
      window.close();
    }
  </script>
</head>

<body>
  <div class='header'>EPG Coffee Tuck Shop (Add new)</div>

  <div class='content center'>
    <div>
    <?php if (isset($_GET['msg'])) { echo $_GET['msg']; } ?>
    </div>
    <div class='addnew left'>
      <form enctype='multipart/form-data' method='post'>
        <div>
          <div class='label'>Name:</div> 
          <input type='text' id='name' name='name'>
        </div>
        <div>
          <div class='label'>Display Name:</div> 
          <input type='text' id='displayname' name='displayname'>
          <input type='submit' id='submitnew' hidden>
          <label for='submitnew' class='submitnew button'>Add</label>
        </div>
      </form>
    </div>
  </div>
</body>
</html>
