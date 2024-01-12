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

require_once '../utils/dbUtils.php';
require_once '../utils/Logger.php';
session_start_or_expire();

if (!isset($_SESSION['email']) || !is_string($_SESSION['email'])) {
    performLog("Warning", "User not logged in while in placeorder", array());
    $_SESSION['errorMsg'] = 'something went wrong with your request';
    header('Location: login.php');
    exit();
} elseif (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || !isset($_SESSION['order']) || !is_array($_SESSION['order']) || !isset($_SESSION['payment']) || !is_array($_SESSION['payment']) || !isset($_SESSION['delivery']) || !is_array($_SESSION['delivery'])) {
    performLog("Warning", "User with missing cart or order or payment or delivery while in placeorder", array());
    $_SESSION['errorMsg'] = 'something went wrong with your request';
    header('Location: index.php');
    exit();
} else {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        performLog("Error", "CSRF token mismatch", array("token" => $_POST['csrf_token']));
        $_SESSION['errorMsg'] = "Something went wrong with your request";
        header('Location: index.php');
        exit();
    }
    $cart_id = random_int(100000, 999999);
    try {
        $db = new DBConnection();
        if (!paymentSuccessful($_SESSION['order'], $_SESSION['payment'])) {
            echo "<h3>Payment failed</h3>";
            echo "<a href='index.php'>Back to home</a>";
            exit();
        }

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
    }
    catch (mysqli_sql_exception $e) {
        performLog("Error", "Failed to connect to DB in placeorder.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
        session_unset();
        session_destroy();
        header('Location: 500.html');
    }
    $db->conn->begin_transaction();

    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
    if(str_contains($_SERVER['SERVER_NAME'], "PhpStorm")){
        $DOMAIN = $_ENV['DEV_DOMAIN'];
    } else {
        $DOMAIN = $_ENV['DOMAIN'];
    }

    $subject = "Your order [#" . htmlspecialchars($_SESSION['order']['orderid']) . "] has been placed";
    $message = "Thanks for buying our books!\n"
        . "Total price: " . htmlspecialchars($_SESSION['order']['total_price']) / 100 . "€\n\n"
        . "You can see your order here: \n"
        . $DOMAIN . "/books.php?id=" . htmlspecialchars($cart_id) . "\n";

    // Additional headers
    $headers = "From: " . $_ENV['NO_REPLY_EMAIL'] . "\r\n";
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

        echo "<header>";
        echo "<h3>Order placed successfully</h3>";
        echo "<nav>";
        echo "<a href='index.php'>Back to home</a>";
        echo "</nav>";
        echo "</header>";
        //header('Location: index.php');
        //exit();
    } catch (mysqli_sql_exception $ex) {
        $db->conn->rollback();
        // echo out the error

        if ($ex->getCode() == 3819){ // book not in stock
            echo "<header>";
            echo "<h3>Order placed successfully</h3>";
            echo "<nav>";
            echo "<a href='index.php'>Back to home</a>";
            echo "</nav>";
            echo "</header>";
            echo "<p>A book that you ordered is currently not available. You can still read the digital version from <a href='books.php?id=" . htmlspecialchars($cart_id) . "'>your books</a>. We will let you know when your book will get back in stock!</p>";

            performLog("Warning", "Book not in stock", array("email" => $_SESSION['email'], "orderid" => $_SESSION['order']['orderid']));


            $db->stmt = $db->conn->prepare("UPDATE orders SET status = 'waiting for restock' WHERE id = ?");

            $db->stmt->bind_param("i", $_SESSION['order']['orderid']);
            $db->stmt->execute();

            $message = "Thanks for buying our books!\n"
                . "Unfortunately, a book that you ordered is currently not available.\n"
                . "We will let you know when your book will get back in stock!\n"
                . "Total price: " . htmlspecialchars($_SESSION['order']['total_price']) / 100 . "€\n\n"
                . "You can still read the digital version from : \n"
                . $DOMAIN . "/books.php?id=" . htmlspecialchars($cart_id) . "\n";

            //cannot factor out this unset because we need to keep the cart if an error occured while placing the order (different from the book not in stock error)
            unset($_SESSION['cart']);
            unset($_SESSION['order']);
            unset($_SESSION['payment']);
            unset($_SESSION['delivery']);
        }else{ // other errors
            performLog("Error", "Error while placing order", ["db_msg"=>$ex->getMessage(), "db_error_code"=>$ex->getCode(), "email" => $_SESSION['email'], "orderid" => $_SESSION['order']['orderid']]);
            echo "<header>";
            echo "<h3>A problem occured while placing the order</h3>";
            echo "<nav>";
            echo "<a href='index.php'>Back to home</a>";
            echo "</nav>";
            echo "</header>";
            echo "<p>Please try again later</p>";
            exit();
        }
    }
    // Send email
    $mailSuccess = mail($_SESSION['email'], $subject, $message, $headers);

    if ($mailSuccess) {
        performLog("Info", "Mail order confirmation sent successfully", array("email" => $_SESSION['email']));
        echo "<p>Order confirmation sent to " . htmlspecialchars($_SESSION['email']) . "</p>";
    } else {
        performLog("Error", "Failed to send email confirmation", array("email" => $_SESSION['email']));
        $_SESSION['errorMsg'] = "Failed to send order confirmation to " . $_SESSION['email'];
        session_unset();
        session_destroy();
        header('Location: 500.html');
        exit();
    }
}
?>
</body>
</html>