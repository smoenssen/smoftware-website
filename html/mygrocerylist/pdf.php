
<?php
// Initialize the session
session_start();

// Check if the user is logged in, if not then redirect him to login page
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../login.php");
    exit;
}

// Include config file
require_once "config.php";

// Require composer autoload for Mpdf
require_once __DIR__ . '/vendor/autoload.php';
// Create an instance of the class:
$mpdf = new \Mpdf\Mpdf();

// Define variables and initialize with empty values
$name = "";
$name_err = "";

// Check existence of list id parameter before processing further
if(isset($_GET["id"]) && !empty(trim($_GET["id"]))){
    // Get URL parameter
    $listId =  trim($_GET["id"]);

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

} else {
    // URL doesn't contain id parameter. Redirect to error page.
    header("location: error.php?sender=pdf");
    exit();
}

$mpdf->WriteHTML(
'<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: sans-serif;">');

echo
'<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body style="font-family: helvetica;">';

try {

  $mpdf->WriteHTML('<columns column-count="3" vAlign="J" column-gap="7" />');

  //$mpdf->keepColumns = true; // this will fill column before moving to next column

  //$mpdf->WriteHTML('<columnbreak />');

if(!empty($category_list)){
for ($x = 0; $x <= 2; $x++) {//remove
  foreach ($category_list as $row):

    // Category
    $mpdf->WriteHTML('<div>');
    $mpdf->WriteHTML('<br>');
    $mpdf->WriteHTML('<h3><u>' . $row["Name"] . '</u></h3>');
    echo '<br>';
    echo '<h3><u>' . $row["Name"] . '</u></h3>';

    $sql = "SELECT * FROM GroceryItem WHERE CatId = " . $row["id"] . " ORDER BY Name";
    $smt = $pdo->prepare($sql);
    $smt->execute();
    $groceryitem_list = $smt->fetchAll();

    // Grocery items
    foreach ($groceryitem_list as $groceryitem_row):

      $sql = "SELECT * FROM ListCategoryGroceryItem WHERE GroceryItemId = ". $groceryitem_row["id"] . " AND ListId = " . $listId;
      $result = $pdo->query($sql);
      if ($result->rowCount() == 1) {
        $listCategoryGroceryItemRow = $result->fetch();
        $mpdf->WriteHTML($groceryitem_row['Name']);
        echo $groceryitem_row['Name'];
      }

    endforeach;

    unset($groceryitem_list);
    unset($smt);

    $mpdf->WriteHTML('</div>');

  endforeach;
}//remove
  // Close connection
  unset($pdo);
} else {
    $mpdf->WriteHTML('<p><em>No grocery items.</em></p>');
}
$mpdf->WriteHTML('</body>');
$mpdf->WriteHTML('</html>');
echo '</body>';
echo '</html>';

// Output a PDF file directly to the browser
$mpdf->Output();


} catch (\Mpdf\MpdfException $e) { // Note: safer fully qualified exception
                                   //       name used for catch
    // Process the exception, log, print etc.
    echo $e->getMessage();
}
