<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();
?>
<DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
    <h1>Secure Book selling website</h1>
    <!-- if session is not started, show a link to the login page -->
    <?php if (!isset($_SESSION['email'])): ?>
        <p>To access your account <a href="login.php">login here</a></p>
    <?php endif ?>
    <!-- if session is started, show a link to the logout page -->
    <?php if (isset($_SESSION['email'])): ?>
        <p>You are logged in as <?php echo htmlspecialchars($_SESSION['email']) ?> <a href="logout.php">Logout</a></p>
        <p><a href="orders.php">My orders</a> </p>
        <p><a href="books.php">My books</a> </p>
        <p><a href="changepassword.php">Change password</a></p>
    <?php endif ?>
    <p>Go to your <a href="cart.php">cart</a></p>
    <h2>Book list</h2>
    <form name='addToCart' method='post'>
    <input type='hidden' name='csrf_token' value='<?php echo $_SESSION['csrf_token'] ?>' readonly='readonly' >
    <table>
        <tr>
            <th>Book name</th>
            <th>Author</th>
            <th>Price</th>
            <th>#Avb</th>
        </tr>
        <?php
        try {
            $db = new DBConnection();

            $result = $db->conn->query("SELECT * FROM books");
            // get the book list from the db
            // loop through the book list
            while ($row = mysqli_fetch_array($result)) {
                echo "<tr>";
                // title is a link to the book details page
                echo "<td><a href='bookdetails.php?id=" . $row['id'] . "'>" . $row['title'] . "</a></td>";
                echo "<td>" . $row['author'] . "</td>";
                // price is divided by 100 to avoid floating point arithmetic
                echo "<td>" . $row['price'] / 100 . "€</td>";
                // availables
                echo "<td>" . $row['available'] . "</td>";
                // button to add the book to the cart
                echo "<td><button name='id' formaction='addtocart.php' value=". $row['id'] .">Buy</button></td>";
                echo "</tr>";
            }
        } catch (mysqli_sql_exception $e) {
            performLog("Error", "Failed to get book list from DB", array("error" => $e->getCode(), "message" => $e->getMessage()));
            $_SESSION['errorMsg'] = 'something went wrong with your request';
            //exit();
        }

        ?>
    </table>
    </form>
    <?php include 'utils/messages.php' ?>
</body>