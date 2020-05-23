<?php
header('Content-Type: application/json');

// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Process delete operation after confirmation
if(isset($_POST["groceryItemId"]) && !empty($_POST["groceryItemId"])){
    // Include config file
    require_once "../config.php";

    $groceryItemId = $_POST["groceryItemId"];
    $listId = $_POST["listId"];
    $isPurchased = $_POST["isPurchased"];

    $sql = "UPDATE ListCategoryGroceryItem SET IsPurchased=:isPurchased WHERE GroceryItemId=:groceryItemId AND ListId=:listId";

    if($stmt = $pdo->prepare($sql)){
        // Bind variables to the prepared statement as parameters
        $stmt->bindParam(":isPurchased", $param_isPurchased);
        $stmt->bindParam(":groceryItemId", $param_groceryItemId);
        $stmt->bindParam(":listId", $param_listId);

        // Set parameters
        $param_isPurchased = $isPurchased;
        $param_groceryItemId = $groceryItemId;
        $param_listId = $listId;

        // Attempt to execute the prepared statement
        if($stmt->execute()){
            // Records created successfully. Redirect to landing page
            //header("location: list.php?listId=" . $listId);
            //header("Refresh:0; url=list.php?listId=" . $listId);
            exit();
        } else{
            echo "Something went wrong. Please try again later.";
        }
    }

    // Close statement
    unset($stmt);

    // Close connection
    unset($pdo);
} else {
  echo "Missing parameter groceryItemId!";
}
?>
