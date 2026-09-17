<?php
session_start();

$type = $_GET['type'] ?? '';

// Unset session variables
$_SESSION = array();

// Destroy session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

if ($type === 'seller') {
    header("Location: shoplogin.php");
} else {
    header("Location: login.php");
}
exit();
?>
