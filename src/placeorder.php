<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<?php
require_once 'utils/dbUtils.php';
session_start();

if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
} elseif (!isset($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
} elseif (!isset($_SESSION['order'])) {
    header('Location: index.php');
    exit();
}
else {
    $conn = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
    if (!$conn) {
        //TODO: handle error in a better way
        echo "Error connecting to database";
        header('Location: index.php');
        exit();
    }
    $conn->autocommit(FALSE);
    $conn->begin_transaction();

    try {
        $userid = getUserID($conn, $_SESSION['username']);

        $stmt = $conn->prepare("INSERT INTO carts (id,book, quantity) VALUES (?, ?, ?)");

        $cart_id = random_int(100000, 999999);

        $stmt->bind_param("iii", $cart_id, $bookid, $quantity);

        foreach ($_SESSION['cart'] as $bookid => $quantity) {
            $stmt->execute();
        }

        $stmt = $conn->prepare("INSERT INTO orders (id, user, cart, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("iiisis", $_SESSION['order']['orderid'], $userid, $cart_id, $_SESSION['delivery']['address'], $_SESSION['order']['total_price'], $_SESSION['order']['status']);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO purchases (buyer, book) VALUES (?, ?)");
        $stmt->bind_param("ii", $userid, $bookid);
        foreach ($_SESSION['cart'] as $bookid => $quantity) {
            $stmt->execute();
        }

        $stmt = $conn->prepare("UPDATE books SET available = available - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $bookid);
        foreach ($_SESSION['cart'] as $bookid => $quantity) {
            $stmt->execute();
        }


        $conn->commit();
        $conn->close();
        unset($_SESSION['cart']);
        unset($_SESSION['order']);
        unset($_SESSION['delivery']);

        echo "<h3>Order placed successfully</h3>";
        //header('Location: index.php');
        //exit();
    } catch (mysqli_sql_exception $ex) {
        echo "Error in placing order";
        $conn->rollback();
        $conn->close();
        //TODO: handle not available book and generic MySQL error
        unset($_SESSION['cart']);
        unset($_SESSION['order']);
        unset($_SESSION['delivery']);
        header('Location: index.php');
        exit();
    }
}
?>
</body>
</html>