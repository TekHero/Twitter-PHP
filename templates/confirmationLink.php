<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 10/2/16
 * Time: 1:34 PM
 */

if (empty($_GET["token"])) {
    echo "Missing Required Information";
}

// Step 1. Check required & passed information
$token = htmlentities($_GET["token"]);

// Step 2. Build Connection

$file = parse_ini_file("../../../../Twitter.ini");

// Store in php var information from ini var
$host = trim($file["dbhost"]);
$user = trim($file["dbuser"]);
$pass = trim($file["dbpass"]);
$name = trim($file["dbname"]);

// Import Access.php to call function from that class
require ("../secure/access.php");
$access = new access($host, $user, $pass, $name);
$access->connect();


// Step 3. Get ID of user
// Store in $id the result of the function
$id = $access->getUserID("emailTokens", $token);

// Check if the $id variable has a value with a key named id
if (empty($id["id"])) {
    echo "User with this token is not found";
    return;
}

// Step 4. Change status of email confirmation & delete token
// Assign result of function executed to $result variable
$result = $access->emailConfirmationStatus(1, $id["id"]);

// Check if there is feedback from setting the confirmation status
if ($result) {

    // Step 4.1 Delete token from emailTokens table in database
    $access->deleteToken("emailTokens", $token);
    echo "Thank you! Your email has been confirmed";
}

// Step 5. Close connection
$access->disconnect();



















