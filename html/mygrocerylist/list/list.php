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
if(isset($_POST["listId"]) && !empty($_POST["listId"])){
    // Get hidden input value
    $listId = $_POST["listId"];

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
            $stmt->bindParam(":id", $param_listId);

            // Set parameters
            $param_name = $name;
            $param_listId = $listId;

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
  // Check existence of list id parameter before processing further
  if(isset($_GET["listId"]) && !empty(trim($_GET["listId"]))){
      // Get URL parameter
      $listId =  trim($_GET["listId"]);

      $sql = "Select * FROM GroceryList where id = " . $listId;
      $smt = $pdo->prepare($sql);
      $smt->execute();
      if ($smt->rowCount() == 1) {
        $row = $smt->fetch(PDO::FETCH_ASSOC);
        $name = $row["Name"];
      }

      // Get category list. Only include categories that have grocery items selected

      $sql = "SELECT DISTINCT c.id, c.Name, c.Icon, c.IsSelected FROM Category c
              INNER JOIN ListCategoryGroceryItem l ON l.CatId = c.id
              WHERE l.ListId = " . $listId . " ORDER BY c.Name";

      if(empty($category_list)){
          //$sql = 'SELECT * FROM Category WHERE UserId = ' . $_SESSION["id"] . ' ORDER BY Name'
          $smt = $pdo->prepare($sql);
          $smt->execute();
          $category_list = $smt->fetchAll();
      }

      // Close statement
      unset($stmt);

      // Close connection
      unset($pdo);
  }  else{
      // URL doesn't contain id parameter. Redirect to error page
      header("location: ../error.php?sender=list1");
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

            // Is purchased
            $('.ispurchased').click(function(){
              var id = this.id;
              alert(this.id);

              //TODO from code below...
            });
        });

        function myAjax() {
          alert("CLICK1");
              $.ajax({
                   type: "POST",
                   url: 'markGroceryItemPurchased.php',
                   data:{listId:'call_this'},
                   success:function(html) {
                     alert(html);
                   }

              });
         }
/*
         $(document).ready(function(){

           // Is purchased
           $('.ispurchased').click(function(){

             alert("CLICK2");

             var el = this;
             var id = this.id;
             var splitid = id.split("_");

             // Delete id
             var deleteid = splitid[1];

             // AJAX Request
             $.ajax({
               url: 'remove.php',
               type: 'POST',
               data: { id:deleteid },
               success: function(response){

                 if(response == 1){
          	 // Remove row from HTML Table
          	 $(el).closest('tr').css('background','tomato');
          	 $(el).closest('tr').fadeOut(800,function(){
          	    $(this).remove();
          	 });
                }else{
          	 alert('Invalid ID.');
                }

              }
             });
           });
        });*/
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
                        <h2 class="pull-left"><?php echo $name;?></h2>
                        <?php echo "<a href='choosegroceries.php?listId=" . $listId . "' class='btn btn-success pull-right'>Add Grocery Items</a>";?>
                    </div>

                    <?php
                    if(!empty($category_list)){
                      echo "<p>Please select grocery items and submit to update the list.</p>\n";
                      echo "<form action='htmlspecialchars(basename(" . $_SERVER['REQUEST_URI'] . ")) ' method='post'>\n";
                      echo "  <div class='panel-group'>\n";
                      echo "     <div class='panel panel-default'>\n";
                              // Open connection
                              include "../config.php";

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

                                  // Set item checked or unchecked based on if it is in the list or not
                                  $sql = "SELECT * FROM ListCategoryGroceryItem WHERE GroceryItemId = ". $groceryitem_row["id"] . " AND ListId = " . $listId;
                                  $result = $pdo->query($sql);
                                  if ($result->rowCount() == 1) {
                                    $listCategoryGroceryItemRow = $result->fetch();

                                    // data is a delimited list of grocery item id and category id
                                    //echo "    <div class='custom-control custom-checkbox'>\n";
                                    //echo "      <input type='checkbox' class='custom-control-input' name='data[]' value='" . $groceryitem_row["id"] . ";" . $groceryitem_row["CatId"] . "' checked>\n";
                                    //echo "      <label class='custom-control-label' for='" . $groceryitem_row["id"] . "'>" . $groceryitem_row["Name"] . "</label>\n";
                                    //echo "    </div>\n";

                                    echo "      <a class='list-group-item clearfix'>\n";
                                    echo          $groceryitem_row['Name'] . "\n";
                                    echo "          <span class='pull-right'> \n";
                                    //echo "              <span class='btn btn-xs btn-default is-purchased' id='" . $listCategoryGroceryItemRow["GroceryItemId"] . ";" . $listCategoryGroceryItemRow["ListId"] . " onclick=\"myAjax(); event.stopPropagation();\">\n";
                                    echo "              <span class='btn btn-xs btn-default ispurchased' id='" . $listCategoryGroceryItemRow["GroceryItemId"] . ";" . $listCategoryGroceryItemRow["ListId"] . "'>\n";
                                    echo "          <span class='glyphicon glyphicon-remove' aria-hidden='true'></span>\n";
                                    echo "          </span>\n";
                                    echo "      </a>\n";
                                  }

                                endforeach;

                                unset($groceryitem_list);
                                unset($smt);

                                echo "  </ul>\n";
                                echo "</div>\n";
                              endforeach;

                              // Close connection
                              unset($pdo);

                    echo "        </div>\n";
                    echo "    </div>\n";
                    echo "    <input type='hidden' name='id' value='" . $listId . "'/>\n";
                    echo "    <input type='submit' class='btn btn-primary' value='Submit'>\n";
                    echo "    <a href='../' class='btn btn-default'>Cancel</a>\n";
                    echo "  </form>\n";
                    } else {
                        echo "<p class='lead'><em>No grocery items.</em></p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
