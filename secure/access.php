<?php
/**
 * Created by PhpStorm.
 * User: Brian
 * Date: 9/25/16
 * Time: 5:27 PM
 */

class access {

    // Connection global variables
    var $host = null;
    var $user = null;
    var $pass = null;
    var $name = null;
    var $connection = null;
    var $result = null;

    // Constructing class (aka, the initializer function)
    // Set the values of the parameters to the global variables
    function __construct($dbHost, $dbUser, $dbPass, $dbName) {
        $this->host = $dbHost;
        $this->user = $dbUser;
        $this->pass = $dbPass;
        $this->name = $dbName;
    }

    // Connection Function
    public function connect() {

        // Establish connection and store it in the variable called connection
        // or Establish a connection to the host & database
        // Then storing that new connection inside the connection variable to be of use later on
        $this->connection = new mysqli($this->host, $this->user, $this->pass, $this->name);

        // Check for error
        if (mysqli_connect_errno()) {
            echo "Could not connect to database";
        }

        // Support all languages - Set the connection to support all languages
        $this->connection->set_charset("utf8");
    }

    // Disconnection Function
    public function disconnect() {

        // Check if there is a connection, if so, close the connection
        if ($this->connection != null) {
            $this->connection->close();
        }
    }

    // Insert user
    public function registerUser($username, $password, $salt, $email, $fullname) {

        // Command for SQL
        $sqlCommand = "INSERT INTO users SET username=?, password=?, salt=?, email=?, fullname=?";
        // Prepare the SQL command for execution, then store that prepared connection into the variable
        $statement = $this->connection->prepare($sqlCommand);

        // Check if there was an error
        if (!$statement) {
            throw new Exception($statement->error);
        }

        // Bind 5 paremeters of type string to be placed in sql command
        // (Pass in the values of the parameters into the SQL command)
        $statement->bind_param("sssss", $username, $password, $salt, $email, $fullname);

        // Execute the SQL command that is in the statement variable and store the result into the variable
        $returnValue = $statement->execute();

        // Return that return value
        return $returnValue;
    }

    // Select user information function
    public function selectUser($username) {

        $returnArray = array();

        // SQL Command
        // Select all the values from the users column where the username is equal to the username passed in the paremter
        $sqlCommand = "SELECT * FROM users WHERE username='".$username."'";

        // Assign result we got from $sqlCommand to $result variable
        $result = $this->connection->query($sqlCommand);

        // Check if the result is not nil and if there is at least 1 value
        if ($result != null && (mysqli_num_rows($result) >= 1)) {

            // Assign results we got to $row as associative array
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if (!empty($row)) {
                $returnArray = $row;
            }
        }

        return $returnArray;
    }

    // Save email confirmation message's token
    public function saveToken($table, $id, $token) {

        // SQL Command
        $sql = "INSERT INTO $table SET id=?, token=?";

        // Prepare statement to be executed
        $statement = $this->connection->prepare($sql);

        // Error occurred
        if (!$statement) {
            throw new Exception($statement->error);
        }

        // Bind parameters to SQL Command
        $statement->bind_param("is", $id, $token);

        // Launch or Execute Command & store feedback in $returnValue
        $returnValue = $statement->execute();

        return $returnValue;
    }

    // Get ID of user via $emailToken he received via email's $_GET
    function getUserID($table, $token) {

        $returnArray = array();

        // SQL Command
        $sql = "SELECT id FROM $table WHERE token = '".$token."'";
        // Launch SQL command
        $result = $this->connection->query($sql);

        // If $result is not empty and store some content
        if ($result != null && (mysqli_num_rows($result) >= 1)) {

            // Content from $result convert to assoc array and store in $row
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if (!empty($row)) {
                $returnArray = $row;
            }
        }

        return $returnArray;
    }

    // Change status of email confirmation
    function emailConfirmationStatus($status, $id) {

        $sql = "UPDATE users SET emailConfirmed=? WHERE id=?";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new Exception($statement->error);
        }

        $statement->bind_param("ii", $status, $id);

        $returnValue = $statement->execute();

        return $returnValue;
    }

    // Delete token once email is confirmed
    function deleteToken($table, $token) {

        $sql = "DELETE FROM $table WHERE token=?"; // DELETE FROM emailTokens WHERE token=
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new Exception($statement->error);
        }

        $statement->bind_param("s", $token);
        $returnValue = $statement->execute();

        return $returnValue;
    }

    // Get full user information
    public function getUser($username) {

        // Declare Array to store all information we got
        $returnArray = array();
        // SQL Command
        $sql = "SELECT * FROM users WHERE username='".$username."'"; // SELECT * FROM users WHERE username='username'
        // Execute SQl Command and store the result in the variable
        $result = $this->connection->query($sql);
        // Check if there is a result
        if ($result != null && (mysqli_num_rows($result) >= 1)) {
            // Assign result to row as associative array
            $row = $result->fetch_array(MYSQLI_ASSOC);
            // Check if row is not empty, meaning there is something in there
            if (!empty($row)) {
                $returnArray = $row;
            }
        }

        return $returnArray;
    }

    // Select & return the user whos email matches the one passed in
    public function selectUserViaEmail($email) {

        $returnArray = array();

        // SQL Command
        // Select all the values from the users column where the username is equal to the username passed in the paremter
        $sqlCommand = "SELECT * FROM users WHERE email='".$email."'";

        // Assign result we got from $sqlCommand to $result variable
        $result = $this->connection->query($sqlCommand);

        // Check if the result is not nil and if there is at least 1 value
        if ($result != null && (mysqli_num_rows($result) >= 1)) {

            // Assign results we got to $row as associative array
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if (!empty($row)) {
                $returnArray = $row;
            }
        }

        return $returnArray;
    }

    // Updating password via link we go on reset password email
    public function updatePassword($id, $password, $salt) {

        $sql = "UPDATE users SET password=?, salt=? WHERE id=?";
        $statement = $this->connection->prepare($sql);

        if (!$statement) {
            throw new Exception($statement->error);
        }

        $statement->bind_param("ssi", $password, $salt, $id);

        $returnValue = $statement->execute();

        return $returnValue;
    }

    // Saving ava path in database
    function updateAvaPath($path, $id) {

        // SQL statement
        $sql = "UPDATE users SET ava=? WHERE id=?"; // UPDATE users SET ava=$path WHERE id=$id

        // Prepare to be executed
        $statement = $this->connection->prepare($sql);

        // Check if there was an error
        if (!$statement) {
            throw new Exception($statement->error);
        }

        // Bind the parameters to the sql statement
        $statement->bind_param("si", $path, $id);

        // Assign execution result to $returnValue
        $returnValue = $statement->execute();

        // Return it
        return $returnValue;
    }

    public function selectUserViaID($id) {

        $returnArray = array();

        // SQL Command
        // Select all the values from the users column where the username is equal to the username passed in the paremter
        $sqlCommand = "SELECT * FROM users WHERE id='".$id."'";

        // Assign result we got from $sqlCommand to $result variable
        $result = $this->connection->query($sqlCommand);

        // Check if the result is not nil and if there is at least 1 value
        if ($result != null && (mysqli_num_rows($result) >= 1)) {

            // Assign results we got to $row as associative array
            $row = $result->fetch_array(MYSQLI_ASSOC);

            if (!empty($row)) {
                $returnArray = $row;
            }
        }

        return $returnArray;
    }
}


















