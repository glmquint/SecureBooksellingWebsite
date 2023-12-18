<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();
if (!isset($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}
if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit();
}
$db = new DBConnection();

$user_id = getUserID($_SESSION['email']);
$db->stmt = $db->conn->prepare("SELECT book,title FROM purchases INNER JOIN books ON book=id WHERE buyer = ? AND book = ?");
$db->stmt->bind_param("ii", $user_id, $_GET['id']);
$db->stmt->execute();
$result = mysqli_stmt_get_result($db->stmt);
$row = mysqli_fetch_array($result);
if (!$row){
    performLog("Error", "Error while retrieving a book", ["book_id" => $_GET['id'], "user_id" => $user_id]);
    echo "Error while retrieving the book";
    exit();
}
performLog("Info", "EBook downloaded", ["book_id" => $_GET['id'], "user_id" => $user_id]);
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=" . $row['title'] . ".pdf");
@readfile('../ebooks/' . $row['title'] . '.pdf');
?>