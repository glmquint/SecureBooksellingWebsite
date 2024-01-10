<?php
require_once '../utils/dbUtils.php';
session_start_or_expire();
// reset cart
unset($_SESSION['cart']);
header('Location: cart.php');
?>
