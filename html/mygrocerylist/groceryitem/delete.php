<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "../config.php";

$src = "";
$listId = "";

// Process delete operation after confirmation
if(isset($_POST["id"]) && !empty($_POST["id"])){

    // Get values
    $listId = $_POST["listId"];
    $src = $_POST["src"];

    // Prepare a delete statement
    $sql = "DELETE FROM GroceryItem WHERE id = :id";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":id", $param_id);

        // Set parameters
        $param_id = trim($_POST["id"]);

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Records deleted successfully. Redirect to landing page
            if ($src== "list-choosegroceries") {
                header("location: ../list/choosegroceries.php?listId=" . $listId);
            }
            else {
                header("location: index.php");
            }
            exit();
        } else{
          header("location: ../error.php?sender=groceryitem delete error 602");
          exit();
        }
    }

    // Close statement
    unset($stmt);

    // Close connection
    unset($pdo);
} else {
    // Check existence of id parameter
    if(empty(trim($_GET["id"]))){
        // URL doesn't contain id parameter. Redirect to error page
        header("location: ../error.php?sender=groceryitem delete error 601");
        exit();
    }

    if(isset($_GET["src"]) && !empty(trim($_GET["src"]))){
        // Get URL parameter
        $src =  trim($_GET["src"]);
    }

    if(isset($_GET["listId"]) && !empty(trim($_GET["listId"]))){
        // Get URL parameter
        $listId =  trim($_GET["listId"]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Record</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="../css/main.css">
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
                        <h2>Delete Item</h2>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="alert alert-danger fade in">
                            <input type="hidden" name="id" value="<?php echo trim($_GET["id"]); ?>"/>
                            <p>Are you sure you want to delete this item?</p><br>
                            <p>
                              <input type="submit" class="btn btn-danger" value="Yes">
                              <input name="src" type="hidden" value="<?php echo $src?>"/>
                              <input name="listId" type="hidden" value="<?php echo $listId?>"/>
                              <input type='button' class='btn btn-default' value='No' onclick='history.back()'>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
