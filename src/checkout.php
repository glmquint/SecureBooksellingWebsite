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

$regexes = [
    'firstname' => "[\\-'A-Z a-zÀ-ÿ]+",
    'lastname' => "[\\-'A-Z a-zÀ-ÿ]+",
    'address' => "[\\-'A-Z a-zÀ-ÿ0-9.,]+",
    'city' => "[\\-'A-Z a-zÀ-ÿ.]+",
    'postalcode' => "\d+",
    'country' => "[\\-'A-Z a-z]+",
    'cardnumber' => "\b\d{4}[\\- ]?\d{4}[\\- ]?\d{4}[\\- ]?\d{4}\b",
    'cardholder' => "[\\-'A-Z a-zÀ-ÿ.]+",
    'cvv' => "\d{3}",
];

function all($iterable) {
    return array_reduce($iterable, function($carry, $item) {
        return $carry && $item;
    }, true);
}
function any($iterable) {
    return array_reduce($iterable, function($carry, $item) {
        return $carry || $item;
    }, false);
}

if (!isset($_SESSION['email']) || !is_string($_SESSION['email'])) {
    performLog("Info", "User not logged in while in checkout", array());
    header('Location: login.php?redirect=checkout.php');
    exit();
} elseif (!isset($_SESSION['cart']) || !is_array($_SESSION['cart']) || count($_SESSION['cart']) == 0) {
    performLog("Info", "Cart not set while in checkout", array());
    $_SESSION['errorMsg'] = 'Something went wrong with your request';
    header('Location: index.php');
    exit();
} elseif (!isset($_SESSION['delivery']) || isset($_GET['updatedelivery'])) {
    $_SESSION['bookdetails'] = array();
    // recompute total price
    $total_price = 0;
    try {
        $db = new DBConnection();
        foreach ($_SESSION['cart'] as $bookid => $quantity) {
            $db->stmt = $db->conn->prepare("SELECT * FROM books WHERE id = ?");
            $db->stmt->bind_param("i", $bookid);
            $db->stmt->execute();
            $result = mysqli_stmt_get_result($db->stmt);
            $row = mysqli_fetch_array($result);
            if (!$row) {
                performLog("Error", "Book not found in checkout", array("bookid" => $bookid));
                $_SESSION['errorMsg'] = 'Something went wrong with your request';
                header('Location: index.php');
                exit();
            }
            $_SESSION['bookdetails'][$bookid] = $row;
            $quantity = $_SESSION['cart'][$row['id']];
            $total_price += $row['price'] * $quantity;
        }
    } catch (mysqli_sql_exception $e) {
        performLog("Error", "Failed to connect to DB in checkout.php", array("error" => $e->getCode(), "message" => $e->getMessage()));
        session_unset();
        session_destroy();
        header('Location: 500.html');
    }

    $_SESSION['order'] = array();
    $_SESSION['order'] = [
        'orderid' => random_int(100000, 999999),
        'cart' => $_SESSION['cart'],
        'total_price' => $total_price,
        'email' => $_SESSION['email'],
        'status' => 'in transit'
    ];

    // form to input delivery address
    echo "<h1>Shipping information</h1>";
    echo "<form method='post' action='checkout.php'>";
    echo "<label for='firstname'>First name</label>";
    $firstname = $_SESSION['delivery']['firstname'] ?? '';
    echo "<input type='text' name='firstname' id='firstname' required='required' placeholder='Abbie' pattern=\"" . $regexes['firstname'] . "\" value='" . htmlspecialchars($firstname) . "'>";
    echo "<label for='lastname'>Last name</label>";
    $lastname = $_SESSION['delivery']['lastname'] ?? '';
    echo "<input type='text' name='lastname' id='lastname' required='required' placeholder='Bernstein' pattern=\"" . $regexes['lastname'] . "\" value='" . htmlspecialchars($lastname) . "'>";
    echo "<label for='address'>Address</label>";
    $address = $_SESSION['delivery']['address'] ?? '';
    echo "<input type='text' name='address' id='address' required='required' placeholder='5th Avenue' pattern=\"" . $regexes['address'] . "\" value='" . htmlspecialchars($address) . "'>";
    echo "<label for='city'>City</label>";
    $city = $_SESSION['delivery']['city'] ?? '';
    echo "<input type='text' name='city' id='city' required='required' placeholder='New York' pattern=\"" . $regexes['city'] . "\" value='" . htmlspecialchars($city) . "'>";
    echo "<label for='postalcode'>Postal code</label>";
    $postalcode = $_SESSION['delivery']['postalcode'] ?? '';
    echo "<input type='text' name='postalcode' id='postalcode' required='required' placeholder='10128' pattern=\"" . $regexes['postalcode'] . "\" value='" . htmlspecialchars($postalcode) . "'>";
    echo "<label for='country'>Country</label>";
    $country = $_SESSION['delivery']['country'] ?? '';
    echo "<input type='text' name='country' id='country' required='required' placeholder='United States' pattern=\"" . $regexes['country'] . "\" value='" . htmlspecialchars($country) . "'>";
    echo "<br>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";

    echo "<a href='cart.php'>Back to cart</a>";
    unset($_SESSION['delivery']);
    $delivery_fields = ['firstname', 'lastname', 'address', 'city', 'postalcode', 'country'];
    if (all(array_map(function ($field) {
        return isset($_POST[$field]);
    }, $delivery_fields))) {
        // if any of the delivery fields doesn't comply with its regex, show an error message
        if (any(array_map(function ($field) use ($regexes) {
            return !preg_match("/" . $regexes[$field] . "/", $_POST[$field]);
        }, $delivery_fields))) {
            performLog("Warning", "Invalid delivery information", array("preg_matched" => array_map(function ($field) use ($regexes) {
                return !preg_match("/" . $regexes[$field] . "/", $_POST[$field]);
            }, $delivery_fields)));
            $_SESSION['errorMsg'] = "Invalid delivery information";
            header('Location: checkout.php');
            exit();
        }

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
    // form to input payment details
    echo "<h1>Payment details</h1>";
    echo "<form method='post' action='checkout.php'>";
    echo "<label for='cardnumber'>Card number</label>";
    $cardnumber = $_SESSION['payment']['cardnumber'] ?? '';
    echo "<input type='text' name='cardnumber' id='cardnumber' required='required' placeholder='XXXX-XXXX-XXXX-XXXX' pattern=\"" . $regexes['cardnumber'] . "\" value='" . htmlspecialchars($cardnumber) . "'>";
    echo "<label for='cardholder'>Card holder</label>";
    $cardholder = $_SESSION['payment']['cardholder'] ?? '';
    echo "<input type='text' name='cardholder' id='cardholder' required='required' placeholder='Abbie Bernstein' patter=\"" . $regexes['cardholder'] . "\" value='" . htmlspecialchars($cardholder) . "'>";
    echo "<label for='expirationdate'>Expiration date</label>";
    $expirationdate = $_SESSION['payment']['expirationdate'] ?? '';
    echo "<input type='month' name='expirationdate' id='expirationdate' required='required' value='" . htmlspecialchars($expirationdate) . "'>";
    echo "<label for='cvv'>CVV</label>";
    $cvv = $_SESSION['payment']['cvv'] ?? '';
    echo "<input type='text' name='cvv' id='cvv' required='required' placeholder='123' pattern=\"" . $regexes['cvv'] . "\" value='" . htmlspecialchars($cvv) . "'>";
    echo "<br>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";
    unset($_SESSION['payment']);
    //echo print_r($_SESSION['payment']);
    $payment_fields = ['cardnumber', 'cardholder', 'cvv'];
    if (all(array_map(function ($field) {
        return isset($_POST[$field]);
    }, $payment_fields))) {
        if (any(array_map(function ($field) use ($regexes) {
            return !preg_match("/" . $regexes[$field] . "/", $_POST[$field]);
        }, $payment_fields))) {
            performLog("Warning", "Invalid payment information", array("preg_matched" => array_map(function ($field) use ($regexes) {
                return !preg_match("/" . $regexes[$field] . "/", $_POST[$field]);
            }, $payment_fields)));
            $_SESSION['errorMsg'] = "Invalid payment information";
            header('Location: checkout.php');
            exit();
        }
        $_SESSION['payment'] = array();
        $_SESSION['payment'] = [
            'cardnumber' => $_POST['cardnumber'] ?? '',
            'cardholder' => $_POST['cardholder'] ?? '',
            'expirationdate' => $_POST['expirationdate'] ?? '',
            'cvv' => $_POST['cvv'] ?? '',
        ];
        header('Location: checkout.php');
    }

    echo "<a href='checkout.php?updatedelivery'>Back to delivery</a>";
} else {
    // order summary
    echo "<header>";
    echo "<h1>Order summary</h1>";
    echo "<nav>";
    echo "<a href='index.php'>Back to Home</a>";
    echo "</nav>";
    echo "</header>";

    echo "<h3>Delivery summary</h3>";
    echo "<table>";

    echo "<tr><td>Order ID: </td><td>" . htmlspecialchars($_SESSION['order']['orderid']) . "</td></tr>";
    echo "<tr><td>Email: </td><td>" . htmlspecialchars($_SESSION['order']['email']) . "</td></tr>";
    echo "<tr><td>Firstname Lastname: </td><td>" . htmlspecialchars($_SESSION['delivery']['firstname']) . " " . htmlspecialchars($_SESSION['delivery']['lastname']) . "</td></tr>";
    echo "<tr><td>Delivery address:  </td><td>" . htmlspecialchars($_SESSION['delivery']['address']) . "</td></tr>";
    echo "<tr><td>Delivery city:  </td><td>" . htmlspecialchars($_SESSION['delivery']['city']) . "</td></tr>";
    echo "<tr><td>Delivery postalcode:  </td><td>" . htmlspecialchars($_SESSION['delivery']['postalcode']) . "</td></tr>";
    echo "<tr><td>Delivery country:  </td><td>" . htmlspecialchars($_SESSION['delivery']['country']) . "</td></tr>";

    echo "</table>";
    echo "<a href='checkout.php?updatedelivery'>Back to delivery</a>";

    echo "<h3>Payment summary</h3>";
    echo "<table>";
    $cart_obfuscated = substr_replace(str_repeat('*', strlen($_SESSION['payment']['cardnumber'])), substr($_SESSION['payment']['cardnumber'],-2), -2);

    echo "<tr><td>Payment card number: </td><td>" . htmlspecialchars($cart_obfuscated). "</td></tr>";
    echo "<tr><td>Payment card holder: </td><td>" . htmlspecialchars($_SESSION['payment']['cardholder']). "</td></tr>";
    echo "<tr><td>Payment card expiration date: </td><td>" . htmlspecialchars($_SESSION['payment']['expirationdate']). "</td></tr>";
    echo "</table>";
    echo "<a href='checkout.php?updatepayment'>Back to payment</a>";
    // echo "<p>Payment card CVV: " . $_SESSION['payment']['cvv']). "</p>";
    echo "<br>";
    echo "<h3>Books summary</h3>";
    echo "<table>";
    echo "<tr>";
    echo "<th>Book name</th>";
    echo "<th>Quantity</th>";
    echo "</tr>";
    $total_price = 0;
    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $row = $_SESSION['bookdetails'][$bookid];
        if (!$row) {
            performLog("Error", "Book not found in checkout", array("bookid" => $bookid));
            $_SESSION['errorMsg'] = 'Something went wrong with your request';
            header('Location: index.php');
            exit();
        }
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($quantity) . "</td>";
        echo "</tr>";
        $total_price += $row['price'] * $quantity;
    }
    $_SESSION['order']['total_price'] = $total_price;
    echo "</table>";
    echo "<b>Total price: " . htmlspecialchars($_SESSION['order']['total_price']) / 100 . "€</b>";
    echo "<br>";
    echo "<form method='post' action='placeorder.php'>";
    echo "<input type='hidden' name='csrf_token' value='" . $_SESSION['csrf_token'] . "' readonly='readonly' >";
    echo "<button type='submit'>Continue</button>";
    echo "</form>";
}

include '../utils/messages.php';

?>
</body>
</html>