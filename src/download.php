<?php
require_once 'utils/dbUtils.php';
session_start_or_expire();
// Check if the user is logged in and if email is a string for type safety
if (!isset($_SESSION['email']) || !is_string($_SESSION['email'])) {
    header('Location: login.php');
    exit();
}
// Check if the book id is set and if it is a valid number, no float or scientific notation allowed
if (!isset($_GET['id']) || !is_numeric($_GET['id']) || strpos($_GET['id'], '.') !== false || strpos($_GET['id'], 'e') !== false) {
    $_SESSION['errorMsg'] = 'something went wrong with your request, please try again';
    performLog("Error", "Error while retrieving a book", ["book_id" => $_GET['id'], "email" => $_SESSION['email']]);
    header('Location: index.php');
    exit();
}
// Connect to the database and retrieve the book information based on the id
$db = new DBConnection();

$user_id = getUserID($_SESSION['email']);
$db->stmt = $db->conn->prepare("SELECT book,title FROM purchases INNER JOIN books ON book=id WHERE buyer = ? AND book = ?");
$db->stmt->bind_param("ii", $user_id, $_GET['id']);
$db->stmt->execute();
$result = mysqli_stmt_get_result($db->stmt);
$row = mysqli_fetch_array($result);
if (!$row){
    performLog("Error", "Error while retrieving a book", ["book_id" => $_GET['id'], "user_id" => $user_id]);
    $_SESSION['errorMsg'] = "Something went wrong with your request!";
    header('Location: books.php');
}
performLog("Info", "EBook downloaded", ["book_id" => $_GET['id'], "user_id" => $user_id]);
header("Content-type: application/pdf");
header("Content-Disposition: inline; filename=" . $row['title'] . ".pdf");
@readfile('../ebooks/' . $row['title'] . '.pdf');
?>