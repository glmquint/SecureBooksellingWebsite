<?php

function session_start_or_expire()
{
    session_start();
    // Expire the session if it hasn't been accessed for more than 30 minutes.
    $maxlifetime =  30*60;
    if (isset($_SESSION['last_access']) && ((time() - $_SESSION['last_access']) > $maxlifetime)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_access'] = time();
}


class DBConnection {
    private $servername = "localhost";
    private $dbusername = "root";
    private $dbpassword = "rootroot";
    private $dbname = "securebooksellingdb";
    public $conn;
    function __construct()
    {
        if(!$this->conn){
            $this->connect();
        }
    }

    function __destruct()
    {
        if ($this->conn){
            $this->conn->close();
        }
    }

    private function connect(): void{
        $this->conn = new mysqli($this->servername, $this->dbusername, $this->dbpassword, $this->dbname);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}

function registerUser($username, $user_input_password, $mail): int
{
    $db = new DBConnection();
    if(!$db->conn){
        die("Connection failed: " . $db->conn->connect_error);
    }
    $hashed_password = password_hash($user_input_password, PASSWORD_BCRYPT);
    $stmt = $db->conn->prepare("INSERT INTO users (username, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss",$username, $hashed_password, $mail);
    $stmt->execute();
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $stmt = $db->conn->prepare("SELECT id FROM users WHERE email=?");
        $stmt->bind_param("s", $mail);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        return $row["id"];
    } else {
        return -1;
    }

}

function verifyLogin($username, $password): int
{
// Retrieve the hashed password from the database based on the username
// Replace the following lines with your database connection and query
    $db = new DBConnection();
    if ($db->conn->connect_error) {
        die("Connection failed: " . $db->conn->connect_error);
    }


    $stmt = $db->conn->prepare("SELECT password, active FROM users WHERE username=? 
        AND (failed_login_attempts < 3 OR failed_login_time < DATE_SUB(NOW(), INTERVAL 1 MINUTE))");
    $stmt->bind_param("s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Password hash found in the database
        $row = mysqli_fetch_array($result);
        $stored_hashed_password = $row["password"];
        $active = $row["active"];
        // Verify the entered password against the stored hash
        if (password_verify($password, $stored_hashed_password)) {
            $stmt = $db->conn->prepare("UPDATE users SET failed_login_attempts=0 WHERE username=?");
            $stmt->bind_param("s", $username);
            mysqli_stmt_execute($stmt);
            return 1 + $active; // 1 - registered but not yet activated, 2 - registered and mail activated
        } else {
            $stmt = $db->conn->prepare("UPDATE users SET failed_login_attempts=failed_login_attempts+1, 
                                                            failed_login_time=NOW() WHERE username=?");
            $stmt->bind_param("s", $username);
            mysqli_stmt_execute($stmt);
            return 0;
        }
    } else {
        return 0;
    }

}

function changePassword($username, $newPassword): bool
{
    // Store the hashed password in the database
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
    $db = new DBConnection();

    // use prepared statements to change the password of a user
    $stmt = $db->conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt->bind_param("ss", $hashed_password, $username);
    $stmt->execute();
    // check if insertion was successful
    return ($stmt->affected_rows > 0);
}

function changePasswordId($userId, $newPassword): bool
{
    // Store the hashed password in the database
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
    $db = new DBConnection();

    $stmt = $db->conn->prepare("UPDATE users SET password=? WHERE id=?");
    $stmt->bind_param("si", $hashed_password, $userId);
    $stmt->execute();
    // check if insertion was successful
    return ($stmt->affected_rows > 0);
}

// create a function to check if a user exists in the database
function checkUser($username): array
{
    $db = new DBConnection();

    $stmt = $db->conn->prepare("SELECT * FROM users WHERE username=? ");
    $stmt->bind_param("s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $row = mysqli_fetch_array($result);
        return array($row["id"], $row["email"]);
    } else {
        return [];
    }
}

function saveToken($token, $userId, $time): bool
{
    date_default_timezone_set('Europe/Rome');
    //generate a time to live for the token express in datetime
    $ttl = date('Y-m-d H:i:s', strtotime('+'. $time .' minutes'));

    $db = new DBConnection();

    //create a prepared statemt to insert the token, the user and ttl into the database
    $stmt = $db->conn->prepare("INSERT INTO reset_token (token, user_id, expiration_date) VALUES (?, ?, ?)");
    //bind the parameters where token is an integer, username is a strings and ttl is datetime
    $stmt->bind_param("iss", $token, $userId, $ttl);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    return ($stmt->affected_rows > 0);
}

//create a function to check if a token exists in the database
function getUidFromToken($token): int
{
    date_default_timezone_set('Europe/Rome');
    //get the current date and time
    $currentDate = date('Y-m-d H:i:s');

    $db = new DBConnection();

    //create a prepare statement to get the token and the expiration_date
    $stmt = $db->conn->prepare("SELECT token, expiration_date, user_id FROM reset_token WHERE token=?");
    //bind the token parameter
    $stmt->bind_param("i", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $row = mysqli_fetch_array($result);
        //check if the token is expired
        if ($row["expiration_date"] > $currentDate) {
            return $row["user_id"];
        } else {
            //delete the token from the database
            deleteToken($token);
            return 0;
        }
    } else {
        return 0;
    }

}

// delete a token from the database
function deleteToken($token): bool
{
    $db = new DBConnection();

    //create a prepare statement to delete the token from the database
    $stmt = $db->conn->prepare("DELETE FROM reset_token WHERE token=? OR expiration_date < NOW()");
    //bind the token parameter
    $stmt->bind_param("i", $token);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    return ($stmt->affected_rows > 0);
}

function activateAccount($userId): bool
{
    $db = new DBConnection();

    //create a prepare statement to delete the token from the database
    $stmt = $db->conn->prepare("UPDATE users SET active=1 WHERE id=?");
    //bind the token parameter
    $stmt->bind_param("i", $userId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    return ($stmt->affected_rows > 0);
}

// TODO: maybe refactor at login time -> set uid in session
function getUserID($conn,$username): int
{
    $db = new DBConnection();
    $stmt = $db->conn->prepare("SELECT id FROM users WHERE username=?");

    $stmt->bind_param("s", $username);

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['id'];
}


?>