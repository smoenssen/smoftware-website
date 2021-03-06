<?php
// see https://www.tutorialrepublic.com/php-tutorial/php-mysql-login-system.php

// Include config file
require_once "config.php";

// Define variables and initialize with empty values
$email = $password = $confirm_password = $captcha = "";
$email_err = $password_err = $confirm_password_err = $captcha_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){

    if (isset($_POST["btnReset"])) {
      header("location: register.php");

    } else {
      // Validate captcha
      if(isset($_POST['g-recaptcha-response'])){
          $captcha=$_POST['g-recaptcha-response'];
      }

      if (empty($captcha)) {
        $captcha_err = "Please verify that you are not a robot.";
      }
      else {
        $secretKey = CAPTCHA_SECRET_KEY;
        $ip = $_SERVER['REMOTE_ADDR'];

        // post request to server
        $url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) . '&response=' . urlencode($captcha);
        $response = file_get_contents($url);
        $responseKeys = json_decode($response, true);

        // should return JSON with success as true
        if (!$responseKeys["success"]) {
            $captcha_err = "Error verifying reCAPTCHA";
        }
      }

      // Validate email
      $email = trim($_POST["email"]);
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          $email_err = "Please enter a valid email.";
      } else {
          // Prepare a select statement
          $sql = "SELECT id FROM users WHERE email = :email";

          if($stmt = $pdo->prepare($sql)){
              // Bind variables to the prepared statement as parameters
              $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

              // Set parameters
              $param_email = trim($_POST["email"]);

              // Attempt to execute the prepared statement
              if($stmt->execute()){
                  if($stmt->rowCount() == 1){
                      $email_err = "This email is already taken.";
                  } else{
                      $email = trim($_POST["email"]);
                  }
              } else {
                header("location: error.php?sender=register error 1100");
                exit();
              }

              // Close statement
              unset($stmt);
          }
      }

      // Validate password
      if(empty(trim($_POST["password"]))){
          $password_err = "Please enter a password.";
      } elseif(strlen(trim($_POST["password"])) < 6){
          $password_err = "Password must have atleast 6 characters.";
      } else{
          $password = trim($_POST["password"]);
      }

      // Validate confirm password
      if(empty(trim($_POST["confirm_password"]))){
          $confirm_password_err = "Please confirm password.";
      } else{
          $confirm_password = trim($_POST["confirm_password"]);
          if(empty($password_err) && ($password != $confirm_password)){
              $confirm_password_err = "Password did not match.";
          }
      }

      // Check input errors before inserting in database
      if(empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($captcha_err)) {

          // Prepare an insert statement
          $sql = "INSERT INTO users (email, password) VALUES (:email, :password)";

          if($stmt = $pdo->prepare($sql)){
              // Bind variables to the prepared statement as parameters
              $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);
              $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);

              // Set parameters
              $param_email = $email;
              $param_password = password_hash($password, PASSWORD_DEFAULT); // Creates a password hash

              // Attempt to execute the prepared statement
              if($stmt->execute()){
                  // Redirect to login page
                  header("location: login.php");
              } else{
                header("location: error.php?sender=register error 1101");
                exit();
              }

              // Close statement
              unset($stmt);
          }
      }
    }

    // Close connection
    unset($pdo);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.css">
    <link rel="stylesheet" href="css/main.css">
    <style type="text/css">
        body{ font: 14px sans-serif; }
        .wrapper{ width: 350px; padding: 20px; }
    </style>
    <style> .content
        {
            max-width: 500px;
            margin: auto;
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="wrapper">
            <h3 style="color:white; font-weight: lighter"><strong>sm</strong>oftware&trade;</h3>
            <h2>Sign Up</h2>
            <p>Please fill this form to create an account.</p>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group <?php echo (!empty($email_err)) ? 'has-error' : ''; ?>">
                    <label>Email</label>
                    <input type="text" name="email" class="form-control" value="<?php echo $email; ?>">
                    <span class="help-block"><?php echo $email_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($password_err)) ? 'has-error' : ''; ?>">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" value="<?php echo $password; ?>">
                    <span class="help-block"><?php echo $password_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($confirm_password_err)) ? 'has-error' : ''; ?>">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" value="<?php echo $confirm_password; ?>">
                    <span class="help-block"><?php echo $confirm_password_err; ?></span>
                </div>
                <div class="form-group <?php echo (!empty($captcha_err)) ? 'has-error' : ''; ?>">
                    <div class="g-recaptcha" data-theme="dark" data-sitekey="<?php echo CAPTCHA_SITE_KEY; ?>"></div>
                    <span class="help-block"><?php echo $captcha_err; ?></span>
                    <br/>
                    <input type="submit" class="btn btn-primary" name="btnSubmit" value="Submit">
                    <input type="submit" class="btn btn-default" name="btnReset" value="Reset">
                </div>
                <p>Already have an account? <a href="login.php">Login here</a>.</p>


                <script src='https://www.google.com/recaptcha/api.js?hl=en'></script>
            </form>
        </div>
    </div>
</body>
</html>
