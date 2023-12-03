<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<h1>My orders</h1>
<a href="index.php">Back to Home</a>
<?php
require_once 'utils/dbUtils.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
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
    $db = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');

    $user_id = getUserID($db, $_SESSION['username']);

    $stmt = mysqli_prepare($db, "SELECT id,cart,address,total_price,status FROM orders WHERE user = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        // Loop through each row
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            // Display each row or perform operations with $row data
            echo "<tr>";
            echo "<td>" . $row['id'] . "</td>";
            echo "<td><a href='books.php?id=" . $row['cart'] . "'>Books</a></td>";
            echo "<td>" . $row['address'] . "</td>";
            // price is divided by 100 to avoid floating point arithmetic
            echo "<td>" . $row['total_price'] / 100 . "€</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "</tr>";
        }
    }
}

?>
</body>
</html>
