<?php
include 'config.php';
if(empty($_SESSION['admin']))
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

    <title>NerdPilots - Developers</title>
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

    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css">

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
            <li class="nav-item active">
              <a class="nav-link" href="admin_developers.php">Developers <span class="sr-only">(current)</span></a>
            </li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item">
              <a class="nav-link" href="admin.php?logout">Logout</a>
            </li>
          </ul>

        </div>
    </nav>
    
    <div class="container" style="margin-top: 20px; max-width: 1400px;">

      <div class="float-left">
        <img src="img/logo.svg" style="width: 180px; height: 58.11px;" />
      </div>
      <div class="float-right">
      	<!-- Button trigger modal -->
      	<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModal">
      	  Add Developer
      	</button>

      </div>
      <div class="clearfix"></div>

      <div class="row" style="margin-top: 20px;">

        <div class="col-md-12">
          <?php

          if(isset($_POST['paid']))
          {
            $q = $pdo->prepare('UPDATE `developer_tasks` SET `paid`=1 WHERE `developer`=?');
            $q->execute(array($_POST['developer']));
          }

          if(isset($_POST['name']))
          {
          	$q = $pdo->prepare('INSERT INTO `developers` VALUES (?,?,?,?,?)');
          	$q->execute(array($_POST['name'], $_POST['email'], $_POST['password'], $_POST['rate'], NULL));
          	echo '
          	<div class="alert alert-success" role="alert" style="margin-top: 10px;">
          	  '.$_POST['name'].' has been added as a developer.
          	</div>
          	';
          }

          $q = $pdo->prepare('SELECT * FROM `developers` ORDER BY `name` ASC');
          $q->execute();
          ?>
          <h4>Developers <span class="badge badge-success"><?php echo $q->rowcount(); ?></span></h4>
          <div class="card">
            <div class="card-body">
              <table class="table">
                <thead>
                  <tr>
                    <th>
                      Name
                    </th>
                    <th>
                      Email
                    </th>
                    <th>
                      Password
                    </th>
                    <th>
                      Rate
                    </th>
                    <th>
                      Unpaid Tasks
                    </th>
                    <th>
                      ID
                    </th>
                    <th>
                      Action
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <?php
                  foreach ($q as $row) {
                    echo '
                    <tr>
                      <td>
                        '.$row['name'].'
                      </td>
                      <td>
                        '.$row['email'].'
                      </td>
                      <td>
                        '.$row['password'].'
                      </td>
                      <td>
                        $'.$row['rate'].'
                      </td>
                      <td>
                      ';
                      $tasks_completed = $pdo->prepare('SELECT * FROM `developer_tasks` WHERE `developer`=? AND `completed`=? AND `paid`=0');
                      $tasks_completed->execute(array($row['id'], 1));
                      echo $tasks_completed->rowcount().' / $'.$tasks_completed->rowcount()*$row['rate'];
                      echo '
                      </td>
                      <td>
                        '.$row['id'].'
                      </td>
                      <td>';
                      if($tasks_completed->rowcount()<1)
                      {
                        echo '
                        <button type="button" class="btn btn-sm btn-success disabled" name="paid">
                          PAID
                        </button>
                        ';
                      }
                      else
                      {
                        echo '
                        <form action="" method="post">
                          <input type="hidden" name="developer" value="'.$row['id'].'">
                          <button type="submit" class="btn btn-sm btn-success" name="paid">
                            Mark Completed Tasks as Paid
                          </button>
                        </form>
                        ';
                      }
                      echo '</td>
                    </tr>
                    ';
                  }
                  ?>
                </tbody>
              </table>
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

    <br/><br/>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="exampleModalLabel">Add Developer</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
          	<form action="" method="post">
            <div class="row">
            	<?php
            	function randomPassword() {
            	    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890/!@#$%^&*()_';
            	    $pass = array(); //remember to declare $pass as an array
            	    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
            	    for ($i = 0; $i < 8; $i++) {
            	        $n = rand(0, $alphaLength);
            	        $pass[] = $alphabet[$n];
            	    }
            	    return implode($pass); //turn the array into a string
            	}
            	?>
            		<div class="col-md-6">
            			<label>Name</label>
            			<input type="text" name="name" class="form-control" style="margin-bottom: 10px;">
            		</div>
            		<div class="col-md-6">
            			<label>Email</label>
            			<input type="text" name="email" class="form-control" style="margin-bottom: 10px;" autocomplete="off">
            		</div>
            		<div class="col-md-6">
            			<label>Password</label>
            			<input type="text" name="password" value="<?php echo randomPassword(); ?>" class="form-control" style="margin-bottom: 10px;">
            		</div>
            		<div class="col-md-6">
            			<label>Rate (per Task)</label>
            			<input type="number" name="rate" value="15" class="form-control" style="margin-bottom: 10px;">
            		</div>
            		<div class="col-sm-12">
            			<button type="submit" class="btn btn-primary" style="margin-top: 10px;">Add Developer</button>
            		</div>
            	
            </div>
            </form>
          </div>
        </div>
      </div>

    <!-- Optional JavaScript -->
    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="js/jquery-2.2.4.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="//cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
      $(document).ready(function(){
        $('[attr-add-credits]').click(function(){
          var member_id = $(this).attr('attr-add-credits');
          $('#member_id').val(member_id);
        });
        $('table').DataTable();
      });
    </script>
  </body>
</html>