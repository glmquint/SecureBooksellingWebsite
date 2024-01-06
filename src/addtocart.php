<?php
    require_once 'utils/dbUtils.php';
    session_start_or_expire();
    $bookid = $_POST['id'] ?? "";
    // echo "book id: " . $bookid . " added to cart (WIP)";
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])){
        $_SESSION['cart'] = array();
    }
    if (!isset($_SESSION['cart'][$bookid])){
        $_SESSION['cart'][$bookid] = 0;
    }
    $_SESSION['cart'][$bookid] += 1;
    // dump content of cart
    // echo "<pre>";
    // print_r($_SESSION['cart']);
    // echo "</pre>";
    header('Location: cart.php');

    ?>
