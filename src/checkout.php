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
    header('Location: login.php?redirect=checkout.php');
    exit();
} elseif (!isset($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
} elseif (!isset($_SESSION['delivery']) || isset($_GET['updatedelivery'])) {
// recompute total price
    $db = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
    $total_price = 0;
    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $stmt = mysqli_prepare($db, "SELECT * FROM books WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "i", $bookid);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_array($result) ?? null;
        $quantity = $_SESSION['cart'][$row['id']];
        $total_price += $row['price'] * $quantity;
    }
    $db->close();

    $_SESSION['order'] = array();
    $_SESSION['order'] = [
        'orderid' => random_int(100000, 999999),
        'cart' => $_SESSION['cart'],
        'total_price' => $total_price,
        'username' => $_SESSION['username'],
        'status' => 'in transit'
    ];

    echo print_r($_SESSION['order']);

    // form to input delivery address
    echo "<h1>Delivery address</h1>";
    echo "<form method='post' action='checkout.php'>";
    echo "<label for='firstname'>First name</label>";
    echo "<input type='text' name='firstname' id='firstname' required='required'>";
    echo "<label for='lastname'>Last name</label>";
    echo "<input type='text' name='lastname' id='lastname' required='required'>";
    echo "<label for='address'>Address</label>";
    echo "<input type='text' name='address' id='address' required='required'>";
    echo "<label for='city'>City</label>";
    echo "<input type='text' name='city' id='city' required='required'>";
    echo "<label for='postalcode'>Postal code</label>";
    echo "<input type='text' name='postalcode' id='postalcode' required='required'>";
    echo "<label for='country'>Country</label>";
    echo "<input type='text' name='country' id='country' required='required'>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";


    echo "<a href='cart.php'>Back to cart</a>";
    unset($_SESSION['delivery']);
    if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['address']) && isset($_POST['city']) && isset($_POST['postalcode']) && isset($_POST['country'])) {
        $_SESSION['delivery'] = array();
        $_SESSION['delivery'] = [
            'firstname' => $_POST['firstname'] ?? '',
            'lastname' => $_POST['lastname'] ?? '',
            'address' => $_POST['address'] ?? '',
            'city' => $_POST['city'] ?? '',
            'postalcode' => $_POST['postalcode'] ?? '',
            'country' => $_POST['country'] ?? '',
        ];
        header('Location: checkout.php');
    }
} elseif (!isset($_SESSION['payment']) || isset($_GET['updatepayment'])) {
    echo print_r($_SESSION['delivery']);
    unset($_SESSION['payment']);
    if (isset($_POST['cardnumber']) && isset($_POST['cardholder']) && isset($_POST['expirationdate']) && isset($_POST['cvv'])) {
        $_SESSION['payment'] = array();
        $_SESSION['payment'] = [
            'cardnumber' => $_POST['cardnumber'] ?? '',
            'cardholder' => $_POST['cardholder'] ?? '',
            'expirationdate' => $_POST['expirationdate'] ?? '',
            'cvv' => $_POST['cvv'] ?? '',
        ];
        header('Location: checkout.php');
    }
    // form to input payment details
    echo "<h1>Payment details</h1>";
    echo "<form method='post' action='checkout.php'>";
    echo "<label for='cardnumber'>Card number</label>";
    echo "<input type='text' name='cardnumber' id='cardnumber' required='required'>";
    echo "<label for='cardholder'>Card holder</label>";
    echo "<input type='text' name='cardholder' id='cardholder' required='required'>";
    echo "<label for='expirationdate'>Expiration date</label>";
    echo "<input type='text' name='expirationdate' id='expirationdate' required='required'>";
    echo "<label for='cvv'>CVV</label>";
    echo "<input type='text' name='cvv' id='cvv' required='required'>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";

    echo "<a href='checkout.php?updatedelivery'>Back to delivery</a>";
} else {
    // order summary
    echo "<h1>Order summary</h1>";
    echo "<p>Order ID: " . $_SESSION['order']['orderid'] . "</p>";
    echo "<p>Username: " . $_SESSION['order']['username'] . "</p>";
    echo "<p>Total price: " . $_SESSION['order']['total_price'] / 100 . "€</p>";
    // TODO: maybe list also the cart
    echo "<p>Delivery address: " . $_SESSION['delivery']['firstname'] . " " . $_SESSION['delivery']['lastname'] . "</p>";
    echo "<p>Delivery address: " . $_SESSION['delivery']['address'] . "</p>";
    echo "<p>Delivery address: " . $_SESSION['delivery']['city'] . "</p>";
    echo "<p>Delivery address: " . $_SESSION['delivery']['postalcode'] . "</p>";
    echo "<p>Delivery address: " . $_SESSION['delivery']['country'] . "</p>";
    echo "<p>Payment card number: " . $_SESSION['payment']['cardnumber'] . "</p>";
    echo "<p>Payment card holder: " . $_SESSION['payment']['cardholder'] . "</p>";
    echo "<p>Payment card expiration date: " . $_SESSION['payment']['expirationdate'] . "</p>";
    echo "<p>Payment card CVV: " . $_SESSION['payment']['cvv'] . "</p>";
    echo "<a href='checkout.php?updatepayment'>Back to payment</a>";
    echo "<a href='placeorder.php'>Continue</a>";
    echo "<a href='index.php'>Back to Home</a>";
}


?>
</body>
</html>