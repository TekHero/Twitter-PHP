<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 10/2/16
 * Time: 1:42 PM
 */

class email {

    // Generate Unique Token when he got confirmation email message
    function generateToken($length) {

        // Some characters
        $characters = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM1234567890";
        // Get length of characters string
        $charactersLength = strlen($characters);

        $token = "";

        // Generate random character from $characters until it it less then the charactersLength
        for ($i = 0; $i < $length; $i++) {
            $token .= $characters[rand(0, $charactersLength-1)];
        }

        return $token;

    }

    // Open confirmation template that the user is going to receive
    function confirmationTemplate() {

        // Open file
        $file = fopen("templates/confirmationTemplate.html", "r") or die("Unable to open file");

        // Store content of file in $template variable
        $template = fread($file, filesize("templates/confirmationTemplate.html"));

        fclose($file);

        return $template;
    }

    // Open confirmation template that the user is going to receive
    function resetPasswordTemplate() {

        // Open file
        $file = fopen("templates/resetPasswordTemplate.html", "r") or die("Unable to open file");

        // Store content of file in $template variable
        $template = fread($file, filesize("templates/resetPasswordTemplate.html"));

        fclose($file);

        return $template;
    }

    // Send email with php
    function sendEmail($details) {

        // Information for email
        $subject = $details["subject"];
        $to = $details["to"];
        $fromName = $details["fromName"];
        $fromEmail = $details["fromEmail"];
        $body = $details["body"];

        // Headers required by some of SMTP or mail sites
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;content=UTF-8" . "\r\n";
        $headers .= "From: " . $fromName . " <" . $fromEmail . ">" . "\r\n";

        // Php function to send email finally
        mail($to, $subject, $body, $headers);
    }
}















