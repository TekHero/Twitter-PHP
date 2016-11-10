<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 10/4/16
 * Time: 4:26 PM
 */

// Check if there is information that is being passed to this file, such as the username & password
if (empty($_REQUEST["username"]) || empty($_REQUEST["password"])) {
    $returnArray["status"] = "400";
    $returnArray["message"] = "Missing Required Information";
    echo json_encode($returnArray);
    return;
}

// Step 1. Store the values that are being passed to this file into the variables
$username = htmlentities($_REQUEST["username"]);
$password = htmlentities($_REQUEST["password"]);

// Step 2. Build connection to database
$file = parse_ini_file("../../../Twitter.ini");

// Store in php var information from file
$host = trim($file["dbhost"]);
$user = trim($file["dbuser"]);
$pass = trim($file["dbpass"]);
$name = trim($file["dbname"]);

// Import Access Class
require ("secure/access.php");
$access = new access($host, $user, $pass, $name);
$access->connect();

// Step 3. Get user information
// Call the getUser function and store the user that is retrieved into the $user variable
$user = $access->getUser($username);

// Check if user variable is empty or nil
if (empty($user)) {
    $returnArray["status"] = "403";
    $returnArray["message"] = "User is not found";
    echo json_encode($returnArray);
    return;
}

// Step 4. Check validity of password

// Get password & salt from database
$secured_password = $user["password"]; // Password for the user that is stored in the database
$salt = $user["salt"]; // Salt that is stored for the user in the database

// Check if passwords retrieved from the user matches the ones in the database & the entered ones
// Check if the variable secured_password contains the password which in this case, the password is the users password
// + the generated salt is equal to the combination of the entered password & the salt value in the database
if ($secured_password == sha1($password . $salt)) {
    $returnArray["status"] = "200";
    $returnArray["message"] = "Logged in Sucessfully";
    $returnArray["id"] = $user["id"];
    $returnArray["username"] = $user["username"];
    $returnArray["email"] = $user["email"];
    $returnArray["fullname"] = $user["fullname"];
    $returnArray["ava"] = $user["ava"];

    // If the password does not match, then return a error message to the json
} else {

    $returnArray["status"] = "403";
    $returnArray["message"] = "Passwords do not match";
}


// Step 5. Close connection
$access->disconnect();


// Step 6. Throw back all information to user
echo json_encode($returnArray);