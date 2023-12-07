<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<?php

function paymentSuccessful($order, $payment) : bool
{
    // order is always successful
    return true;
}

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
    if (!paymentSuccessful($_SESSION['order'], $_SESSION['payment'])) {
        echo "<h3>Payment failed</h3>";
        echo "<a href='index.php'>Back to home</a>";
        exit();
    }

    // TODO: check the insret ignore
    // we want to insert the purchase only if it is not already present
    // but currently all purchases by a user that already has a book, get discarded
    $stmt = $conn->prepare("INSERT INTO purchases (buyer, book) VALUES (?, ?) ON DUPLICATE KEY UPDATE buyer = buyer");
    $userid = getUserID($conn, $_SESSION['username']);
    $stmt->bind_param("ii", $userid, $bookid);
    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $stmt->execute();
    }



    $stmt = $conn->prepare("INSERT INTO carts (id,book, quantity) VALUES (?, ?, ?)");

    $cart_id = random_int(100000, 999999);

    $stmt->bind_param("iii", $cart_id, $bookid, $quantity);

    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $stmt->execute();
    }

    $stmt = $conn->prepare("INSERT INTO orders (id, user, cart, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("iiisis", $_SESSION['order']['orderid'], $userid, $cart_id, $_SESSION['delivery']['address'], $_SESSION['order']['total_price'], $_SESSION['order']['status']);
    $stmt->execute();

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE books SET available = available - ? WHERE id = ?");
        $stmt->bind_param("ii", $quantity, $bookid);
        foreach ($_SESSION['cart'] as $bookid => $quantity) {
            $stmt->execute();
        }


        $conn->commit();
        $stmt->close();
        $conn->close();
        unset($_SESSION['cart']);
        unset($_SESSION['order']);
        unset($_SESSION['payment']);
        unset($_SESSION['delivery']);

        echo "<h3>Order placed successfully</h3>";
        //header('Location: index.php');
        echo "<a href='index.php'>Back to home</a>";
        //exit();
    } catch (mysqli_sql_exception $ex) {
        echo "Error in placing order";
        $conn->rollback();
        // echo out the error
        echo "Error: " . $ex->getMessage() . "<br>" . $ex->getCode();

        if ($ex->getCode() == 3819){
            echo "<p>A book that you ordered is currently not available. You can still read the digital version from <a href='books.php'>your books</a>. 
            We will let you know when your book will get back in stock!</p>";
        }


        $stmt = $conn->prepare("UPDATE orders SET status = 'waiting for restock' WHERE id = ?");

        $stmt->bind_param("i", $_SESSION['order']['orderid']);
        $stmt->execute();

        $stmt->close();
        $conn->close();

        //TODO: handle not available book and generic MySQL error
        unset($_SESSION['cart']);
        unset($_SESSION['order']);
        unset($_SESSION['payment']);
        unset($_SESSION['delivery']);
        //header('Location: index.php');
        echo "<a href='index.php'>Back to home</a>";
        exit();
    }
}
?>
</body>
</html>