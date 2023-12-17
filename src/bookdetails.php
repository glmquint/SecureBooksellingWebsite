<DOCTYPE html>
    <html lang="en">
    <head>
        <title>Secure Book selling website</title>
        <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    </head>
    <body>
    <?php
    // Purpose: Displays the details of a book
    // given the id passed as a GET parameter, the page shows the information about the book,
    // like the title, the author, the synopsis and the price
    // finally, ther is a button for the user to add the book to its cart

    $bookid = $_GET['id'] ?? 0;
    // get the book list from the db, using prepared statements
    $db = new DBConnection();
    $db->stmt = mysqli_prepare($db->conn, "SELECT * FROM books WHERE id = ?");
    mysqli_stmt_bind_param($db->stmt, "i", $bookid);
    mysqli_stmt_execute($db->stmt);
    $result = mysqli_stmt_get_result($db->stmt);
    $row = mysqli_fetch_array($result) ?? null;
    $booktitle = $row['title'] ?? 'Unkown book';
    $bookprice = $row['price'] ?? 0;
    $bookauthor = $row['author'] ?? 'Unkown author';
    $bookavailable = $row['available'] ?? 0;
    $booksynopsis = $row['synopsis'] ?? 'No info available for unkown book';

 ?>
 </body>
    <h1><?php echo $booktitle ?></h1>
    <table>
        <tr>
            <th>Author</th>
            <th>Price</th>
            <th>Hard copies available</th>
        </tr>
        <tr>
            <td><?php echo $bookauthor?></td>
            <td><?php echo $bookprice / 100 ?>€</td>
            <td><?php echo $bookavailable ?></td>
        </tr>
    </table>
    <form method="post" action="addtocart.php">
        <input type="hidden" name="id" value="<?php echo $bookid ?>" readonly="readonly" >
        <button type="submit">Add to cart</button>
    </form>
    <p>
        <?php
            echo $booksynopsis
        ?>
    </p>

</html>

