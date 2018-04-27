<?php
include 'config.php';
if(empty($_SESSION['member']))
{
  header('Location: ../?showlogin=true');
}
if(isset($_GET['logout']))
{
  session_destroy();
  header('Location: ../?showlogin=true');
}

$q = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
$q->execute(array($_SESSION['member']['id']));
$member = $q->fetch(PDO::FETCH_ASSOC);

$q = $pdo->prepare('SELECT * FROM `tasks` WHERE `id`=?');
$q->execute(array($_GET['id']));
$task = $q->fetch(PDO::FETCH_ASSOC);

if($task['by_member'] !== $member['id'])
{
  header('Location: index.php');
  die();
}

$q = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
$q->execute(array($task['by_member']));
$memberInfo = $q->fetch(PDO::FETCH_ASSOC);

if(isset($_GET['reopen']))
{
  $q = $pdo->prepare('UPDATE `tasks` SET `status`=? WHERE `id`=?');
  $q->execute(array('active', $task['id']));

  $q = $pdo->prepare('DELETE FROM `developer_tasks` WHERE `task_id`=?');
  $q->execute(array($task['id']));
}

?><!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="shortcut icon" href="https://nerdpilots.com/wp-content/themes/NerdPilots/img/favicon.ico"/>

    <title>NerdPilots - <?php echo $task['title']; ?></title>
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
      img
      {
        max-width: 100% !important;
      }
      [target="_blank"] img
      {
        display: inline-block !important;
        max-width: 250px !important;
        margin: 5px;
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

      if(isset($_POST['reply']))
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

        $_POST['reply'] = convert_input($_POST['reply']).$files;

        $q = $pdo->prepare('INSERT INTO `task_replies` VALUES (?,?,?,?,?)');
        $q->execute(array($task['id'], explode(' ', $member['name'])[0].' '.substr(explode(' ', $member['name'])[1], 0, 1).'.', $_POST['reply'], time(), NULL));

        header('Location: task.php?id='.$task['id']);
        exit();

      }

      ?>

      <div class="float-left">
        <img src="img/logo.svg" style="width: 180px; height: 58.11px;" />
      </div>
      <div class="clearfix"></div>

      <div class="row">
        <div class="col-sm-12">
          <br/>
          <h2><?php echo $task['title'];
          switch ($task['status']) {
            case 'active':
              echo ' <span class="badge badge-success">Active</span>';
              break;
            case 'in-progress':
              echo ' <span class="badge badge-primary">In-Progress</span>';
              break;
            case 'completed':
              echo ' <span class="badge badge-success">Completed</span>';
              break;
          }
          ?></h2>
          <p>
            <strong><?php echo explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1); ?>.</strong><br/>
            <?php echo convert_links(convert_images($task['description'])); ?>
          </p>
          <hr/>

          <?php
          $q = $pdo->prepare('SELECT * FROM `task_replies` WHERE `task_id`=? ORDER BY `time` ASC');
          $q->execute(array($task['id']));
          foreach ($q as $row) {
            echo '<p>
            <strong>'.$row['reply_by'].'</strong><br/>
            '.convert_links(convert_images($row['reply'])).'<br/><em class="text-muted">'.time_elapsed_string('@'.$row['time']).'</em>
          </p><hr/>';
          }
          ?>
          
          <form action="" method="post" enctype="multipart/form-data">
            <label>Reply</label>
            <textarea name="reply" class="form-control" rows="5" style="margin-bottom: 20px;" <?php
            if(isset($_GET['reopen']))
            {
              echo ' placeholder="Please enter a reason for reopening this task."';
            }
            ?>></textarea>
            <label>Attach</label>
            <input type="file" name="files[]" class="btn btn-default btn-sm" style="margin-bottom: 10px; display: block; max-width: 500px;" multiple>
            <button type="submit" class="btn btn-primary">Reply</button>
          </form>
          <br/><br/>

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
            <form action="" method="post">
              
              <div class="row">
                <div class="col-md-8">
                  <label>Title</label>
                  <input type="text" name="title" class="form-control" placeholder="Example: Install a new theme on my wordpress blog." style="margin-bottom: 10px;">
                  <label>Description</label>
                  <textarea name="description" class="rich-text"></textarea>
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
                  <button type="submit" class="btn btn-danger btn-lg">Submit Task</button>
                </div>
              </div>

            </form>
          </div>
        </div>
      </div>
    </div>

    <a href="javascript:void(0)" id="up" style="position: fixed; bottom: 0; left: 0; z-index: 99999; background: rgba(255,255,255,0.75); padding: 10px; border-radius: 0 10px; border: 1px groove #EFEFEF; display: none; overflow: hidden;"><img src="img/navigate-up.png" style="width: 50px;"> UP</a>

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
        $("html, body").animate({ scrollTop: $(document).height() }, 1000);
        setInterval(function(){
          if($(window).scrollTop()>300)
          {
            $('#up').show();
          }
          else
          {
            $('#up').hide();
          }
        }, 500);
        $('#up').click(function(){
           $("html, body").animate({ scrollTop: 0 }, 800);
           return false;
        });
      });
    </script>
  </body>
</html>