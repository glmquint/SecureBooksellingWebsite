<?php
session_start();
// reset cart
$_SESSION['cart'] = array();
header('Location: cart.php');
?>
