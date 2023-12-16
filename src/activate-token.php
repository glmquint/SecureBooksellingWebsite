<?php
require_once 'utils/dbUtils.php';
session_start();
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $userid = getUidFromToken($token);
    if($userid!=-1){
        if(deleteToken($token)) {
            if(activateAccount($userid)){
                $_SESSION['success'] = "Your account was successfully activated";
                $_SESSION['$userid'] = $userid;
                header('Location: activate-token.php');
                exit();
            }
            else{
                echo "failed activating account";
            }

        }
        else{
            echo "failed deleting token";
        }
    }
    else{
        // If 'token' is not incorrect, return a 404 error
        http_response_code(404);
        echo "Error 404: Page Not Found";
    }
}
else {
    if(!isset($_SESSION['success'])) {
        // If 'token' is not set, return a 404 error
        http_response_code(404);
        echo "Error 404: Page Not Found";
    }

}



?>

    <!DOCTYPE html>
    <html lang="en">
<head>
    <title>Secure Book selling website</title>
    <link rel="stylesheet" href="https://cdn.simplecss.org/simple.min.css">
</head>
<body>

<!-- if the user is logged in, show a message -->
<?php if (isset($_SESSION['success'])): ?>
    <div class="error success">
        <h3>
            <?php
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
        </h3>
    </div>

<?php endif ?>
    <a href="index.php">Back to Home</a>
</body>
<?php
