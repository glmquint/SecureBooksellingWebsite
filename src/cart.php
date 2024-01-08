<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<header>
    <h1>Cart</h1>
    <nav>
        <a href="index.php">Back to Home</a>
    </nav>
</header>
<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();
include 'utils/messages.php';

// print the cart contents in a table
if (isset($_SESSION['cart'])) {
    echo "<form name='form' method='post'>";
    echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "' readonly='readonly' >";
    echo "<table>";
    echo "<tr>";
    echo "<th>Book name</th>";
    echo "<th>Author</th>";
    echo "<th>Price</th>";
    echo "<th>Quantity</th>";
    echo "</tr>";
    try{
    // connect to the database
        $db = new DBConnection();
        // get the book list from the db
        // loop through the book list
        $total_price = 0;
        foreach ($_SESSION['cart'] as $bookid => $quantity){
            $db->stmt = $db->conn->prepare("SELECT * FROM books WHERE id = ?");
            $db->stmt->bind_param("i", $bookid);
            $db->stmt->execute();
            $result = mysqli_stmt_get_result($db->stmt);
            $row = mysqli_fetch_array($result) ?? null;
            if($row == null) { // book not found
                continue;
            }
            echo "<tr>";
            // title is a link to the book details page
            echo "<td><a href='bookdetails.php?id=" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['title']) . "</a></td>";
            echo "<td>" . htmlspecialchars($row['author']) . "</td>";
            // price is divided by 100 to avoid floating point arithmetic
            echo "<td>" . $row['price'] / 100 . "€</td>";
            $quantity = $_SESSION['cart'][$row['id']];
            $total_price += $row['price'] * $quantity;
            echo "<td>" . $quantity . "</td>";
            echo "<td><button name='id' formaction='addtocart.php' value=". htmlspecialchars($row['id']) .">Add</button></td>";
            echo "<td><button name='id' formaction='removefromcart.php' value=". htmlspecialchars($row['id']) .">Remove</button></td>";

            echo "</tr>";
        }
    } catch (mysqli_sql_exception $e) {
        performLog("Error", "Failed to get book list from DB in cart.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
        session_unset();
        session_destroy();
        header('Location: 500.html');
    }
    echo "</table>";
    echo "</form>";
    echo "<b>Total price: " . $total_price / 100 . "€</b>";
    // button to empty the cart
    echo "<a href='emptycart.php'>Empty cart</a>";
    // button to checkout
    echo "<a href='checkout.php'>Checkout</a>";
} else {
    echo "<p>Your cart is empty</p>";
}
?>
</body>
</html>
