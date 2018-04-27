<?php
include 'config.php';

if(isset($_GET['login']))
{
  $_SESSION['admin'] = 1;
}

if(empty($_SESSION['admin']))
{
  header('Location: ../');
}
if(isset($_GET['logout']))
{
  session_destroy();
  header('Location: ../');
}

$q = $pdo->prepare('SELECT * FROM `tasks` WHERE `id`=?');
$q->execute(array($_GET['id']));
$task = $q->fetch(PDO::FETCH_ASSOC);

$q = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
$q->execute(array($task['by_member']));
$memberInfo = $q->fetch(PDO::FETCH_ASSOC);

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
              <a class="nav-link" href="admin.php">Home</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_members.php">Members</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_developers.php">Developers</a>
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
        $q->execute(array($task['id'], 'NerdPilots', $_POST['reply'], time(), NULL));

        header('Location: admin_task.php?id='.$task['id']);
        exit();
        

      }

      if(isset($_POST['complete_task']))
      {

        $q = $pdo->prepare('UPDATE `members` SET `credits`=`credits`-? WHERE `id`=?');
        $q->execute(array($_POST['complete_task'], $memberInfo['id']));

        $q = $pdo->prepare('UPDATE `tasks` SET `status`=? WHERE `id`=?');
        $q->execute(array('completed', $task['id']));

        $q = $pdo->prepare('UPDATE `developer_tasks` SET `completed`=1 WHERE `task_id`=?');
        $q->execute(array($task['id']));

        $q = $pdo->prepare('SELECT * FROM `tasks` WHERE `id`=?');
        $q->execute(array($_GET['id']));
        $task = $q->fetch(PDO::FETCH_ASSOC);

        
            $q = $pdo->prepare('SELECT * FROM `tasks` WHERE `id`=?');
            $q->execute(array($_GET['id']));
            $task = $q->fetch(PDO::FETCH_ASSOC);
        

      }

      ?>

      <div class="float-left">
        <img src="img/logo.svg" style="width: 180px; height: 58.11px;" />
      </div>
      <div class="float-right">
        <?php
        if($task['status'] == 'active' || $task['status'] == 'in-progress')
        {
          ?>
          <button type="button" class="btn btn-success btn-lg" data-toggle="modal" data-target="#modal">
            Mark as Complete
          </button>
          <?php
        }
        ?><br/>
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
            <strong><?php echo explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1); ?>.</strong> (<span class="text-danger"><?php echo $memberInfo['credits'].' credits'; ?></span>)<br/>
            <?php echo convert_links(convert_images($task['description'])); ?>
          </p>
          <hr/>

          <?php
          $q = $pdo->prepare('SELECT * FROM `task_replies` WHERE `task_id`=? ORDER BY `time` ASC');
          $q->execute(array($task['id']));
          foreach ($q as $row) {
            echo '<p style="">
            <strong>'.$row['reply_by'].'</strong><br/>
            '.convert_links(convert_images($row['reply'])).'<br/><em class="text-muted">'.time_elapsed_string('@'.$row['time']).'</em>
            </p><hr/>';
          }
          ?>
          
          <form action="" method="post" enctype="multipart/form-data">
            <label>Reply</label>
            <textarea name="reply" class="form-control" rows="5" style="margin-bottom: 20px;"></textarea>
            <label>Attach</label>
            <input type="file" name="files[]" class="btn btn-default btn-sm" style="margin-bottom: 10px; display: block; max-width: 500px;" multiple>
            <button type="submit" class="btn btn-primary">Reply</button>
          </form>
          <br/><br/>

        </div>
      </div>

    </div>

    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modalLabel">Credits to charge</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form action="" method="post">
              <input type="number" name="complete_task" class="form-control" value="1">

              <button type="submit" class="btn btn-success" style="margin-top: 10px;">COMPLETE TASK</button>
            </form>
          </div>
        </div>
      </div>
    </div>

    <a href="javascript:void(0)" id="up" style="position: fixed; bottom: 0; left: 0; z-index: 99999; background: rgba(255,255,255,0.75); padding: 10px; border-radius: 0 10px; border: 1px groove #EFEFEF; display: none; overflow: hidden;"><img src="img/navigate-up.png" style="width: 50px;"> UP</a>

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
        $("html, body").animate({ scrollTop: $(document).height() }, 800);
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