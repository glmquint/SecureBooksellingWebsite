<?php
require_once 'utils/dbUtils.php';
session_start();
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$db = mysqli_connect('localhost', 'root', 'rootroot', 'securebooksellingdb');
$user_id = getUserID($db, $_SESSION['username']);
$stmt = mysqli_prepare($db, "SELECT book,title FROM purchases INNER JOIN books ON book=id WHERE buyer = ? AND book = ?");
mysqli_stmt_bind_param($stmt, "ii", $user_id, $_GET['id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
$db->close();
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=" . $row['title'] . ".pdf");
@readfile('../ebooks/' . $row['title'] . '.pdf');
?>