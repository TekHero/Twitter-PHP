<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 10/11/16
 * Time: 5:16 PM
 */

// Second load of this page

// Step 1. Check that there is data being passed to this file
if (!empty($_POST["password_1"]) && !empty($_POST["password_2"]) && !empty($_POST["token"])) {

    // Store the passed information into the variables
    $password_1 = htmlentities($_POST["password_1"]);
    $password_2 = htmlentities($_POST["password_2"]);
    $token = htmlentities($_POST["token"]);

    // Step 2. Check if passwords match or not
    if ($password_1 == $password_2) {

        // Step 3. Build Connection
        $file = parse_ini_file("../../../../Twitter.ini");

        $host = trim($file["dbhost"]);
        $user = trim($file["dbuser"]);
        $pass = trim($file["dbuser"]);
        $name = trim($file["dbname"]);

        require ("../secure/access.php");
        $access = new access($host,$user,$pass,$name);
        $access->connect();

        // Step 4. Get the user id that matches the token passed in
        $user = $access->getUserID("passwordTokens", $token);


        // Step 5. Update database
        if (!empty($user)) {

            // Step 5.1 Generate secured password
            $salt = openssl_random_pseudo_bytes(20);
            $secured_password = sha1($password_1 . $salt); // Combine the new password with the salt into one and store it

            // Step 5.2 Update user password
            $result = $access->updatePassword($user["id"], $secured_password, $salt);

            if ($result) {

                // Step 5.3 Delete unique token
                $access->deleteToken("passwordTokens", $token);
                $message = "Successfully created new password";

                header("Location:didResetPassword.php?message=" . $message);

            } else {

                echo "User ID is empty";
            }
        }

    } else {
        $message = "Passwords do not match";
    }
}

?>


<html>

<!--First Load of page-->

    <head>
        <!--Title-->
        <center><title>Create new password</title></center>

        <!--CSS Style-->
        <style>

            .password_field
            {
                margin: 10px;
            }

            .button
            {
                margin: 10px;
            }

        </style>


    </head>

    <body>
        <center><h1>Create new password</h1></center>

        <?php

        if (!empty($message)) {
            echo "</br>" . $message . "</br>";
        }

        ?>

    <!--Forms-->
    <form method="POST" action="<?php $_SERVER['PHP_SELF'];?>">
        <center><div><input type="password" name="password_1" placeholder="New password:" class="password_field"/></div></center>
        <center><div><input type="password" name="password_2" placeholder="Repeat password:" class="password_field"/></div></center>
        <center><div><input type="submit" value="Save" class="button"/></div></center>

        <input type="hidden" value="<?php echo $_GET['token'];?>" name="token">

        </form>

    </body>

</html>


