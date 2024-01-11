<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<header>
    <h1>My orders</h1>
    <nav>
        <a href="index.php">Back to Home</a>
    </nav>
</header>
<?php
require_once '../utils/dbUtils.php';
session_start_or_expire();

if (!isset($_SESSION['email']) || !is_string($_SESSION['email'])) {
    performLog("Warning", "User not logged in while in orders", array());
    header('Location: login.php?redirect=orders.php');
    exit();
}
else {
    echo "<table>";
    echo "<tr>";
    echo "<th>Order ID</th>";
    echo "<th>Books</th>";
    echo "<th>Address</th>";
    echo "<th>Total Price</th>";
    echo "<th>Status</th>";
    echo "</tr>";
    // connect to the database
    try {
        $db = new DBConnection();

        $user_id = getUserID($_SESSION['email']);
        if(!$user_id) {
            performLog("Error", "Failed to get user id from DB", array("email" => $_SESSION['email']));
            $_SESSION['errorMsg'] = 'something went wrong with your request';
            header('Location: index.php');
            exit();
        }
        $db->stmt = $db->conn->prepare("SELECT id,cart,address,total_price,status FROM orders WHERE user = ?");
        $db->stmt->bind_param("i", $user_id);
        $db->stmt->execute();
        $result = mysqli_stmt_get_result($db->stmt);
        if (mysqli_num_rows($result) > 0) {
            // Loop through each row
            while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                // Display each row or perform operations with $row data
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td><a href='books.php?id=" . htmlspecialchars($row['cart']) . "'>Books</a></td>";
                echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                // price is divided by 100 to avoid floating point arithmetic
                echo "<td>" . intval(htmlspecialchars($row['total_price']))/ 100 . "€</td>";
                echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                echo "</tr>";
            }
        }
    }
    catch (mysqli_sql_exception $e) {
        performLog("Error", "Failed to connect to DB in orders.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
        session_unset();
        session_destroy();
        header('Location: 500.html');
    }
}

?>
</body>
</html>
