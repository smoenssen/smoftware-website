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

// Define variables and initialize with empty values
$name = "";
$name_err = "";
$src = "";
$listId = "";

// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input values
    $id = $_POST["id"];
    $listId = $_POST["listId"];
    $src = $_POST["src"];

    // Validate name
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } elseif(!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z0-9\s]{1,48}+$/")))){
        $name_err = "Please enter a valid name (max 48 non-special characters).";
    } else{
        $name = $input_name;
    }

    // Check input errors before inserting in database
    if(empty($name_err)){
        // Prepare an update statement
        $sql = "UPDATE Category SET Name=:name WHERE id=:id";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":name", $param_name);
            $stmt->bindParam(":id", $param_id);

            // Set parameters
            $param_name = $name;
            $param_id = $id;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records updated successfully. Redirect to landing page
                if ($src== "list-choosegroceries") {
                    header("location: ../list/choosegroceries.php?listId=" . $listId);
                }
                else {
                    header("location: index.php");
                }
                exit();
            } else{
              header("location: ../error.php?sender=category update error 300");
              exit();
            }
        }

        // Close statement
        unset($stmt);
    }

    // Close connection
    unset($pdo);
} else{
    // Check existence of id parameter before processing further
    if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
        // Get URL parameter
        $id =  trim($_GET["id"]);

        if(isset($_GET["src"]) && !empty(trim($_GET["src"]))){
            // Get URL parameter
            $src =  trim($_GET["src"]);
        }

        if(isset($_GET["listId"]) && !empty(trim($_GET["listId"]))){
            // Get URL parameter
            $listId =  trim($_GET["listId"]);
        }

        // Prepare a select statement
        $sql = "SELECT * FROM Category WHERE id = :id";
        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":id", $param_id);

            // Set parameters
            $param_id = $id;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                if($stmt->rowCount() == 1){
                    /* Fetch result row as an associative array. Since the result set contains only one row, we don't need to use while loop */
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);

                    // Retrieve individual field value
                    $name = $row["Name"];
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: ../error.php?sender=category update error 301");
                    exit();
                }

            } else{
                header("location: ../error.php?sender=category update error 302");
                exit();
            }
        }

        // Close statement
        unset($stmt);

        // Close connection
        unset($pdo);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: ../error.php?sender=category update error 303");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Record</title>
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
                        <h2>Update Category</h2>
                    </div>
                    <p>Edit the name and click Save to update the category.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <span class="help-block"><?php echo $name_err;?></span>
                        </div>
                        <input type="submit" class="btn btn-primary" value="Save">
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input name="src" type="hidden" value="<?php echo $src?>"/>
                        <input name="listId" type="hidden" value="<?php echo $listId?>"/>
                        <input type='button' class='btn btn-default' value='Cancel' onclick='history.back()'>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
