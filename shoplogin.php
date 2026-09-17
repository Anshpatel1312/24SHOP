<?php
session_start();
require_once __DIR__ . '/db.php';

/* LOGIN */
$error = "";

if (isset($_POST['login'])) {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if ($email === false || $password === '') {
        $error = "Please enter a valid Email and Password.";
    } else {
        $stmt = mysqli_prepare($conn, 'SELECT seller_id, email, password FROM seller_details WHERE email = ?');
        mysqli_stmt_bind_param($stmt, 's', $email);
        mysqli_stmt_execute($stmt);
        $row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        $password_is_valid = $row && password_verify($password, $row['password']);
        $legacy_password = $row && strlen($row['password']) < 60 && hash_equals($row['password'], $password);

        if ($password_is_valid || $legacy_password) {
            if ($legacy_password) {
                $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
                $upgrade_stmt = mysqli_prepare($conn, 'UPDATE seller_details SET password = ? WHERE seller_id = ?');
                mysqli_stmt_bind_param($upgrade_stmt, 'si', $new_password_hash, $row['seller_id']);
                mysqli_stmt_execute($upgrade_stmt);
                mysqli_stmt_close($upgrade_stmt);
            }

            $_SESSION['seller_id'] = (int) $row['seller_id'];
            $_SESSION['email'] = $row['email'];
            header('Location: admin.php');
            exit();
        } else {
            $error = "Invalid seller email or password.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Login UI</title>


    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" />

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Poppins, sans-serif;
        }

        body {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: url("https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1600") center/cover;
            overflow: hidden;
        }

        body::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, .55);
            backdrop-filter: blur(8px);
        }

        .container {
            position: relative;
            width: 900px;
            display: flex;
            flex-direction: column;
            gap: 30px;
            z-index: 2;
        }

        .login-box {
            position: relative;
            background: rgba(255, 255, 255, .15);
            backdrop-filter: blur(18px);
            border: 1px solid rgba(255, 255, 255, .3);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .4);
            overflow: hidden;
            transition: .4s;
        }

        .login-box:hover {
            transform: translateY(-6px);
        }

        .header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .header i {
            font-size: 55px;
            color: #0048ff;
        }

        .header h2 {
            font-size: 34px;
            color: aliceblue;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 18px 55px;
            border-radius: 15px;
            border: none;
            outline: none;
            font-size: 18px;
            background: white;
        }

        .input-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
            font-size: 20px;
        }

        .eye {
            left: auto !important;
            right: 20px;
            cursor: pointer;
        }

        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 20px 0;
        }

        .options label {
            color: white;
        }

        .options a {
            text-decoration: none;
            color: #0f7dff;
            font-weight: 500;
        }

        .login-btn {

            width: 100%;
            padding: 18px;
            font-size: 22px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            background: linear-gradient(45deg, #4CAF50, #7FFF00);
            color: white;
            font-weight: bold;
            transition: .3s;
        }

        .login-btn:hover {
            transform: scale(1.02);
            box-shadow: 0 15px 30px rgba(0, 255, 0, .4);
        }

        .register {

            background: rgba(255, 255, 255, .12);
            backdrop-filter: blur(15px);
            border-radius: 20px;
            padding: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .3);
            border: 1px solid rgba(255, 255, 255, .3);
        }

        .register h2 {
            font-size: 30px;
            margin-bottom: 10px;
            color: aqua;
        }

        .register p {
            color: white;
            opacity: .9;
        }

        .register button {

            padding: 18px 40px;
            font-size: 20px;
            border: none;
            border-radius: 12px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            transition: .3s;
        }

        .register button:hover {

            background: #0048ff;
            transform: translateY(-3px);

        }

        .keys {

            position: absolute;
            right: -40px;
            top: -20px;
            width: 220px;
            animation: float 3s infinite ease-in-out;

        }

        @keyframes float {

            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-15px);
            }

            100% {
                transform: translateY(0);
            }

        }

        .circle {

            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .1);
            animation: move 8s infinite;

        }

        .c1 {
            width: 150px;
            height: 150px;
            left: -50px;
            top: -50px;
        }

        .c2 {
            width: 250px;
            height: 250px;
            right: -80px;
            bottom: -100px;
        }

        @keyframes move {

            50% {
                transform: scale(1.2) rotate(180deg);
            }

        }

        @media(max-width:900px) {

            .container {
                width: 95%;
            }

            .register {
                flex-direction: column;
                gap: 20px;
                text-align: center;
            }

            .keys {
                display: none;
            }

        }
    </style>

</head>

<body>
    <div class="container">
        <div class="circle c1"></div>
        <div class="circle c2"></div>

        <div class="login-box">
            <img class="keys" src="https://cdn-icons-png.flaticon.com/512/3064/3064197.png" alt="Keys">

            <div class="header">
                <i class="fa-solid fa-store" style="font-size: 45px; color: #0048ff;"></i>
                <h2>Seller Login</h2>
            </div>

            <?php if (!empty($error)): ?>
                <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #fecaca; padding: 12px; border-radius: 10px; margin-bottom: 20px; font-size: 14px; text-align: center;">
                    <i class="fa-solid fa-triangle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="">
                <div class="input-group">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" placeholder="Seller Email" required>
                </div>

                <div class="input-group">
                    <i class="fa-solid fa-lock"></i>
                    <input id="password" type="password" name="password" placeholder="Password" required>
                    <i class="fa-solid fa-eye eye" id="toggle"></i>
                </div>

                <div class="options">
                    <label>
                        <input type="checkbox">
                        Remember Me
                    </label>

                    <a href="forgot-password.php?type=seller">Forgot Password?</a>
                </div>

                <button class="login-btn" name="login" type="submit">
                    LOGIN TO SELLER PORTAL →
                </button>
            </form>
            <div style="text-align: center; margin-top: 15px;">
                <a href="home.php" style="color: rgba(255,255,255,0.7); font-size: 13px; text-decoration: none;"><i class="fa-solid fa-arrow-left"></i> Back to 24SHOP Storefront</a>
            </div>
        </div>

        <div class="register">
            <div>
                <h2>Become a Seller</h2>
                <p>
                    Register your store on 24SHOP and reach thousands of customers today.
                </p>
            </div>
            <a href="registration.html" style="text-decoration: none;">
                <button type="button">REGISTER STORE</button>
            </a>
        </div>
    </div>

    <script>
        const toggle = document.getElementById("toggle");
        const pass = document.getElementById("password");

        toggle.onclick = function() {

            if (pass.type == "password") {

                pass.type = "text";
                toggle.classList.remove("fa-eye");
                toggle.classList.add("fa-eye-slash");

            } else {

                pass.type = "password";
                toggle.classList.remove("fa-eye-slash");
                toggle.classList.add("fa-eye");

            }

        }

        const box = document.querySelector(".login-box");

        document.addEventListener("mousemove", e => {

            let x = (window.innerWidth / 2 - e.pageX) / 40;
            let y = (window.innerHeight / 2 - e.pageY) / 40;

            box.style.transform = `rotateY(${x}deg) rotateX(${-y}deg)`;

        });

        document.addEventListener("mouseleave", () => {

            box.style.transform = "rotateY(0deg) rotateX(0deg)";

        });
    </script>

</body>

</html>