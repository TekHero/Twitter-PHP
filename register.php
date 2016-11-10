<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 9/25/16
 * Time: 5:46 PM
 */

// If GET or POST are empty
// Check if either any of the information variables are empty or nil
if (empty($_REQUEST["username"]) || empty($_REQUEST["password"]) || empty($_REQUEST["email"]) || empty($_REQUEST["fullname"])) {

    $returnArray["status"] = "400";
    $returnArray["message"] = "Missing required information";
    echo json_encode($returnArray);
    return;
}

// STEP 1. Declare parameters of user information
// Securing information and storing variables
$username = htmlentities($_REQUEST["username"]);
$password = htmlentities($_REQUEST["password"]);
$email = htmlentities($_REQUEST["email"]);
$fullname = htmlentities($_REQUEST["fullname"]);

// Secure password
// Generate a random code comprised of 20 characters, then encrypting it by combining both password and salt
$salt = openssl_random_pseudo_bytes(20);
$secured_password = sha1($password . $salt);


// STEP 2. Build Connection
// Secure way to build connection
$file = parse_ini_file("../../../Twitter.ini");

// Accessing the Twitter.ini file and extracting certain pieces of information & storing it in the variables
$host = trim($file["dbhost"]);
$user = trim($file["dbuser"]);
$pass = trim($file["dbpass"]);
$name = trim($file["dbname"]);

// Include access.php to call function from access.php file
// (Import the class then create a new instance of the class then call the connect function)
require ("secure/access.php");
$access = new access($host,$user,$pass,$name);
$access->connect();

// STEP 3. Insert user information
$result = $access->registerUser($username, $secured_password, $salt, $email, $fullname);

// Successfully Registered
if ($result) {

    // Got current registered information and store in $user variable
    $user = $access->selectUser($username);

    // Adding the values into the array with the certain key
    // Declaring information to send as feedback to user of app as json
    $returnArray["status"] = "200";
    $returnArray["message"] = "Successfully registered";
    $returnArray["id"] = $user["id"];
    $returnArray["username"] = $user["username"];
    $returnArray["email"] = $user["email"];
    $returnArray["fullname"] = $user["fullname"];
    $returnArray["ava"] = $user["ava"];

    // STEP 4. Emailing

    // Import email.php
    require ("secure/email.php");
    // Create a new instance of the email class
    $email = new email();
    // Store generated token into $token variable
    $token = $email->generateToken(20);
    // Save information in emailTokens table
    $access->saveToken("emailTokens", $user["id"], $token);

    // Append emailing information
    $details = array();
    $details["subject"] = "Email Confirmation on Twitter";
    $details["to"] = $user["email"];
    $details["fromName"] = "Twitter Inc.";
    $details["fromEmail"] = "brian.xxdeathracexx.lim8@gmail.com";

    // Access Template File
    $template = $email->confirmationTemplate();
    // Replace {token} from confirmationTemplate.html by token variable & store all content in $template var
    $template = str_replace("{token}", $token, $template);

    $details["body"] = $template;

    $email->sendEmail($details);

} else {

    // If there is no result, then store default error codes in the array
    $returnArray["status"] = "400";
    $returnArray["message"] = "Could not register with provided information";
}

// After the new user has been inserted into the database, then disconnect from the host & database
// STEP 5. Close the connection
$access->disconnect();

// STEP 6. Json data
// Print the array in a json format (When the web page loads, it prints out all the values in the array)
echo json_encode($returnArray);
