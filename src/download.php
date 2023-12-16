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
$db = new DBConnection();

$user_id = getUserID($_SESSION['username']);
$stmt = $db->conn->prepare("SELECT book,title FROM purchases INNER JOIN books ON book=id WHERE buyer = ? AND book = ?");
$stmt->bind_param("ii", $user_id, $_GET['id']);
$stmt->execute();
$result = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_array($result);
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=" . $row['title'] . ".pdf");
@readfile('../ebooks/' . $row['title'] . '.pdf');
?>