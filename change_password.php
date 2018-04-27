<?php
include 'config.php';
if(empty($_SESSION['member']))
{
  header('Location: ../');
}
if(isset($_GET['logout']))
{
  session_destroy();
  header('Location: ../');
}

$q = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
$q->execute(array($_SESSION['member']['id']));
$member = $q->fetch(PDO::FETCH_ASSOC);

?><!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="shortcut icon" href="https://nerdpilots.com/wp-content/themes/NerdPilots/img/favicon.ico"/>

    <title>NerdPilots - Change Password</title>
    <style type="text/css">
      /* Sticky footer styles
      -------------------------------------------------- */
      html {
        position: relative;
        min-height: 100%;
      }
      body {
        margin-bottom: 60px; /* Margin bottom by footer height */
      }
      .footer {
        position: absolute;
        bottom: 0;
        width: 100%;
        height: 60px; /* Set the fixed height of the footer here */
        line-height: 60px; /* Vertically center the text there */
        background-color: #b54036;
        color: #FFF !important;
      }
      .btn
      {
        white-space:normal !important;
        word-wrap: break-word;
      }
    </style>


  </head>
  <body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">

          <ul class="navbar-nav mr-auto">
            <li class="nav-item">
              <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="change_password.php">Change Password <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="edit_billing_info.php">Edit Billing Info</a>
            </li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="index.php?logout">Logout</a>
            </li>
          </ul>

        </div>
    </nav>
    
    <div class="container" style="margin-top: 20px; max-width: 1400px;">
      <div class="float-left">
        <img src="img/logo.svg" style="width: 180px; height: 58.11px;" />
      </div>
      <div class="clearfix"></div>
      <div class="row">
        <div class="col-sm-12">
          <hr/>
        </div>
        <div class="col-md-6">
          <?php
          if(isset($_POST['cpassword']))
          {
            if($member['password'] == $_POST['cpassword'] && strlen($_POST['npassword'])>4)
            {
              if($_POST['npassword'] == $_POST['rnpassword'])
              {
                $q = $pdo->prepare('UPDATE `members` SET `password`=? WHERE `id`=?');
                $q->execute(array($_POST['npassword'], $_SESSION['member']['id']));
                echo '
                <div class="alert alert-success" role="alert" style="margin-top: 10px;">
                  Password changed successfully.
                </div>
                ';
              }
              else
              {
                echo '
                <div class="alert alert-danger" role="alert" style="margin-top: 10px;">
                  Unable to change your password, please try again.
                </div>
                ';
              }
            }
            else
            {
               echo '
               <div class="alert alert-danger" role="alert" style="margin-top: 10px;">
                 Password too short, must be longer than 4 characters.
               </div>
               ';
            }
          }
          ?>
          <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
              <h4 class="card-title">Change Password</h4>
              <form action="" method="post">
                  <label>Current Password</label>
                  <input type="password" class="form-control" name="cpassword" style="margin-bottom: 10px;">
                  <label>New Password</label>
                  <input type="password" class="form-control" name="npassword" style="margin-bottom: 10px;">
                  <label>Confirm New Password</label>
                  <input type="password" class="form-control" name="rnpassword" style="margin-bottom: 10px;">
                <button type="submit" class="btn btn-danger mr-2">Save</button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer">
      <div class="container">
        <span>Copyright NerdPilots 2018 ©</span>
      </div>
    </footer>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-2.2.4.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript">
      $('#creditsNum').change(function(){
        $('#cost').text($('#creditsNum').val()*29);
      });
      $(document).ready(function(){
      });
    </script>
  </body>
</html>