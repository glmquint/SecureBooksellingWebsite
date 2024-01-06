<?php
require_once 'Logger.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();
function session_start_or_expire() : void
{
    session_start();
    // Expire the session if it hasn't been accessed for more than 30 minutes.
    $maxlifetime =  $_ENV['SESSION_MAX_LIFETIME'];
    if (!isset($_SESSION['last_access'])){
        $_SESSION['last_access'] = time();
        $_SESSION['csrf_token'] = array();
    }
    if ((time() - $_SESSION['last_access']) > $maxlifetime) {
        session_unset();
        session_destroy();
        session_start();
        $_SESSION['csrf_token'] = array();
    }
}


class DBConnection {
    private $servername = "localhost";
    private $dbusername;
    private $dbpassword;
    private $dbname = "securebooksellingdb";
    public $conn;
    public $stmt;
    function __construct()
    {
        $this->dbusername = $_ENV['DB_USERNAME'];
        $this->dbpassword = $_ENV['DB_PASSWORD'];
        if(!$this->conn){
            $this->connect();
        }
    }

    function __destruct()
    {
        if ($this->conn){
            $this->conn->close();
        }
        if ($this->stmt){
            $this->stmt->close();
        }
    }

    private function connect(): void{
        $this->conn = new mysqli($this->servername, $this->dbusername, $this->dbpassword, $this->dbname);
        if ($this->conn->connect_error) {
            performLog("Error", "Failed to connect to database", array("Error" => $this->conn->connect_error));
            die("Connection failed: " . $this->conn->connect_error);
        }
    }
}

function registerUser($mail, $user_input_password): array
{
    $userArray = getUser($mail);
    if(count($userArray) > 0){
        return array("id" => $userArray['id'], "exists" => true);
    }
    $db = new DBConnection();
    if(!$db->conn){
        die("Connection failed: " . $db->conn->connect_error);
    }
    $hashed_password = password_hash($user_input_password, PASSWORD_BCRYPT);
    $stmt = $db->conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
    $stmt->bind_param("ss",$mail, $hashed_password);
    $stmt->execute();
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        $id = getUserID($mail);
        return array("id" =>$id, "exists" => false);
    } else {
        return [];
    }

}

function verifyLogin($email, $password): int
{
// Retrieve the hashed password from the database based on the username
// Replace the following lines with your database connection and query
    $db = new DBConnection();

    $db->stmt = $db->conn->prepare("SELECT password, active FROM users WHERE email=? 
        AND (failed_login_attempts < 3 OR failed_login_time < DATE_SUB(NOW(), INTERVAL 1 MINUTE))");
    $db->stmt->bind_param("s", $email);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    // check if insertion was successful
    if ($db->stmt->affected_rows > 0) {
        // Password hash found in the database
        $row = mysqli_fetch_array($result);
        $stored_hashed_password = $row["password"];
        $active = $row["active"];
        // Verify the entered password against the stored hash
        if (password_verify($password, $stored_hashed_password)) {
            $db->stmt = $db->conn->prepare("UPDATE users SET failed_login_attempts=0 WHERE email=?");
            $db->stmt->bind_param("s", $email);
            mysqli_stmt_execute($db->stmt);
            return 1 + $active; // 1 - registered but not yet activated, 2 - registered and mail activated
        } else {
            $db->stmt = $db->conn->prepare("UPDATE users SET failed_login_attempts=failed_login_attempts+1, 
                                                            failed_login_time=NOW() WHERE email=?");
            $db->stmt->bind_param("s", $email);
            mysqli_stmt_execute($db->stmt);
            return 0;
        }
    } else {
        return 0;
    }

}

function changePassword($email, $newPassword): bool
{
    // Store the hashed password in the database
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
    $db = new DBConnection();

    // use prepared statements to change the password of a user
    $db->stmt = $db->conn->prepare("UPDATE users SET password=? WHERE email=?");
    $db->stmt->bind_param("ss", $hashed_password, $email);
    $db->stmt->execute();
    // check if insertion was successful
    return ($db->stmt->affected_rows > 0);
}

function changePasswordById($userId, $newPassword): bool
{
    // Store the hashed password in the database
    $hashed_password = password_hash($newPassword, PASSWORD_BCRYPT);
    $db = new DBConnection();

    $db->stmt = $db->conn->prepare("UPDATE users SET password=? WHERE id=?");
    $db->stmt->bind_param("si", $hashed_password, $userId);
    $db->stmt->execute();
    // check if insertion was successful
    return ($db->stmt->affected_rows > 0);
}

// create a function to check if a user exists in the database
function getUser($email): array
{
    $db = new DBConnection();

    $db->stmt = $db->conn->prepare("SELECT * FROM users WHERE email=? ");
    $db->stmt->bind_param("s", $email);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    // check if insertion was successful
    if ($db->stmt->affected_rows > 0) {
        $row = mysqli_fetch_array($result);
        return array("id" => $row["id"], "active" => $row["active"]);
    } else {
        return [];
    }
}

function countToken($userId): int
{
    $db = new DBConnection();
    $db->stmt = $db->conn->prepare("SELECT COUNT(*) FROM reset_token WHERE user_id=? AND expiration_date > NOW()");
    $db->stmt->bind_param("i", $userId);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    $row = mysqli_fetch_array($result);
    return $row[0];
}

function saveToken($token, $userId, $time): bool
{
    if(countToken($userId)>=3){
        performLog("Warning", "Too many token request for a user", array("id" => $userId));
        return false;
    }

    date_default_timezone_set('Europe/Rome');
    //generate a time to live for the token express in datetime
    $ttl = date('Y-m-d H:i:s', strtotime('+'. $time .' minutes'));

    $db = new DBConnection();

    //create a prepared statemt to insert the token, the user and ttl into the database
    $db->stmt = $db->conn->prepare("INSERT INTO reset_token (token, user_id, expiration_date) VALUES (?, ?, ?)");
    //bind the parameters where token is an integer, username is a strings and ttl is datetime
    $db->stmt->bind_param("sss", $token, $userId, $ttl);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    // check if insertion was successful
    return ($db->stmt->affected_rows > 0);
}

//create a function to check if a token exists in the database
function getUidFromToken($token): int
{
    date_default_timezone_set('Europe/Rome');
    //get the current date and time
    $currentDate = date('Y-m-d H:i:s');

    $db = new DBConnection();

    //create a prepare statement to get the token and the expiration_date
    $db->stmt = $db->conn->prepare("SELECT token, expiration_date, user_id FROM reset_token WHERE token=?");
    //bind the token parameter
    $db->stmt->bind_param("s", $token);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    // check if insertion was successful
    if ($db->stmt->affected_rows > 0) {
        $row = mysqli_fetch_array($result);
        //check if the token is expired
        if ($row["expiration_date"] > $currentDate) {
            return $row["user_id"];
        } else {
            // token exists but is expired
            return 0;
        }
    } else {
        // token does not exist
        return 0;
    }

}

// delete a token from the database
function deleteToken($token): bool
{
    $db = new DBConnection();

    //create a prepare statement to delete the current token from the database
    // also remove all expired tokens to keep the database clean
    $db->stmt = $db->conn->prepare("DELETE FROM reset_token WHERE token=? OR expiration_date < NOW()");
    //bind the token parameter
    $db->stmt->bind_param("s", $token);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    // check if insertion was successful
    return ($db->stmt->affected_rows > 0);
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

function getUserID($email): int
{
    $db = new DBConnection();
    $db->stmt = $db->conn->prepare("SELECT id FROM users WHERE email=?");

    $db->stmt->bind_param("s", $email);

    $db->stmt->execute();
    $result = $db->stmt->get_result();
    $row = $result->fetch_assoc();

    return $row['id'];
}

?>