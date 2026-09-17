<?php
session_start();
require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($fullname !== '' && $email !== false && strlen($password) >= 4) {
        $check = $mysqli->prepare("SELECT id FROM customers WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $exists = $check->get_result()->num_rows > 0;
        $check->close();

        if ($exists) {
            echo "<script>alert('This email is already registered. Please log in.'); window.location='login.php';</script>";
            exit();
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO customers (full_name, email, phone, password) VALUES (?, ?, '', ?)");
        $stmt->bind_param("sss", $fullname, $email, $password_hash);

        if ($stmt->execute()) {
            $_SESSION['user'] = [
                'id' => $stmt->insert_id,
                'full_name' => $fullname,
                'email' => $email
            ];
            $stmt->close();
            header("Location: home.php");
            exit();
        } else {
            echo "<script>alert('Registration failed. Please try again.'); window.history.back();</script>";
            exit();
        }
    } else {
        echo "<script>alert('Please enter your full name, valid email, and at least 4 characters for password.'); window.history.back();</script>";
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
