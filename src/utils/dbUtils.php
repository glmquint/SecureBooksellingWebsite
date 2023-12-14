<?php


class DBConnection {
    private $servername = "localhost";
    private $dbusername = "root";
    private $dbpassword = "rootroot";
    private $dbname = "securebooksellingdb";
    public $conn;
    function __construct()
    {
        echo "construct";
        if(!$this->conn){
            $this->connect();
        }
    }

    function __destruct()
    {
        echo "destruct";
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
function verifyLogin($username, $password): bool
{
// Retrieve the hashed password from the database based on the username
// Replace the following lines with your database connection and query
    $db = new DBConnection();
    if ($db->conn->connect_error) {
        die("Connection failed: " . $db->conn->connect_error);
    }


    $stmt = $db->conn->prepare("SELECT password FROM users WHERE username=? ");
    $stmt->bind_param("s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    // check if insertion was successful
    if ($stmt->affected_rows > 0) {
        // Password hash found in the database
        $row = mysqli_fetch_array($result);
        $stored_hashed_password = $row["password"];

        // Verify the entered password against the stored hash
        if (password_verify($password, $stored_hashed_password)) {
            return true;
        } else {
            return false;
        }
    } else {
        return false;
    }

}

function changePassword($username, $newPassword): bool
{
    // Store the hashed password in the database
    $db = new DBConnection();

    // use prepared statements to change the password of a user
    $stmt = $db->conn->prepare("UPDATE users SET password=? WHERE username=?");
    $stmt->bind_param("ss", $newPassword, $username);
    $stmt->execute();
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