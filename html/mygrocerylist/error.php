
<?php
// Initialize the session
session_start();

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$sender = "";

// Processing form data when form is submitted
if(isset($_GET["sender"]) && !empty(trim($_GET["sender"]))){
    // Get URL parameter
    $sender =  trim($_GET["sender"]);

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="css/main.css">
    <style type="text/css">
        .wrapper{
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h2>Invalid Request</h2>
                    </div>
                    <div class="alert alert-danger fade in">
                      <?php
                      if (empty($sender)){
                        echo "<p>Sorry, you've made an invalid request. Please <a href='index.php' class='alert-link'>go back</a> and try again.</p>";
                      }
                      else {
                        echo "<p>Sorry, you've made an invalid request (sender = " . $sender . "). Please <a href='index.php' class='alert-link'>go back</a> and try again.</p>";
                      }
                      ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
