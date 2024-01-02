<!DOCTYPE html>
<html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>
<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();

$regexes = [
    'firstname' => "[\\-'A-Z a-zÀ-ÿ]+",
    'lastname' => "[\\-'A-Z a-zÀ-ÿ]+",
    'address' => "[\\-'A-Z a-zÀ-ÿ0-9.,]+",
    'city' => "[\\-'A-Z a-zÀ-ÿ.]+",
    'postalcode' => "\d+",
    'country' => "[\\-'A-Z a-z]+",
    'cvv' => "\d{3}",
    'cardholder' => "[\\-'A-Z a-zÀ-ÿ.]+",
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

if (!isset($_SESSION['email'])) {
    header('Location: login.php?redirect=checkout.php');
    exit();
} elseif (!isset($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
} elseif (!isset($_SESSION['delivery']) || isset($_GET['updatedelivery'])) {
// recompute total price
    $db = new DBConnection();
    $total_price = 0;
    // if cart is not an array, get back to index
    if (!is_array($_SESSION['cart'])) {
        header('Location: index.php');
        exit();
    }
    foreach ($_SESSION['cart'] as $bookid => $quantity) {
        $db->stmt = $db->conn->prepare("SELECT * FROM books WHERE id = ?");
        $db->stmt->bind_param("i", $bookid);
        $db->stmt->execute();
        $result = mysqli_stmt_get_result($db->stmt);
        $row = mysqli_fetch_array($result);
        if (!$row) {
            performLog("Error", "Book not found in checkout", array("bookid" => $bookid));
            header('Location: index.php');
            exit();
        }
        $quantity = $_SESSION['cart'][$row['id']];
        $total_price += $row['price'] * $quantity;
    }

    $_SESSION['order'] = array();
    $_SESSION['order'] = [
        'orderid' => random_int(100000, 999999),
        'cart' => $_SESSION['cart'],
        'total_price' => $total_price,
        'email' => $_SESSION['email'],
        'status' => 'in transit'
    ];

    //echo print_r($_SESSION['order']);

    // form to input delivery address
    echo "<h1>Delivery address</h1>";
    echo "<form method='post' action='checkout.php'>";
    echo "<label for='firstname'>First name</label>";
    $firstname = $_SESSION['delivery']['firstname'] ?? '';
    echo "<input type='text' name='firstname' id='firstname' required='required' placeholder='Abbie' pattern=" . $regexes['firstname'] . " value='" . $firstname . "'>";
    echo "<label for='lastname'>Last name</label>";
    $lastname = $_SESSION['delivery']['lastname'] ?? '';
    echo "<input type='text' name='lastname' id='lastname' required='required' placeholder='Bernstein' pattern=" . $regexes['lastname'] . " value='" . $lastname . "'>";
    echo "<label for='address'>Address</label>";
    $address = $_SESSION['delivery']['address'] ?? '';
    echo "<input type='text' name='address' id='address' required='required' placeholder='5th Avenue' pattern=" . $regexes['address'] . " value='" . $address . "'>";
    echo "<label for='city'>City</label>";
    $city = $_SESSION['delivery']['city'] ?? '';
    echo "<input type='text' name='city' id='city' required='required' placeholder='New York' pattern=" . $regexes['city'] . " value='" . $city . "'>";
    echo "<label for='postalcode'>Postal code</label>";
    $postalcode = $_SESSION['delivery']['postalcode'] ?? '';
    echo "<input type='text' name='postalcode' id='postalcode' required='required' placeholder='10128' pattern=" . $regexes['postalcode'] . " value='" . $postalcode . "'>";
    echo "<label for='country'>Country</label>";
    $country = $_SESSION['delivery']['country'] ?? '';
    echo "<input type='text' name='country' id='country' required='required' placeholder='United States' pattern=" . $regexes['country'] . " value='" . $country . "'>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";

    echo "<a href='cart.php'>Back to cart</a>";
    unset($_SESSION['delivery']);
    if (all(array_map(function ($field) {
        return isset($_POST[$field]);
    }, ['firstname', 'lastname', 'address', 'city', 'postalcode', 'country']))) {
    //if (isset($_POST['firstname']) && isset($_POST['lastname']) && isset($_POST['address']) && isset($_POST['city']) && isset($_POST['postalcode']) && isset($_POST['country'])) {
        // if any of the delivery fields doesn't comply with its regex, show an error message
        if (any(array_map(function ($field) use ($regexes) {
            return !preg_match("/" . $regexes[$field] . "/", $_POST[$field]);
        }, array_keys($regexes)))) {
            $_SESSION['errorMsg'] = "Invalid delivery address";
            header('Location: checkout.php');
            exit();
        }

        $_SESSION['delivery'] = array();
        $_SESSION['delivery'] = [
            'firstname' => htmlspecialchars($_POST['firstname']) ?? '',
            'lastname' => htmlspecialchars($_POST['lastname']) ?? '',
            'address' => htmlspecialchars($_POST['address']) ?? '',
            'city' => htmlspecialchars($_POST['city']) ?? '',
            'postalcode' => htmlspecialchars($_POST['postalcode']) ?? '',
            'country' => htmlspecialchars($_POST['country']) ?? '',
        ];
        header('Location: checkout.php');
    }
} elseif (!isset($_SESSION['payment']) || isset($_GET['updatepayment'])) {
    //echo print_r($_SESSION['delivery']);
    unset($_SESSION['payment']);
    if (isset($_POST['cardnumber']) && isset($_POST['cardholder']) && isset($_POST['expirationdate']) && isset($_POST['cvv'])) {
        $_SESSION['payment'] = array();
        $_SESSION['payment'] = [
            'cardnumber' => htmlspecialchars($_POST['cardnumber']) ?? '',
            'cardholder' => htmlspecialchars($_POST['cardholder']) ?? '',
            'expirationdate' => htmlspecialchars($_POST['expirationdate']) ?? '',
            'cvv' => htmlspecialchars($_POST['cvv']) ?? '',
        ];
        header('Location: checkout.php');
    }
    // form to input payment details
    echo "<h1>Payment details</h1>";
    echo "<form method='post' action='checkout.php'>";
    echo "<label for='cardnumber'>Card number</label>";
    $cardnumber = $_SESSION['delivery']['cardnumber'] ?? '';
    echo "<input type='text' name='cardnumber' id='cardnumber' required='required' placeholder='XXXX-XXXX-XXXX-XXXX' pattern=\"\b\d{4}[\\- ]?\d{4}[\\- ]?\d{4}[\\- ]?\d{4}\b\" value='" . $cardnumber . "'>";
    echo "<label for='cardholder'>Card holder</label>";
    $cardholder = $_SESSION['delivery']['cardholder'] ?? '';
    echo "<input type='text' name='cardholder' id='cardholder' required='required' placeholder='Abbie Bernstein' patter=" . $regexes['cardholder'] . "value='" . $cardholder . "'>";
    echo "<label for='expirationdate'>Expiration date</label>";
    $expirationdate = $_SESSION['delivery']['expirationdate'] ?? '';
    echo "<input type='date' name='expirationdate' id='expirationdate' required='required' value='" . $expirationdate . "'>";
    echo "<label for='cvv'>CVV</label>";
    $cvv = $_SESSION['delivery']['cvv'] ?? '';
    echo "<input type='text' name='cvv' id='cvv' required='required' placeholder='123' pattern=" . $regexes['cvv'] . "value='" . $cvv . "'>";
    echo "<button type='submit'>Submit</button>";
    echo "</form>";

    echo "<a href='checkout.php?updatedelivery'>Back to delivery</a>";
} else {
    // order summary
    echo "<h1>Order summary</h1>";
    echo "<p>Order ID: " . htmlspecialchars($_SESSION['order']['orderid']) . "</p>";
    echo "<p>Email: " . htmlspecialchars($_SESSION['order']['email']) . "</p>";
    echo "<p>Total price: " . $_SESSION['order']['total_price'] / 100 . "€</p>";
    // TODO: maybe list also the cart
    echo "<p>Delivery address: " . htmlspecialchars($_SESSION['delivery']['firstname']) . " " . htmlspecialchars($_SESSION['delivery']['lastname']) . "</p>";
    echo "<p>Delivery address: " . htmlspecialchars($_SESSION['delivery']['address']) . "</p>";
    echo "<p>Delivery address: " . htmlspecialchars($_SESSION['delivery']['city']). "</p>";
    echo "<p>Delivery address: " . htmlspecialchars($_SESSION['delivery']['postalcode']). "</p>";
    echo "<p>Delivery address: " . htmlspecialchars($_SESSION['delivery']['country']). "</p>";
    echo "<p>Payment card number: " . htmlspecialchars($_SESSION['payment']['cardnumber']). "</p>";
    echo "<p>Payment card holder: " . htmlspecialchars($_SESSION['payment']['cardholder']). "</p>";
    echo "<p>Payment card expiration date: " . htmlspecialchars($_SESSION['payment']['expirationdate']). "</p>";
    echo "<p>Payment card CVV: " . htmlspecialchars($_SESSION['payment']['cvv']). "</p>";
    echo "<a href='checkout.php?updatepayment'>Back to payment</a>";
    echo "<a href='placeorder.php'>Continue</a>";
    echo "<a href='index.php'>Back to Home</a>";
}

include 'utils/messages.php';

?>
</body>
</html>