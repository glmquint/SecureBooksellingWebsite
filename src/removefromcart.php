<?php
    require_once 'utils/dbUtils.php';
    session_start_or_expire();
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        $_SESSION['errorMsg'] = "CSRF token mismatch";
        header('Location: cart.php');
        exit();
    }
    $bookid = $_POST['id'] ?? "";
    #echo "book id: " . htmlspecialchars($bookid) . " removed from cart (WIP)";
    if (!isset($_SESSION['cart'])){
        $_SESSION['cart'] = array();
    }
    if (!isset($_SESSION['cart'][$bookid])){
        $_SESSION['cart'][$bookid] = 0;
    }
    if($_SESSION['cart'][$bookid] > 0){
        $_SESSION['cart'][$bookid] -= 1;
    }
    if($_SESSION['cart'][$bookid] == 0){
        unset($_SESSION['cart'][$bookid]);
        if(count($_SESSION['cart']) == 0){
            unset($_SESSION['cart']);
        }
    }
    // dump content of cart
    //echo "<pre>";
    //print_r($_SESSION['cart']);
    //echo "</pre>";
    header('Location: cart.php');

    ?>
