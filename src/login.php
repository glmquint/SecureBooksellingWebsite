<?php
require_once '../utils/dbUtils.php';
require_once '../utils/Logger.php';
session_start_or_expire();

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();
$REMEMBERME_KEY = $_ENV['REMEMBERME_KEY'];
$CIPHER = "aes-128-gcm";

if (isset($_COOKIE['rememberme']) && $_COOKIE['rememberme'] != "") {
    $rememberme_token = unserialize(base64_decode($_COOKIE['rememberme']));
    $iv = $rememberme_token[0];
    $enc_email = $rememberme_token[1];
    $tag = $rememberme_token[2];
    try{
        $email = openssl_decrypt($enc_email, $CIPHER, $REMEMBERME_KEY, $options=0, $iv, $tag);
        if(!getUser($email)){
            performLog("Warning", "User not found in database", array("email" => $email));
            header('Location: logout.php');
            exit();
        }
        $_SESSION['email'] = $email;
        performLog("Info", "User logged in via remember me cookie", array("email" => $_SESSION['email']));
    } catch (Exception $e){
        performLog("Error", "Failed to decrypt remember me cookie", array("cookie" => $rememberme_token, "cipher" => $CIPHER));
    }
    // To Redirect the user to the home page or another secure page
    if (isset($_REQUEST['redirect']) && !str_contains($_REQUEST['redirect'], "\n")) {
        header('Location: ' . $_REQUEST['redirect']);
        exit();
    }
    header('Location: index.php');
    exit();
}
if (isset($_POST['email']) || isset($_POST['password'])) {
    if (!is_string($_POST['email'])|| !is_string($_POST['password'])) {
        performLog("Error", "Invalid email or password, not a string", array("mail" => $_POST['email']));
        $_SESSION['errorMsg'] = "Something went wrong with your request, please try again later with different email or password";
        header("Location: login.php");
        exit();
    }
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] != $_SESSION['csrf_token']) {
        performLog("Error", "CSRF token mismatch", array("token" => $_POST['csrf_token']));
        $_SESSION['errorMsg'] = "Something went wrong with your request";
        header('Location: index.php');
        exit();
    }
    // Get username and password from the form submitted by the user
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    // Assume $username and $password are the submitted credentials
    $userValidity = verifyLogin($email, $password);
    if ($userValidity) {
        if ($userValidity == 1) {
            performLog("Info", "User didn't log in because it hasn't verified its email yet", array("email" => $email));
            $_SESSION['errorMsg'] = "Verify your email first!";
            header('Location: index.php');
            exit();
        }else if ($userValidity == 2) {
            // Correct login
            performLog("Info", "User logged in", array("email" => $email));
            $_SESSION['email'] = $email;
            // change session id to prevent session fixation
            session_regenerate_id();
            if(isset($_POST['remember']) && $_POST['remember'] === "on"){
                // encrypt the email and store it in a cookie
                if (in_array($CIPHER, openssl_get_cipher_methods())) {
                    $ivlen = openssl_cipher_iv_length($CIPHER);
                    $iv = openssl_random_pseudo_bytes($ivlen);
                    $enc_email = openssl_encrypt($email, $CIPHER, $REMEMBERME_KEY, $options = 0, $iv, $tag);
                    setcookie('rememberme', base64_encode(serialize([$iv, $enc_email, $tag])), time() + (86400 * $_ENV['REMEMBERME_LIFETIME']), '/', '', true, true); // 86400 = 1 day
                } else {
                    performLog("Error", "Cipher not supported", array("cipher" => $CIPHER));
                }
            }
            // To Redirect the user to the home page or another secure page
            if (isset($_REQUEST['redirect']) && !str_contains($_REQUEST['redirect'], "\n")) {
                header('Location: ' . $_REQUEST['redirect']);
                exit();
            }
            header('Location: index.php');
            exit();
        }
    } else {
        // Incorrect login
        performLog("Warning", "Incorrect login attempt ", array("email" => $email));
        $_SESSION['errorMsg'] = "Invalid login credentials! User could be suspended if multiple login failures occurred, please try later";
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
    <!-- show a form to login -->
    <header>
        <h1>Login</h1>
        <nav>
            <a href="index.php">Back to Home</a>
        </nav>
    </header>
    <?php include '../utils/messages.php' ?>
    <br>
    <form method="post" action="login.php">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?>" readonly="readonly" >
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required="required">
        <label for="password">Password</label>
        <input type="password" required="required" name="password" id="password">
        <?php if(isset($_GET['redirect'])) {
            echo "<input type='hidden' value='" . htmlspecialchars($_GET['redirect']) . "' name='redirect'>";
        }
        ?>
        <article>
            <label for="remember">Remember me for 30 days</label>
            <input type="checkbox" name="remember" id="remember">
            <p>Please note that there are potential security concerns related to leaving your account logged in for long periods of time; especially when using an insecure, shared or public device.</p>
        </article>
        <button type="submit" name="login_btn">Login</button>

    </form>
    <p><a href="resetpassword.php">Forgot password?</a></p>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</body>