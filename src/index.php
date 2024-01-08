<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body onload="loadBooks()">
    <header>
    <h1>Secure Book selling website</h1>
    <nav>
    <a href="cart.php">Cart</a>
    <?php if (isset($_SESSION['email'])): ?>
        <a href="orders.php">My orders</a>
        <a href="books.php">My books</a>
        <a href="changepassword.php">Change password</a>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
    <?php endif ?>
    </nav>
    </header>
    <?php if (isset($_SESSION['email'])): ?>
        <p>You are logged in as <?php echo htmlspecialchars($_SESSION['email']) ?></p>
    <?php endif ?>
    <!-- if session is started, show a link to the logout page -->
    <?php include 'utils/messages.php' ?>
    <h2>Book list</h2>
    <script>
        books = [
            <?php
            // connect to the database
            try {
                $db = new DBConnection();

                $result = $db->conn->query("SELECT * FROM books");
                // get the book list from the db
                // loop through the book list
                while ($row = mysqli_fetch_array($result)) {
                    echo "{";
                    echo '"id":' . $row['id'] . ',';
                    echo '"title":"' . htmlspecialchars($row['title']) . '",';
                    echo '"author":"' . htmlspecialchars($row['author']) . '",';
                    // price is divided by 100 to avoid floating point arithmetic
                    echo '"price":' . $row['price'] / 100 . ',';
                    echo '"available":' . $row['available'];
                    echo "},";
                }
            } catch (mysqli_sql_exception $e) {
                performLog("Error", "Failed to get book list from DB", array("error" => $e->getCode(), "message" => $e->getMessage()));
                $_SESSION['errorMsg'] = 'something went wrong with your request';
                //exit();
            }

            ?>
        ]
        function search() {
            // Declare variables
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("search");
            filter = input.value.toUpperCase();
            table = document.getElementsByTagName("table")[0];
            tr = table.getElementsByTagName("tr");

            // Loop through all table rows, and hide those who don't match the search query
            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[0];
                if (td) {
                    txtValue = td.textContent || td.innerText || td.innerHTML;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }

        }
        function loadBooks() {
            var table = document.getElementsByTagName("table")[0];
            for (i = 0; i < books.length; i++) {
                var row = table.insertRow(-1);
                var title = row.insertCell(0);
                var author = row.insertCell(1);
                var price = row.insertCell(2);
                var available = row.insertCell(3);
                var buy = row.insertCell(4);
                title.innerHTML = "<a href='bookdetails.php?id=" + books[i].id + "'>" + books[i].title + "</a>";
                author.innerHTML = books[i].author;
                price.innerHTML = books[i].price + "€";
                available.innerHTML = books[i].available;
                buy.innerHTML = "<button name='id' formaction='addtocart.php' value=" + books[i].id + ">Buy</button>";
            }
        }
    </script>
    <input type="text" id="search" onkeyup="search()" placeholder="Search for book title..">
    <form name='addToCart' method='post'>
    <input type='hidden' name='csrf_token' value='<?php echo $_SESSION['csrf_token'] ?>' readonly='readonly' >
    <table>
        <tr>
            <th>Book name</th>
            <th>Author</th>
            <th>Price</th>
            <th>#Avb</th>
        </tr>
    </table>
    </form>
</body>