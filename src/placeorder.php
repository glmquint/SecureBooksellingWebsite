<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<?php
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
    //Write a prepared statement transaction in order to update books table, orders table
    // and cart table

    // 1. Connect to the database
    $conn = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
    if(!$conn) {
        //TODO: handle error hiding
        //die("Connection failed: " . mysqli_connect_error());
        exit();
    }
    $conn->autocommit(FALSE);
    $conn->begin_transaction();
    //TODO check cart id, autoincrement or others(?)
    $stmt = $conn->prepare("INSERT INTO carts (book, quantity) VALUES (?, ?)");

    $stmt->bind_param("ii", $bookid, $quantity);

    $success = true; // Variable to track insert success

    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $stmt->execute();
        if ($stmt->affected_rows <= 0) {
            $success = false;
            break;
        }
    }
    if(!$success){
        $conn->rollback();
        $conn->close();
        header('Location: index.php');
        exit();
    }
    $stmt = $conn->prepare("INSERT INTO orders (id, user, cart, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");
    //TODO: modify username with user id otherwise it will not work
    $stmt->bind_param("iiisis", $_SESSION['order']['orderid'], $_SESSION['order']['username'], $cart_id, $_SESSION['delivery']['address'], $_SESSION['order']['total_price'], $_SESSION['order']['status']);
    $stmt->execute();
    if($stmt->affected_rows <= 0){
        $conn->rollback();
        $conn->close();
        header('Location: index.php');
        exit();
    }
    $stmt = $conn->prepare("UPDATE books SET available = available - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $bookid);
    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $stmt->execute();
        if ($stmt->affected_rows <= 0) {
            $success = false;
            break;
        }
    }
    if(!$success){
        $conn->rollback();
        $conn->close();
        header('Location: index.php');
        exit();
    }
    $conn->commit();
    $conn->close();

}
?>
</body>
</html>