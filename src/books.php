<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<h1>My Books</h1>
<a href="index.php">Back to Home</a>
<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();
include 'utils/messages.php';

if (!isset($_SESSION['email']) || !is_string($_SESSION['email'])) {
    header('Location: login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
    exit();
}
else {
    echo "<table>";
    echo "<tr>";
    echo "<th>Book name</th>";
    echo "</tr>";
    $cart_id = $_REQUEST['id'] ?? "";
    // connect to the database
    try {
        $db = new DBConnection();
        $user_id = getUserID($_SESSION['email']);

        if ($cart_id != "") {
            $db->stmt = $db->conn->prepare("SELECT book,title FROM carts c
                                                INNER JOIN books b ON c.book=b.id
                                                INNER JOIN orders o ON o.cart=c.id
                                                  WHERE c.id = ? AND o.user = ?");
            $db->stmt->bind_param("ii", $cart_id, $user_id);
        } else {
            $db->stmt = $db->conn->prepare("SELECT book,title FROM purchases p INNER JOIN books b ON p.book=b.id WHERE p.buyer = ?");
            $db->stmt->bind_param("i", $user_id);
        }

        $db->stmt->execute();
        $result = mysqli_stmt_get_result($db->stmt);
        if ($db->stmt->affected_rows > 0) {
            // Loop through each row
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                // Display each row or perform operations with $row data
                echo "<tr>";
                echo "<td><a href='bookdetails.php?id=" . htmlspecialchars($row['book']) . "'>" . htmlspecialchars($row['title']) . "</a></td>";
                echo "<td><a href='download.php?id=" . htmlspecialchars($row['book']) . "'>Download</a></td>";
                echo "</tr>";
            }
        }
    }
    catch (mysqli_sql_exception $e) {
        performLog("Error", "Failed to connect to DB in books.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
        session_unset();
        session_destroy();
        header('Location: 500.html');
    }

}

?>
</body>
</html>
