<?php
/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'st3v37895');
define('DB_NAME', 'MyGroceryList');
 
/* Attempt to connect to MySQL database */
try{
    $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
    // Disable emulated prepared statements and use real prepared statements.
    // This makes sure the statement and the values aren't parsed by PHP before sending it
    // to the MySQL server (giving a possible attacker no chance to inject malicious SQL).
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("ERROR: Could not connect. " . $e->getMessage());
}
?>