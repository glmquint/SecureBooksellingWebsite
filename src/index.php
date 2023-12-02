<?php
session_start();
// TODO: implement password change functionality in new page
// TODO: implement download purchased books
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
    <?php if (!isset($_SESSION['username'])): ?>
        <p>You are not logged in. <a href="login.php">Login here</a></p>
    <?php endif ?>
    <!-- if session is started, show a link to the logout page -->
    <?php if (isset($_SESSION['username'])): ?>
        <p>You are logged in as <?php echo htmlspecialchars($_SESSION['username']) ?> <a href="logout.php">Logout</a></p>
    <?php endif ?>
    <h2>Book list</h2>
    <table>
        <tr>
            <th>Book name</th>
            <th>Author</th>
            <th>Price</th>
        </tr>
        <?php
        // connect to the database
        $db = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
        // get the book list from the db
        $result = mysqli_query($db, "SELECT * FROM books");
        // loop through the book list
        while ($row = mysqli_fetch_array($result)) {
            echo "<tr>";
            // title is a link to the book details page
            echo "<td><a href='bookdetails.php?id=" . $row['id'] . "'>" . $row['title'] . "</a></td>";
            echo "<td>" . $row['author'] . "</td>";
            // price is divided by 100 to avoid floating point arithmetic
            echo "<td>" . $row['price'] / 100 . "€</td>";
            // button to add the book to the cart
            echo "<td><a href='addtocart.php?id=" . $row['id'] . "'>Add to cart</a></td>";
            echo "</tr>";
        }
        ?>
    </table>

</body>