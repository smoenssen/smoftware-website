<?php
// Initialize the session
session_start();
 
// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Include config file
require_once "config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <!--<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">-->
    
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
</head>
<style> .content { max-width: 500px; margin: auto; } </style>
<body>
    <div class="content">
        <div class="wrapper">
            <h2>Welcome!</h2>
            <p><a href="logout.php">Logout</a>.</p>
            
            <?php
            echo "<table class='table table-striped'>";
            echo "<thead><tr><th>Id</th><th>Category</th></tr></thead>";
            echo "<tbody>";
            
            class TableRows extends RecursiveIteratorIterator {
                function __construct($it) {
                    parent::__construct($it, self::LEAVES_ONLY);
                }
            
                function current() {
                    return "<td>" . parent::current(). "</td>";
                }
            
                function beginChildren() {
                    echo "<tr>";
                }
            
                function endChildren() {
                    /*
                    echo "<td>";
                    echo "<button type='button' class='btn btn-outline-secondary btn-sm'>Edit</button>";
                    echo "</td>";
                    echo "<td>";
                    echo "<button type='button' class='btn btn-outline-danger btn-sm'>X</button>";
                    */
                    echo "<td>";
                    echo "<form name='editCatecory' action='editCategory.php' method='GET'>";
                    echo    "<input type='hidden' name='categoryID' value='<?php echo $id; ?>'>";
                    echo    "<input type='submit' class='btn btn-outline-secondary btn-sm' name='editRow' value='Edit'>";
                    echo "</form>";
                    
                    echo "</td>";
                    echo "</tr>" . "\n";
                }
                
                function buttonClicked() {
                    echo "BUTTON CLICKED!";
                }
            }
 
            try {
                $stmt = $pdo->prepare("SELECT id, Name FROM Category WHERE UserId = " . $_SESSION["id"]);
                $stmt->execute();
            
                // set the resulting array to associative
                $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
            
                foreach(new TableRows(new RecursiveArrayIterator($stmt->fetchAll())) as $k=>$v) {
                    echo $v;
                }
            }
            catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
            $pdo = null;
            echo "</tbody>";
            echo "</table>";
            ?>
        </div>
    </div>
</body>
</html>