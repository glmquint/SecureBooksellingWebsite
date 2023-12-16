<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<h1>Cart</h1>
<a href="index.php">Back to Home</a>
<?php
session_start_or_expire();
// print the cart contents in a table

if (isset($_SESSION['cart'])) {
    echo "<table>";
    echo "<tr>";
    echo "<th>Book name</th>";
    echo "<th>Author</th>";
    echo "<th>Price</th>";
    echo "<th>Quantity</th>";
    echo "</tr>";
    // connect to the database
    $db = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
    // get the book list from the db
    // loop through the book list
    $total_price = 0;
    foreach ($_SESSION['cart'] as $bookid => $quantity){
        $stmt = mysqli_prepare($db, "SELECT * FROM books WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $bookid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result) ?? null;
        echo "<tr>";
        // title is a link to the book details page
        echo "<td><a href='bookdetails.php?id=" . $row['id'] . "'>" . $row['title'] . "</a></td>";
        echo "<td>" . $row['author'] . "</td>";
        // price is divided by 100 to avoid floating point arithmetic
        echo "<td>" . $row['price'] / 100 . "€</td>";
        $quantity = $_SESSION['cart'][$row['id']];
        $total_price += $row['price'] * $quantity;
        echo "<td>" . $quantity . "</td>";
        echo "<td><a href='addtocart.php?id=" . $row['id'] . "'>Add</a></td>";
        echo "<td><a href='removefromcart.php?id=" . $row['id'] . "'>Remove</a></td>";

        echo "</tr>";
    }
    echo "</table>";
    echo "<p>Total price: " . $total_price / 100 . "€</p>";
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
