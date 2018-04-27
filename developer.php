<?php
include 'config.php';
if(empty($_SESSION['developer']))
{
  header('Location: ../');
}
if(isset($_GET['logout']))
{
  session_destroy();
  header('Location: ../');
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

    <title>NerdPilots - Developer Dashboard</title>
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
              <a class="nav-link" href="developer.php">Home <span class="sr-only">(current)</span></a>
            </li>
            <?php
            if(isset($_SESSION['member']))
            {
              echo '
              <li class="nav-item">
                <a class="nav-link" href="index.php">Member Dashboard</a>
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

      <div class="float-left">
        <img src="img/logo.svg" style="width: 180px; height: 58.11px;" />
      </div>
      <div class="float-right">
        <h4>Developer Dashboard</h4>
      </div>
      <div class="clearfix"></div>

      <div class="row" style="margin-top: 20px;">

        <div class="col-md-6">
          <?php
          $q = $pdo->prepare('SELECT * FROM `tasks` WHERE `status`=? ORDER BY `id` DESC');
          $q->execute(array('active'));
          ?>
          <h4>Active Tasks <span class="badge badge-success"><?php echo $q->rowcount(); ?></span></h4>
          <div class="card" style="height: 50vh; overflow-y: scroll;">
            <div class="card-body">
              
              <?php
              foreach ($q as $row) {

                $memberInfo = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
                $memberInfo->execute(array($row['by_member']));
                $memberInfo = $memberInfo->fetch(PDO::FETCH_ASSOC);

                $updated = $pdo->prepare('SELECT * FROM `task_replies` WHERE `task_id`=? AND `time`>? ORDER BY `time` DESC LIMIT 1');
                $updated->execute(array($row['id'], time()-900));

                if($updated->rowcount()>0)
                {
                  $updated = $updated->fetch(PDO::FETCH_ASSOC);
                  echo '
                  <a class="btn btn-primary btn-block text-left" href="developer_task.php?id='.$row['id'].'">
                      '.$row['title'].' <span class="badge badge-secondary">Updated '.time_elapsed_string('@'.$updated['time']).'</span>
                      <span class="badge badge-default float-right">by '.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'.'.'</span>
                    </a>
                  ';
                }
                else
                {
                  echo '
                  <a class="btn btn-secondary btn-block text-left" href="developer_task.php?id='.$row['id'].'">
                      '.$row['title'].'
                      <span class="badge badge-default float-right">by '.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'.'.'</span>
                    </a>
                  ';
                }

                //<span class="badge badge-success">'.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'</span>
              }
              ?>

            </div>
          </div>
        </div>

        <div class="col-md-6">
          <?php
          $q = $pdo->prepare('SELECT * FROM `developer_tasks` WHERE `developer`=? AND `completed`=? ORDER BY `id` DESC');
          $q->execute(array($_SESSION['developer']['id'], '0'));
          ?>
          <h4>My Tasks <span class="badge badge-success"><?php echo $q->rowcount(); ?></span></h4>
          <div class="card" style="height: 50vh; overflow-y: scroll;">
            <div class="card-body">
              
              <?php
              foreach ($q as $row) {

                $task = $pdo->prepare('SELECT * FROM `tasks` WHERE `id`=?');
                $task->execute(array($row['task_id']));
                $task = $task->fetch(PDO::FETCH_ASSOC);

                $memberInfo = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
                $memberInfo->execute(array($task['by_member']));
                $memberInfo = $memberInfo->fetch(PDO::FETCH_ASSOC);

                $updated = $pdo->prepare('SELECT * FROM `task_replies` WHERE `task_id`=? AND `time`>? ORDER BY `time` DESC LIMIT 1');
                $updated->execute(array($task['id'], time()-900));

                if($updated->rowcount()>0)
                {
                  $updated = $updated->fetch(PDO::FETCH_ASSOC);
                  echo '
                  <a class="btn btn-primary btn-block text-left" href="developer_task.php?id='.$task['id'].'">
                      '.$task['title'].' <span class="badge badge-secondary">Updated '.time_elapsed_string('@'.$updated['time']).'</span>
                      <span class="badge badge-default float-right">by '.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'.'.'</span>
                    </a>
                  ';
                }
                else
                {
                  echo '
                  <a class="btn btn-secondary btn-block text-left" href="developer_task.php?id='.$task['id'].'">
                      '.$task['title'].'
                      <span class="badge badge-default float-right">by '.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'.'.'</span>
                    </a>
                  ';
                }

                //<span class="badge badge-success">'.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'</span>
              }
              ?>

            </div>
          </div>
        </div>

            <div class="col-md-8">
              <?php
              $q = $pdo->prepare('SELECT * FROM `developer_tasks` WHERE `completed`=? AND `developer`=? ORDER BY `id` DESC');
              $q->execute(array('1', $_SESSION['developer']['id']));
              ?>
              <h4 style="margin-top: 20px;">Tasks Completed <span class="badge badge-success"><?php echo $q->rowcount(); ?></span></h4>
              <div class="card" style="height: 50vh; overflow-y: scroll; margin-bottom: 40px;">
                <div class="card-body">
                  
                  <?php
                  foreach ($q as $row) {

                    $task = $pdo->prepare('SELECT * FROM `tasks` WHERE `id`=?');
                    $task->execute(array($row['task_id']));
                    $task = $task->fetch(PDO::FETCH_ASSOC);

                    $memberInfo = $pdo->prepare('SELECT * FROM `members` WHERE `id`=?');
                    $memberInfo->execute(array($task['by_member']));
                    $memberInfo = $memberInfo->fetch(PDO::FETCH_ASSOC);

                    $developer = $pdo->prepare('SELECT * FROM `developers` WHERE `id`=?');
                    $developer->execute(array($row['developer']));
                    $developer = $developer->fetch(PDO::FETCH_ASSOC);

                    $updated = $pdo->prepare('SELECT * FROM `task_replies` WHERE `task_id`=? AND `time`>? ORDER BY `time` DESC LIMIT 1');
                    $updated->execute(array($task['id'], time()-900));

                    if($updated->rowcount()>0)
                    {
                      $updated = $updated->fetch(PDO::FETCH_ASSOC);
                      echo '
                      <a class="btn btn-primary btn-block text-left" href="developer_task.php?id='.$task['id'].'">
                          '.$task['title'].' <span class="badge badge-default float-right">Updated '.time_elapsed_string('@'.$updated['time']).'</span>
                        </a>
                      ';
                    }
                    else
                    {
                      echo '
                      <a class="btn btn-secondary btn-block text-left" href="developer_task.php?id='.$task['id'].'">
                          '.$task['title'].'
                        </a>
                      ';
                    }

                    //<span class="badge badge-success">'.explode(' ', $memberInfo['name'])[0].' '.substr(explode(' ', $memberInfo['name'])[1], 0, 1).'</span>
                  }
                  ?>
            </div>

          </div>

        </div>

      </div>

    </div>
    <br/><br/>

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
      $(document).ready(function(){
      });
    </script>
  </body>
</html>