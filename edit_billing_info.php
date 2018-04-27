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

    <title>NerdPilots - Edit Billing Info</title>
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
            <li class="nav-item">
              <a class="nav-link" href="change_password.php">Change Password</a>
            </li>
            <li class="nav-item active">
              <a class="nav-link" href="edit_billing_info.php">Edit Billing Info <span class="sr-only">(current)</span></a>
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
        <div class="col-md-8">
          <?php
          if(isset($_POST['name']))
          {

            $stripe = json_decode($member['stripe'], 1);

            try {
                $token = \Stripe\Token::create(array(
                  "card" => array(
                    "number" => $_POST['number'],
                    "exp_month" => $_POST['exp_month'],
                    "exp_year" => $_POST['exp_year'],
                    "cvc" => $_POST['cvv']
                  )
                ));
            } catch (Exception $e) {
              $invalid = 'Unable to verify this Credit Card, please try again.';
            }

            if(isset($token))
            {
              try {
                  $cu = \Stripe\Customer::retrieve($stripe['customer']); // stored in your application
                  $cu->source = $token->id; // obtained with Checkout
                  $cu->save();

                  $success = "Your card details have been updated!";
                }
                catch(\Stripe\Error\Card $e) {

                  // Use the variable $error to save any errors
                  // To be displayed to the customer later in the page
                  $body = $e->getJsonBody();
                  $err  = $body['error'];
                  $error = $err['message'];
                }

                if(isset($invalid))
                {
                  echo '
                  <div class="alert alert-danger" role="alert" style="margin-top: 10px;">
                    '.$invalid.'
                  </div>
                  ';
                }

                if(isset($success))
                {
                  $stripe['token'] = $token->id;
                  $q = $pdo->prepare('UPDATE `members` SET `stripe`=? WHERE `id`=?');
                  $q->execute(array(json_encode($stripe), $_SESSION['member']['id']));
                  echo '
                  <div class="alert alert-success" role="alert" style="margin-top: 10px;">
                    '.$success.'
                  </div>
                  ';
                }

                if(isset($error))
                {
                  echo '
                  <div class="alert alert-danger" role="alert" style="margin-top: 10px;">
                    '.$error.'
                  </div>
                  ';
                }
            }

          }
          ?>
          <div class="card" style="margin-bottom: 20px;">
            <div class="card-body">
              <h4 class="card-title">Edit Billing Info</h4>
              <form action="" method="post">

              <div class="row">
                <div class="col-sm-12">
                  <label>Name on Card</label>
                  <input type="text" class="form-control" name="name" placeholder="John Doe" id="billing_name" style="margin-bottom: 10px;">
                </div>
                <div class="col-md-4">
                    <label>Card Number</label>
                    <input type="text" class="form-control" name="number" placeholder="Example: 4242424242424242" style="margin-bottom: 10px;">
                </div>
                <div class="col-md-4">
                    <label>Expiration (MM/YY)</label>
                    <table>
                        <tr>
                            <td>
                                <input type="text" class="form-control" name="exp_month" placeholder="MM">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="exp_year" placeholder="YY">
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-4">
                    <label>Security Code (CVV)</label>
                    <input type="text" class="form-control" name="cvv" placeholder="Example: 544">
                </div>
                <div class="col-sm-12">
                  <br/><br/>
                  <button class="btn btn-lg btn-danger" name="sign_up_btn">Update Billing Info</button>
                </div>
              </div>

              </form>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <img src="img/cc.png" style="max-width: 100%;" />
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