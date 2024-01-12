<!DOCTYPE html>
    <html lang="en">
    <head>
        <title>Secure Book selling website</title>
        <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
    </head>
    <body>
    <?php
    require_once '../utils/dbUtils.php';
    session_start_or_expire();

    $bookid = $_GET['id'] ?? 0;
    try{
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
        $bookcover = $row['cover_path'] ?? 'images/default.png';
    } catch (mysqli_sql_exception $e) {
        performLog("Error", "Failed to get book list from DB in bookdetails.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
        session_unset();
        session_destroy();
        header('Location: 500.html');
    }

 ?>
 </body>
    <header>
    <h1><?php echo htmlspecialchars($booktitle) ?></h1>
    <nav>
    <a href="index.php">Back to Home</a>
    </nav>
    </header>
    <img style="display: block; margin-left: auto; margin-right: auto; width: 50%;" src="<?php echo htmlspecialchars($bookcover) ?>" alt="Book cover">
    <table>
        <tr>
            <th>Author</th>
            <th>Price</th>
            <th>Hard copies available</th>
        </tr>
        <tr>
            <td><?php echo htmlspecialchars($bookauthor) ?></td>
            <td><?php echo htmlspecialchars($bookprice) / 100 /* price is guaranteed to be an integer in db */?>€</td>
            <td><?php echo htmlspecialchars($bookavailable) ?></td>
        </tr>
    </table>
    <form method="post" action="addtocart.php">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>" readonly="readonly" >
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($bookid) ?>" readonly="readonly" >
        <button type="submit">Add to cart</button>
    </form>
    <?php
        if (isset($_SESSION['email'])) {
            try {
                $user_id = getUserID($_SESSION['email']);
                $db->stmt = $db->conn->prepare("SELECT * FROM purchases WHERE buyer = ? AND book = ?");
                $db->stmt->bind_param("ii", $user_id, $bookid);
                $db->stmt->execute();
                $result = mysqli_stmt_get_result($db->stmt);
                if ($db->stmt->affected_rows > 0) {
                    echo "<p>This item is in your bookshelf! You can <a href='download.php?id=" . htmlspecialchars($bookid) . "'>download</a> the ebook version</p>";
                }
            }
            catch (mysqli_sql_exception $e) {
                performLog("Error", "Failed to get purchesed book list from DB in bookdetails.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
                session_unset();
                session_destroy();
                header('Location: 500.html');
            }
        }
    ?>
    <p>
        <?php
            echo htmlspecialchars($booksynopsis);
        ?>
    </p>

</html>

