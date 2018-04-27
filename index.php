<?php
include 'config.php';

if(isset($_GET['save_input']))
{
	setcookie('description', $_POST['description']);
}

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

    <title>NerdPilots - Customer Portal</title>
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
            <li class="nav-item active">
              <a class="nav-link" href="index.php">Home <span class="sr-only">(current)</span></a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="change_password.php">Change Password</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="edit_billing_info.php">Edit Billing Info</a>
            </li>
            <?php
            if(isset($_SESSION['developer']))
            {
            	echo '
            	<li class="nav-item">
            	  <a class="nav-link" href="developer.php">Developer Dashboard</a>
            	</li>
            	';
            }
            ?>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="index.php?logout">Logout</a>
            </li>
          </ul>

        </div>
    </nav>
    
    <div class="container" style="margin-top: 20px; max-width: 1400px;">

      <?php
      if(isset($_POST['credits']))
      {
        $stripe = json_decode($member['stripe'], 1);
        $amount = $_POST['credits']*29;
        $charge = \Stripe\Charge::create(array(
            "amount" => $amount.'00',
            "currency" => "usd",
            "customer" => $stripe['customer']
        ));
        if($charge->status == 'succeeded')
        {
          $q = $pdo->prepare('UPDATE `members` SET `credits`=`credits`+? WHERE `id`=?');
          $q->execute(array($_POST['credits'], $_SESSION['member']['id']));

          $q = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
          $q->execute(array($_SESSION['member']['id']));
          $member = $q->fetch(PDO::FETCH_ASSOC);

          echo '
          <div class="alert alert-success" role="alert" style="margin-top: 10px;">
            '.$_POST['credits'].' credit';
            if($_POST['credits']>1) { echo 's'; }
            echo ' was added to your account. 
          </div>
          ';
        }
        else
        {
          echo '
          <div class="alert alert-warning" role="alert" style="margin-top: 10px;">
            We we\'re unable to charge your credit card.
          </div>
          ';
        }
      }
      ?>

      <div class="float-left">
        <img src="img/logo.svg" style="width: 180px; height: 58.11px;" />
      </div>
      <div class="float-right">
        <h4>Welcome <?php echo explode(' ', $member['name'])[0].' '.substr(explode(' ', $member['name'])[1], 0, 1); ?>.<br/>
          <?php

          if($member['credits']>0)
          {
          	?>
          	<em>You have <span class="badge badge-danger"><?php echo $member['credits']; ?></span> credit<?php
          	  if($member['credits']>1)
          	  {
          	    echo 's';
          	  }
          	  ?></em>
          	<?php
          }
          else
          {
          	echo '<em class="text-muted">You have no credits left for this month.
          	<br/>You need credits to start a new task.</em>';
          }
          ?></h4>
        <button type="button" class="btn btn-sm btn-secondary" data-toggle="modal" data-target="#getCredits">GET MORE!</button>
      </div>
      <div class="clearfix"></div>

      <div class="row" style="margin-top: 20px;">
        <div class="col-sm-12">
          <?php
          $active_task = $pdo->prepare('SELECT * FROM `tasks` WHERE `status`<>\'completed\' AND `by_member`=?');
          $active_task->execute(array($_SESSION['member']['id']));

          if($member['credits']>0 && $member['plan'] !== 2)
          {
            echo '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#newTask">NEW TASK</button>';
          }

          if($member['plan'] == 2)
          {
            if($active_task->rowcount()<1)
            {
              echo '<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#newTask">NEW TASK</button>';
            }
          }

          ?>
          <hr/>

          <?php
          if(isset($_POST['title']))
          {

          	$files = '<hr/><span class="text-success">Attachments</span><br/>';
          	foreach ($_FILES['files']['tmp_name'] as $key => $value) {
          	  if(!empty($_FILES['files']['name'][$key]))
              {
              $filename = md5(rand(000000,999999)).'.'.pathinfo($_FILES['files']['name'][$key], PATHINFO_EXTENSION);
              move_uploaded_file($_FILES['files']['tmp_name'][$key], 'uploads/'.$filename);
              $files .= '<a href="uploads/'.$filename.'" target="_blank" class="btn btn-primary" style="margin: 5px;">'.$_FILES['files']['name'][$key].'</a>';
              }
          	}
          	if(!strpos($files, 'uploads/'))
          	{
          	  $files = '';
          	}
          	
          	$_POST['description'] = convert_input($_POST['description']).$files;

            $q = $pdo->prepare('INSERT INTO `tasks` VALUES (?,?,?,?,?)');
            $q->execute(array($_POST['title'], $_POST['description'], 'active', $_SESSION['member']['id'], NULL));
            echo '
            <div class="alert alert-success" role="alert" style="margin-top: 10px;">
              Task: ('.$_POST['title'].') has been added to the queue, we will send an update shortly!
            </div>
            ';

            $headers = 'From: '.$member['email']."\r\n" .
                'Reply-To: '.$member['email']."\r\n" .
                'X-Mailer: PHP/' . phpversion();

            define('MAILGUN_URL', 'https://api.mailgun.net/v3/mx.nerdpilots.com');
            define('MAILGUN_KEY', 'key-4e7702dff68faac2bac4d5fbd1602173');

            $array_data = array(
                    'from'=> $member['email'],
                    'to'=>'<hello@nerdpilots.com>',
                    'subject'=>'A new task was posted on NerdPilots!',
                    'text'=>$member['name'].' posted a new task on NerdPilots: '.$_POST['title'],
                    'h:Reply-To'=>'hello@nerdpilots.com'
                );
                $session = curl_init(MAILGUN_URL.'/messages');
                curl_setopt($session, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($session, CURLOPT_USERPWD, 'api:'.MAILGUN_KEY);
                curl_setopt($session, CURLOPT_POST, true);
                curl_setopt($session, CURLOPT_POSTFIELDS, $array_data);
                curl_setopt($session, CURLOPT_HEADER, false);
                curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
                curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
                $response = curl_exec($session);
                curl_close($session);
                $results = json_decode($response, true);

          }
          if(isset($_GET['cancel']))
          {
            $q = $pdo->prepare('DELETE FROM `tasks` WHERE `id`=?');
            $q->execute(array($_GET['cancel']));
          }
          ?>
        </div>

        <div class="col-md-6">
          <?php
          $q = $pdo->prepare('SELECT * FROM `tasks` WHERE `by_member`=? AND `status`=? || `by_member`=? AND `status`=? ORDER BY `id` DESC');
          $q->execute(array($_SESSION['member']['id'], 'active', $_SESSION['member']['id'], 'in-progress'));
          ?>
          <h4>Active Tasks <span class="badge badge-success"><?php echo $q->rowcount(); ?></span></h4>
          <div class="card" style="height: 50vh; overflow-y: scroll;">
            <div class="card-body">
              
              <?php
              foreach ($q as $row) {

                $updated = $pdo->prepare('SELECT * FROM `task_replies` WHERE `task_id`=? AND `time`>? ORDER BY `time` DESC LIMIT 1');
                $updated->execute(array($row['id'], time()-900));

                if($updated->rowcount()>0)
                {
                  $updated = $updated->fetch(PDO::FETCH_ASSOC);
                  echo '
                  <div class="dropdown" style="margin-bottom: 10px;">
                    <button class="btn btn-primary btn-block text-left dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      '.$row['title'].' ';
                      if($row['status'] == 'in-progress')
                      {
                      	echo '<span class="badge badge-primary">In-Progress</span> ';
                      }
                      echo ' <span class="badge badge-secondary">Updated '.time_elapsed_string('@'.$updated['time']).'</span>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                      <a class="dropdown-item" href="task.php?id='.$row['id'].'">View Task</a>
                      <a class="dropdown-item" href="index.php?cancel='.$row['id'].'" onclick="return confirm(\'Cancel '.$row['title'].'?\');">Cancel Task</a>
                    </div>
                  </div>
                  ';
                }
                else
                {
                  echo '
                  <div class="dropdown" style="margin-bottom: 10px;">
                    <button class="btn btn-secondary btn-block text-left dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      '.$row['title'].' ';
                      if($row['status'] == 'in-progress')
                      {
                      	echo '<span class="badge badge-primary">In-Progress</span> ';
                      }
                      echo '
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                      <a class="dropdown-item" href="task.php?id='.$row['id'].'">View Task</a>
                      <a class="dropdown-item" href="index.php?cancel='.$row['id'].'" onclick="return confirm(\'Cancel '.$row['title'].'?\');">Cancel Task</a>
                    </div>
                  </div>
                  ';
                }
                
              }
              ?>

            </div>
          </div>
        </div>

        <div class="col-md-6">
          <?php
          $q = $pdo->prepare('SELECT * FROM `tasks` WHERE `by_member`=? AND `status`=? ORDER BY `id` DESC');
          $q->execute(array($_SESSION['member']['id'], 'completed'));
          ?>
          <h4>Completed Tasks <span class="badge badge-secondary"><?php echo $q->rowcount(); ?></span></h4>
          <div class="card" style="height: 50vh; overflow-y: scroll;">
            <div class="card-body">
              
              <?php
              foreach ($q as $row) {
                echo '
                <div class="dropdown" style="margin-bottom: 10px;">
                  <button class="btn btn-default btn-block text-left dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    '.$row['title'].'
                  </button>
                  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item" href="task.php?id='.$row['id'].'">View Task</a>
                    <a class="dropdown-item" href="task.php?id='.$row['id'].'&reopen=1">Reopen Task</a>
                  </div>
                </div>
                ';
              }
              ?>

            </div>
          </div>
        </div>

      </div>

      <div class="row" style="margin-top: 40px;">
        <div class="col-sm-12">
          <p>Click on "View Task" to view updates and activity on a task. You will also be notified via email at <strong><?php echo $member['email']; ?></strong> for everything.</p>
        </div>
      </div>

    </div>

    <footer class="footer">
      <div class="container">
        <span>Copyright NerdPilots 2018 ©</span>
      </div>
    </footer>

    <!-- Modal -->
    <div class="modal fade" id="getCredits" tabindex="-1" role="dialog" aria-labelledby="getCreditsLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="getCreditsLabel">Purchase Credits</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="post">
              <label>How many credits do you want to purchase?</label>
              <input type="number" name="credits" class="form-control" style="max-width: 20%;" value="1" id="creditsNum">
              <hr/>
              <p>Total $<span id="cost">29</span></p>
              <button type="submit" class="btn btn-danger">Purchase</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="newTask" tabindex="-1" role="dialog" aria-labelledby="newTaskLabel" aria-hidden="true" style="margin-top: 5vh;">
      <div class="modal-dialog modal-lg" role="document" style="max-width: 90%;">
        <div class="modal-content" style="min-height: 85vh;">
          <div class="modal-header">
            <h5 class="modal-title" id="newTaskLabel">Submit a new task</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="post" enctype="multipart/form-data">
              
              <div class="row">
                <div class="col-md-8">
                  <label>Title</label>
                  <input type="text" name="title" class="form-control" placeholder="Example: Install a new theme on my wordpress blog." style="margin-bottom: 10px;" required>
                  <label>Description</label>
                  <textarea name="description" class="form-control" style="margin-bottom: 20px; height: 30vh;"><?php echo @$_COOKIE['description']; ?></textarea>
                </div>
                <div class="col-md-4">
                  <p><strong>Important</strong> Please be very specific and make sure to include all the necessary information.</p>
                  Examples include
                  <ul>
                    <li>
                      Your wordpress login
                    </li>
                    <li>
                      Your server login or FTP details
                    </li>
                    <li>
                      For pointing domains, include the login for the registrar (Godaddy, Namecheap, Enom etc..)
                    </li>
                  </ul>
                </div>
                <div class="col-sm-12">
                  <label>Attach</label>
                  <input type="file" name="files[]" class="btn btn-default btn-sm" style="margin-bottom: 10px; display: block; max-width: 500px;" multiple>
                  <button type="submit" class="btn btn-danger btn-lg">Submit Task</button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>

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

        <?php
        if(isset($_POST['title']))
        {
          echo '$(\'[name="description"]\').val(\'\');';
        }
        ?>

        setInterval(function(){
        	$.post('index.php?save_input=1', { description: $('[name="description"]').val() });
        }, 1000);

      });
    </script>
  </body>
</html>