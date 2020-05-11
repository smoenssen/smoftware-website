<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "../config.php";
 
// Define variables and initialize with empty values
$name = "";
$name_err = "";
 
// Processing form data when form is submitted
if(isset($_POST["id"]) && !empty($_POST["id"])){
    // Get hidden input value
    $id = $_POST["id"];
    
    // Validate name
    $input_name = trim($_POST["name"]);
    if(empty($input_name)){
        $name_err = "Please enter a name.";
    } elseif(!filter_var($input_name, FILTER_VALIDATE_REGEXP, array("options"=>array("regexp"=>"/^[a-zA-Z\s]+$/")))){
        $name_err = "Please enter a valid name.";
    } else{
        $name = $input_name;
    }
    
    // Check input errors before inserting in database
    if(empty($name_err)){
        // Prepare an update statement
        $sql = "UPDATE GroceryList SET Name=:name WHERE id=:id";
 
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
                header("location: index.php");
                exit();
            } else{
                echo "Something went wrong. Please try again later.";
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
        
        // Prepare a select statement
        $sql = "SELECT * FROM GroceryList WHERE id = :id";
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
                    header("location: error.php");
                    exit();
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        
        // Get category list
        if(empty($category_list)){
            $smt = $pdo->prepare('SELECT * FROM Category WHERE UserId = ' . $_SESSION["id"]);
            $smt->execute();
            $category_list = $smt->fetchAll();
        }
        
        // Close statement
        unset($stmt);
        
        // Close connection
        unset($pdo);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: error.php");
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
    
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script type="text/javascript" src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    <style id="compiled-css" type="text/css">
        .just-padding {
          padding: 15px;
      }
      
      .list-group.list-group-root {
          padding: 0;
          overflow: hidden;
      }
      
      .list-group.list-group-root .list-group {
          margin-bottom: 0;
      }
      
      .list-group.list-group-root .list-group-item {
          border-radius: 0;
          border-width: 1px 0 0 0;
      }
      
      .list-group.list-group-root > .list-group-item:first-child {
          border-top-width: 0;
      }
      
      .list-group.list-group-root > .list-group > .list-group-item {
          padding-left: 30px;
      }
      
      .list-group.list-group-root > .list-group > .list-group > .list-group-item {
          padding-left: 45px;
      }
      
      .list-group-item .glyphicon {
          margin-right: 5px;
      }
    </style>
    
    <script type="text/javascript">//<![CDATA[
    $(function() {
            
      $('.list-group-item').on('click', function() {
        $('.glyphicon', this)
          .toggleClass('glyphicon-chevron-right')
          .toggleClass('glyphicon-chevron-down');
      });
    
    });

    //]]></script>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header">
                        <h2><?php echo $name;?></h2>
                    </div>
                    <p>Please edit the input values and submit to update the record.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="form-group<?php echo (!empty($name_err)) ? 'has-error' : ''; ?>">
                            <label>Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo $name; ?>">
                            <span class="help-block"><?php echo $name_err;?></span>
                            
                            
                        </div>
                        <input type="hidden" name="id" value="<?php echo $id; ?>"/>
                        <input type="submit" class="btn btn-primary" value="Submit">
                        <a href="index.php" class="btn btn-default">Cancel</a>
                        <!--
                        <a href="#item-1" class="list-group-item text-center" data-toggle="collapse">
                        <i class="glyphicon glyphicon-chevron-right"></i>Item 1</a>
                        <div class="list-group collapse" id="item-1">
                        
                        <a href="#item-1-1" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Item 1.1
                        </a>
                        <div class="list-group collapse" id="item-1-1">
                          <a href="#" class="list-group-item">Item 1.1.1</a>
                          <a href="#" class="list-group-item">Item 1.1.2</a>
                          <a href="#" class="list-group-item">Item 1.1.3</a>
                        </div>
                        
                        <a href="#item-1-2" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Item 1.2
                        </a>
                        <div class="list-group collapse" id="item-1-2">
                          <a href="#" class="list-group-item">Item 1.2.1</a>
                          <a href="#" class="list-group-item">Item 1.2.2</a>
                          <a href="#" class="list-group-item">Item 1.2.3</a>
                        </div>
                        
                        <a href="#item-1-3" class="list-group-item" data-toggle="collapse">
                          <i class="glyphicon glyphicon-chevron-right"></i>Item 1.3
                        </a>
                        <div class="list-group collapse" id="item-1-3">
                          <a href="#" class="list-group-item">Item 1.3.1</a>
                          <a href="#" class="list-group-item">Item 1.3.2</a>
                          <a href="#" class="list-group-item">Item 1.3.3</a>
                        </div>
                        
                      </div>-->
                    </form>
                </div>
            </div>        
        </div>
    </div>
</body>
</html>
