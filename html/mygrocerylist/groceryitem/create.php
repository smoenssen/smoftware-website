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
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // Get values
    $listId = $_POST["listId"];
    $src = $_POST["src"];

    if (isset($_POST["btnCancel"])) {
        if ($src == "category-create") {
            header("location: ../list/choosegroceries.php?listId=" . $listId);
        }
        else if ($src == "list-choosegroceries") {
            header("location: ../list/choosegroceries.php?listId=" . $listId);
        }
        else if ($src == "index") {
            header("location: index.php");
        }
        else {
            header("location: ../error.php?sender=grocery item create cancel src = " . $src);
        }
        exit();
    }

    if(empty($category_list)){
        $smt = $pdo->prepare('SELECT * FROM Category WHERE UserId = ' . $_SESSION["id"] . ' ORDER BY Name');
        $smt->execute();
        $category_list = $smt->fetchAll();
    }

    // Validate name
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } elseif(!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z0-9\s]{1,48}+$/")))){
        $name_err = "Please enter a valid name (max 48 non-special characters).";
    } else{
        $name = $input_name;
    }

    // Validate category
    if (isset($_POST["selCategory"])) {
      $input_category = trim($_POST["selCategory"]);
    }
    if(empty($input_category)){
        $category_err = "Please select a category.";
    } else{
        $category = $input_category;
    }

    // Check input errors before inserting in database
    if(empty($name_err) && empty($category_err)){
        // Prepare an insert statement
        $sql = "INSERT INTO GroceryItem (Name, CatId, IsSelected, Quantity, UserId) VALUES (:name, :catId, 0, 1, :userId)";

        if($stmt = $pdo->prepare($sql)){
            // Bind variables to the prepared statement as parameters
            $stmt->bindParam(":name", $param_name);
            $stmt->bindParam(":catId", $param_catId);
            $stmt->bindParam(":userId", $param_user_id);

            // Set parameters
            $param_name = $name;
            $param_catId = $_POST['selCategory'];
            $param_user_id = $_SESSION["id"];

            // Attempt to execute the prepared statement
            if($stmt->execute()){
                // Records created successfully. Redirect to landing page
                if ($src == "list-choosegroceries") {
                    header("location: ../list/choosegroceries.php?listId=" . $listId);
                }
                else if ($src == "category-create") {
                    header("location: ../list/choosegroceries.php?listId=" . $listId);
                }
                else if ($src == "index") {
                    header("location: index.php");
                }
                else {
                    header("location: ../error.php?sender=grocery item create src = " . $src);
                }
                exit();
            } else{
              header("location: ../error.php?sender=grocery item create error 1800");
              exit();
            }
        }

        // Close statement
        unset($stmt);
    }

    // Close connection
    unset($pdo);
} else {
    // Check existence of src parameter before processing further
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
    <title>Create Item</title>
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
                        <h2>Create Item</h2>
                    </div>
                    <p>Fill in this form and click Save to add item.</p>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?listId=" . $listId . "&src=" . $src; ?>" method="post">
                        <div class="form-group <?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <input type="hidden" name="src" class="form-control" value="<?php echo $src; ?>">
                            <span class="help-block"><?php echo $name_err;?></span>
                        </div>
                        <div class="form-group <?php echo (!empty($category_err)) ? 'has-error' : ''; ?>">
                            <label>Category</label>

                            <?php
                            // Get category list
                            if(empty($category_list)){
                                $smt = $pdo->prepare('SELECT * FROM Category WHERE UserId = ' . $_SESSION["id"] . ' ORDER BY Name');
                                $smt->execute();
                                $category_list = $smt->fetchAll();
                            }
                            ?>
                            <div class="input-group">
                                <select class="form-control" name="selCategory">
                                    <option value="" disabled selected>Choose category</option>
                                    <?php foreach ($category_list as $row): ?>
                                    <option value="<?=$row["id"]?>"><?=$row["Name"]?></option>
                                    <?php endforeach ?>
                                </select>
                                <span class="input-group-btn">
                                    <a href="../category/create.php?listId=<?php echo $listId?>&src=groceryitem-create" class="btn btn-success pull-right">New</a>
                                </span>
                            </div>

                            <span class="help-block"><?php echo $category_err;?></span>
                        </div>
                        <input type="submit" class="btn btn-primary" value="Save">
                        <input name="src" type="hidden" value="<?php echo $src?>"/>
                        <input name="listId" type="hidden" value="<?php echo $listId?>"/>
                        <input type="submit" class="btn btn-default" name="btnCancel" value='Cancel'>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
