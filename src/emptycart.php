<?php
session_start_or_expire();
// reset cart
$_SESSION['cart'] = null;
header('Location: cart.php');
?>
