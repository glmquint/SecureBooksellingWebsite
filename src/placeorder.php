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
require_once 'utils/Logger.php';
session_start_or_expire();

if (!isset($_SESSION['email']) || !is_string($_SESSION['email'])) {
    $_SESSION['errorMsg'] = 'something went wrong with your request';
    header('Location: login.php');
    exit();
} elseif (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['errorMsg'] = 'something went wrong with your request';
    header('Location: index.php');
    exit();
} elseif (!isset($_SESSION['order']) || !is_array($_SESSION['order'])) {
    $_SESSION['errorMsg'] = 'something went wrong with your request';
    header('Location: index.php');
    exit();
}
else {
    $db = new DBConnection();
    if (!paymentSuccessful($_SESSION['order'], $_SESSION['payment'])) {
        echo "<h3>Payment failed</h3>";
        echo "<a href='index.php'>Back to home</a>";
        exit();
    }

    // TODO: check the insert ignore
    // we want to insert the purchase only if it is not already present
    // but currently all purchases by a user that already has a book, get discarded
    $db->stmt = $db->conn->prepare("INSERT INTO purchases (buyer, book) VALUES (?, ?) ON DUPLICATE KEY UPDATE buyer = buyer");
    $userid = getUserID($_SESSION['email']);
    $db->stmt->bind_param("ii", $userid, $bookid); // bookid is defined and used just below.. it just works
    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $db->stmt->execute();
    }



    $db->stmt = $db->conn->prepare("INSERT INTO carts (id,book, quantity) VALUES (?, ?, ?)");
    // this random number is not critical if it's leaked, to see the content of the cart you need to be logged in
    // as the user that ordered it. Access auth should be secure by now.
    $cart_id = random_int(100000, 999999);

    $db->stmt->bind_param("iii", $cart_id, $bookid, $quantity);

    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $db->stmt->execute();
    }
    if(!is_string($_SESSION['delivery']['address'])||!is_int($_SESSION['order']['total_price'])||!is_string($_SESSION['order']['status'])){
        performLog("Error", "Wrong type in order field", array("email" => $_SESSION['email'],
                    "orderid" => $_SESSION['order']['orderid'], "address"=>$_SESSION['delivery']['address'],
                    "total_price"=>$_SESSION['order']['total_price'], "status"=>$_SESSION['order']['status']));
        $_SESSION['errorMsg'] = 'something went wrong with your order request';
        header('Location: index.php');
        exit();
    }
    $db->stmt = $db->conn->prepare("INSERT INTO orders (id, user, cart, address, total_price, status) VALUES (?, ?, ?, ?, ?, ?)");

    $db->stmt->bind_param("iiisis", $_SESSION['order']['orderid'], $userid, $cart_id, $_SESSION['delivery']['address'], $_SESSION['order']['total_price'], $_SESSION['order']['status']);
    $db->stmt->execute();

    $db->conn->begin_transaction();
    try {
        $db->stmt = $db->conn->prepare("UPDATE books SET available = available - ? WHERE id = ?");
        $db->stmt->bind_param("ii", $quantity, $bookid);
        foreach ($_SESSION['cart'] as $bookid => $quantity) {
            $db->stmt->execute();
        }


        $db->conn->commit();

        performLog("Info", "Order placed successfully", array("email" => $_SESSION['email'], "orderid" => $_SESSION['order']['orderid']));

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
        $db->conn->rollback();
        // echo out the error

        if ($ex->getCode() == 3819){
            echo "<p>A book that you ordered is currently not available. You can still read the digital version from <a href='books.php'>your books</a>. 
            We will let you know when your book will get back in stock!</p>";

            performLog("Warning", "Book not in stock", array("email" => $_SESSION['email'], "orderid" => $_SESSION['order']['orderid']));


            $db->stmt = $db->conn->prepare("UPDATE orders SET status = 'waiting for restock' WHERE id = ?");

            $db->stmt->bind_param("i", $_SESSION['order']['orderid']);
            $db->stmt->execute();


            //TODO: handle not available book and generic MySQL error
            unset($_SESSION['cart']);
            unset($_SESSION['order']);
            unset($_SESSION['payment']);
            unset($_SESSION['delivery']);
            //header('Location: index.php');
            echo "<a href='index.php'>Back to home</a>";
        }else{
            performLog("Error", "Error while placing order", ["db_msg"=>$ex->getMessage(), "db_error_code"=>$ex->getCode(), "email" => $_SESSION['email'], "orderid" => $_SESSION['order']['orderid']]);
        }
        exit();
    }
}
?>
</body>
</html>