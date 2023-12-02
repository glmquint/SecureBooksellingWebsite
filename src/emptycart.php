<?php
session_start();
// reset cart
$_SESSION['cart'] = null;
header('Location: cart.php');
?>
