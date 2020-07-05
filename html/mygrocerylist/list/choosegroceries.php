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
$category_list = null;

// Processing form data when form is submitted
if(isset($_POST['id'])){
    if(!empty($_POST['data'])) {

      $listId = $_POST['id'];

      // First delete all items for this list. This handles the case
      // where items were unchecked. I'm not sure of a better way.
      $sql = "DELETE FROM ListCategoryGroceryItem WHERE listId = :listId";

      if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":listId", $param_listId);

        // Set parameters
        $param_listId = $listId;

        // Attempt to execute the prepared statement
        if(!$stmt->execute()){
          echo "Something went wrong. Please try again later.";
        }

        unset($stm);
      }

      // Now loop through all selected items
      $selectedItems = $_POST['data'];

      if (!empty($selectedItems)) {
        foreach($_POST['data'] as $value){
          $array = explode(';', $value);
          $groceryItemId = $array[0];
          $catId = $array[1];

          // Make sure this item isn't already in the list1
          $sql = "SELECT * FROM ListCategoryGroceryItem WHERE GroceryItemId = ". $groceryItemId . " AND ListId = " . $listId;

          if($result = $pdo->query($sql)){
            if ($result->rowCount() == 0) {
              // Prepare an insert statement
              $sql = "INSERT INTO ListCategoryGroceryItem (GroceryItemId, IsPurchased, Quantity, ListId, CatId) VALUES (:groceryItemId, 0, 1, :listId, :catId)";

              if($stmt = $pdo->prepare($sql)){
                  // Bind variables to the prepared statement as parameters
                  $stmt->bindParam(":groceryItemId", $param_groceryItemId);
                  $stmt->bindParam(":catId", $param_catId);
                  $stmt->bindParam(":listId", $param_listId);

                  // Set parameters
                  $param_groceryItemId = $groceryItemId;
                  $param_catId = $catId;
                  $param_listId = $listId;

                  // Attempt to execute the prepared statement
                  if($stmt->execute()){
                      // Records created successfully. Redirect to landing page
                      header("location: list.php?listId=" . $listId);
                  } else {
                      echo "Something went wrong. Please try again later.";
                  }

                  unset($stm);
              }
              else {
                  echo "Something went wrong! Please try again later.";
                  exit();
              }
            }
            else {
                echo "Something went wrong here. Please try again later.";
                exit();
            }
          }
          else {
              echo "Hmmm... something went wrong. Please try again later.";
              exit();
          }
        }
      }
      else {
        echo "Something went wrong. Please try again later!";
        exit();
      }

      // Close connection
      unset($pdo);
    }
} else {
    // Check existence of list id parameter before processing further
    if(isset($_GET["listId"]) && !empty(trim($_GET["listId"]))){
        // Get URL parameter
        $listId =  trim($_GET["listId"]);

        // Get category list
        if(empty($category_list)){
            $smt = $pdo->prepare('SELECT * FROM Category WHERE UserId = ' . $_SESSION["id"] . ' ORDER BY Name');
            $smt->execute();
            $category_list = $smt->fetchAll();
        }

        // Close statement
        unset($stmt);

        // Close connection
        unset($pdo);
    }  else{
        // URL doesn't contain id parameter. Redirect to error page
        header("location: ../error.php?sender=list choosegroceries");
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
    <link rel="stylesheet" href="../css/main.css">
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
        div.custom-control  {
            padding: 8px;
            border-top: 0px;
            border-left: 0px;
            border-right: 0px;
            border-bottom: 1px;
            border-style: solid;
            border-color: var(--row-bg-color-dark);
            background-color: var(--light-gray-bg-color);
        }
        div.custom-control.custom-checkbox{
          padding-left: 15px;
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
                        <h2 class="pull-left">Choose Items</h2>
                        <?php echo "<a href='../groceryitem/create.php?id=" . $listId . "' class='btn btn-success pull-right'>New Item</a>";?>
                    </div>

                    <p>Select items from the categories below then click Save.</p>
                    <form action="<?php echo htmlspecialchars(basename($_SERVER['REQUEST_URI'])); ?>" method="post">
                      <div class="panel-group">
                          <div class="panel panel-default">

                            <?php
                            // Open connection
                            include "../config.php";

                            if (!empty($category_list)) {
                              foreach ($category_list as $row):
                                echo "<div class='panel-heading'>\n";
                                echo "  <h4 class='panel-title'>\n";
                                echo "    <a data-toggle='collapse' href='#" . $row["Name"] . "'>" . $row["Name"] ."</a>\n";
                                echo "  </h4>\n";
                                echo "</div>\n";
                                echo "<div id='"  . $row["Name"] . "' class='panel-collapse collapse'>\n";
                                echo "  <ul class='list-group'>\n";

                                $sql = "SELECT * FROM GroceryItem WHERE CatId = " . $row["id"] . " ORDER BY Name";
                                $smt = $pdo->prepare($sql);
                                $smt->execute();
                                $groceryitem_list = $smt->fetchAll();

                                foreach ($groceryitem_list as $groceryitem_row):
                                  // data is a delimited list of grocery item id and category id
                                  echo "    <div class='custom-control custom-checkbox'>\n";

                                  // Set item checked or unchecked based on if it is in the list or not
                                  $sql = "SELECT * FROM ListCategoryGroceryItem WHERE GroceryItemId = ". $groceryitem_row["id"] . " AND ListId = " . $listId;
                                  $result = $pdo->query($sql);
                                  if ($result->rowCount() == 0) {
                                    echo "      <input type='checkbox' class='custom-control-input' name='data[]' value='" . $groceryitem_row["id"] . ";" . $groceryitem_row["CatId"] . "'>\n";
                                  }
                                  else {
                                    echo "      <input type='checkbox' class='custom-control-input' name='data[]' value='" . $groceryitem_row["id"] . ";" . $groceryitem_row["CatId"] . "' checked>\n";
                                  }

                                  echo "      <label class='custom-control-label' for='" . $groceryitem_row["id"] . "'>&nbsp;&nbsp;" . $groceryitem_row["Name"] . "</label>\n";
                                  echo "    </div>\n";
                                endforeach;

                                unset($groceryitem_list);
                                unset($smt);

                                echo "  </ul>\n";
                                echo "</div>\n";
                              endforeach;
                            }

                            // Close connection
                            unset($pdo);
                            ?>

                          </div>
                      </div>
                      <input type="hidden" name="id" value="<?php echo $listId; ?>"/>
                      <input type="submit" class="btn btn-primary" value="Save">
                      <a href="list.php?listId=<?php echo $listId;?>" class="btn btn-default">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
