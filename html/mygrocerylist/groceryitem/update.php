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
$name = $category = "";
$name_err = $category_err = "";
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
        $name_err = "Please enter a valid name (max 48 chars).";
    } else{
        $name = $input_name;
    }

    // Validate category
    $input_category = trim($_POST["selCategory"]);
    if(empty($input_category)){
        $category_err = "Please select a category.";
    } else{
        $category = $input_category;
    }

    // Check input errors before inserting in database
    if(empty($name_err) && empty($category_err)){
        // Prepare an update statement
        $sql = "UPDATE GroceryItem SET Name=:name, CatId=:catId, IsSelected=:isSelected, Quantity=:quantity WHERE id=:id";


        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":name", $param_name);
            $stmt->bindParam(":catId", $param_catId);
            $stmt->bindParam(":isSelected", $param_isSelected);
            $stmt->bindParam(":quantity", $param_quantity);
            $stmt->bindParam(":id", $param_id);

            // Set parameters
            $param_name = $name;
            $param_catId = $_POST['selCategory'];
            $param_isSelected = $isSelected;
            $param_quantity = $quantity;
            $param_id = $id;

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                if ($src== "list-choosegroceries") {
                    header("location: ../list/choosegroceries.php?listId=" . $listId);
                }
                else {
                    header("location: index.php");
                }
                exit();
            } else{
              header("location: ../error.php?sender=groceryitem update error 700");
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

        // Get category list
        if(empty($category_list)){
            $smt = $pdo->prepare('SELECT * FROM Category WHERE UserId = ' . $_SESSION["id"] . ' ORDER BY Name');
            $smt->execute();
            $category_list = $smt->fetchAll();
        }

        // Prepare a select statement
        $sql = "SELECT * FROM GroceryItem WHERE id = " . $id;

        error_log($sql);

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
                    $catId = $row["CatId"];
                    $isSelected = $row["IsSelected"];
                    $quantity = $row["Quantity"];
                } else{
                    // URL doesn't contain valid id. Redirect to error page
                    header("location: ../error.php?sender=groceryitem update error 701");
                    exit();
                }

            } else{
              header("location: ../error.php?sender=groceryitem update error 702");
              exit();
            }
        }

        // Close statement
        unset($stmt);

        // Close connection
        unset($pdo);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: ../error.php?sender=groceryitem update error 703");
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
                        <h2>Update Record</h2>
                    </div>
                    <p>Edit the values and click Save to update the item.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <span class="help-block"><?php echo $name_err;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($category_err)) ? 'has-error' : ''; ?>">
                            <label>Category</label>

                            <select class="form-control" name="selCategory">

                                <?php foreach ($category_list as $row):
                                    if ($row["id"] == $catId) {
                                        echo "<option value='" . $row["id"] . "' selected>" . $row["Name"] . "</option>";
                                    }
                                    else {
                                        echo "<option value='" . $row["id"] . "'>" . $row["Name"] . "</option>";
                                    }
                                    endforeach
                                ?>
                            </select>

                            <span class="help-block"><?php echo $category_err;?></span>
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
