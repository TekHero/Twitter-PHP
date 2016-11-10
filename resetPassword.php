<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 10/11/16
 * Time: 12:19 PM
 */


if (empty($_REQUEST["email"])) {
    $returnArray = array();
    $returnArray["message"] = "Missing Required Information";
    echo json_encode($returnArray);
    return;
}

// Step 1. Get information passed to this file & Secure way to store information in the variable
$email = htmlentities($_REQUEST["email"]);

// Step 2. Build Connection
$file = parse_ini_file("../../../Twitter.ini");

$host = trim($file["dbhost"]);
$user = trim($file["dbuser"]);
$pass = trim($file["dbpass"]);
$name = trim($file["dbname"]);

require ("secure/access.php");
$access = new access($host,$user,$pass,$name);
$access->connect();


// Step 3. Check if email is found in database as registered email

// Store all results of function into $user variable
$user = $access->selectUserViaEmail($email);

// Check if there is any information stored in user variable
if (empty($user)) {
    $returnArray["message"] = "Email not found";
    echo json_encode($returnArray);
    return;
}


// Step 4. Emailing
require ("secure/email.php");
$email = new email();

// Generate unique token associated with user in our db
$token = $email->generateToken(20);

// Store unique token into database
$access->saveToken("passwordTokens", $user["id"], $token);

// Prepare email message to be sent
$details = array();
$details["subject"] = "Password Reset Request on Twitter";
$details["to"] = $user["email"];
$details["fromName"] = "Twitter";
$details["fromEmail"] = "brian.xxdeathracexx.lim8@gmail.com";

// Load html template
$template = $email->resetPasswordTemplate();
$template = str_replace("{token}", $token, $template);
$details["body"] = $template;

// Send email to user
$email->sendEmail($details);


// Step 5. Return Message to app
$returnArray["email"] = $user["email"];
$returnArray["message"] = "We have sent you an email to reset your password";
echo json_encode($returnArray);


// Step 6. Close connection
$access->disconnect();


















