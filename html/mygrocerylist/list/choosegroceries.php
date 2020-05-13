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
} else {
    // Check existence of id parameter before processing further
    if(isset($_GET["listId"]) && !empty(trim($_GET["listId"]))){
        // Get URL parameter
        $id =  trim($_GET["listId"]);

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
        header("location: ../error.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Grocery List</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.js"></script>
    <style type="text/css">
        .wrapper{
            max-width: 500px;
            margin: 0 auto;
        }
        .page-header h2{
            margin-top: 0;
        }
        table tr td:last-child a{
            margin-right: 15px;
        }
    </style>
    <script type="text/javascript">
        $(document).ready(function(){
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>
</head>
<body>
    <div class="wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="page-header clearfix">
                        <a href="../" class="pull-right">Done</a>
                    </div>
                    <div class="page-header clearfix">
                        <h2 class="pull-left">Choose Groceries</h2>
                        <?php echo "<a href='../groceryitem/create.php?id=" . $id . "' class='btn btn-success pull-right'>New Grocery Item</a>";?>
                    </div>

                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                      <div class="panel-group">
                          <div class="panel panel-default">

                            <?php
                            include "../config.php";

                            foreach ($category_list as $row):
                              echo "<div class='panel-heading'>\n";
                              echo "  <h4 class='panel-title'>\n";
                              echo "    <a data-toggle='collapse' href='#" . $row["Name"] . "'>" . $row["Name"] ."</a>\n";
                              echo "  </h4>\n";
                              echo "</div>\n";
                              echo "<div id='"  . $row["Name"] . "' class='panel-collapse collapse'>\n";
                              echo "  <ul class='list-group'>\n";

                              $sql = "SELECT * FROM GroceryItem WHERE CatId = " . $row["id"];
                              $smt = $pdo->prepare($sql);
                              $smt->execute();
                              $groceryitem_list = $smt->fetchAll();

                              foreach ($groceryitem_list as $groceryitem_row):
                                echo "    <li class='list-group-item'>" . $groceryitem_row["Name"] . "</li>\n";
                              endforeach;

                              unset($groceryitem_list);

                              echo "  </ul>\n";
                              echo "</div>\n";
                            endforeach;
                            ?>

                          </div>
                      </div>
                    </form>
<!--
                    <form action="choosegroceries.php?listId=5" method="post">
                      <div class="panel-group">
                          <div class="panel panel-default">

                            <div class='panel-heading'>
                              <h4 class='panel-title'>
                              <a data-toggle='collapse' href=''#collapse1'>Meat</a>
                              </h4>
                              </div>
                              <div id='collapse1' class='panel-collapse collapse'>
                              <ul class='list-group'>
                              <li class='list-group-item'>One</li>
                              <li class='list-group-item'>Two</li>
                              <li class='list-group-item'>Three</li>
                              </ul>
                            </div>
                          </div>
                      </div>
                    </form>

                    <p>Select category to add grocery items.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                        <div class="panel-group">
                            <div class="panel panel-default">

                                <div class="panel-heading">
                                  <h4 class="panel-title">
                                    <a data-toggle="collapse" href="#collapse1">Collapsible list group</a>
                                    <a href='update.php?id=1' title='Add grocery item' data-toggle='tooltip' class='pull-right'><span class='glyphicon glyphicon-pencil'></span></a>
                                  </h4>
                                </div>
                                <div id="collapse1" class="panel-collapse collapse">
                                  <ul class="list-group">
                                    <li class="list-group-item">One</li>
                                    <li class="list-group-item">Two</li>
                                    <li class="list-group-item">Three</li>
                                  </ul>
                                </div>

                                <div class="panel-heading">
                                  <h4 class="panel-title">
                                    <a data-toggle="collapse" href="#collapse2">Collapsible list group</a>
                                  </h4>
                                </div>
                                <div id="collapse2" class="panel-collapse collapse">
                                  <ul class="list-group">
                                    <li class="list-group-item">Four</li>
                                    <li class="list-group-item">Five</li>
                                    <li class="list-group-item">Six</li>
                                  </ul>
                                </div>
                            </div>
                        </div>
                    </form>
-->
                </div>
            </div>
        </div>
    </div>
</body>
</html>
