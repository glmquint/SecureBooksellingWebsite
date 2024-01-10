<?php
    require_once '../utils/dbUtils.php';
    session_start_or_expire();
    $bookid = $_POST['id'] ?? "";
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        performLog("Error", "CSRF token mismatch", array("token" => $_POST['csrf_token']));
        $_SESSION['errorMsg'] = "Something went wrong with your request";
        header('Location: cart.php');
        exit();
    }
    if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])){
        $_SESSION['cart'] = array();
    }
    if (!isset($_SESSION['cart'][$bookid]) || !is_int($_SESSION['cart'][$bookid])){
        $_SESSION['cart'][$bookid] = 0;
    }
    $_SESSION['cart'][$bookid] += 1;
    header('Location: cart.php');

?>
