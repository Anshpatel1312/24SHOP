<?php
session_start();
require_once __DIR__ . '/db.php';

// Initialize message variables
$login_message = "";
$register_message = "";
$active_tab = "login"; // Default active tab

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // ------------------------------------
    // PROCESS LOGIN FORM
    // ------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'login') {
        $active_tab = "login";

        $email = filter_var(trim($_POST['loginEmail'] ?? ''), FILTER_VALIDATE_EMAIL);
        $password = $_POST['loginPassword'] ?? '';

        if ($email !== false && $password !== '') {
            $stmt = $mysqli->prepare('SELECT id, full_name, email, password FROM customers WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $customer = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            $password_is_valid = $customer && password_verify($password, $customer['password']);
            $legacy_password = $customer && strlen($customer['password']) < 60 && hash_equals($customer['password'], $password);

            if ($password_is_valid || $legacy_password) {
                if ($legacy_password) {
                    $new_password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $upgrade_stmt = $mysqli->prepare('UPDATE customers SET password = ? WHERE id = ?');
                    $upgrade_stmt->bind_param('si', $new_password_hash, $customer['id']);
                    $upgrade_stmt->execute();
                    $upgrade_stmt->close();
                }
                $_SESSION['user'] = [
                    'id' => $customer['id'],
                    'full_name' => $customer['full_name'],
                    'email' => $customer['email']
                ];
                header('Location: home.php');
                exit;
            } else {
                $login_message = "<div class='alert error'>Invalid email or password.</div>";
            }
        } else {
            $login_message = "<div class='alert error'>Please enter a valid email and password.</div>";
        }
    }

    // ------------------------------------
    // PROCESS REGISTRATION FORM
    // ------------------------------------
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $active_tab = "register";

        // Sanitize and collect input values
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $phone     = trim($_POST['phone'] ?? '');
        $password  = $_POST['password'] ?? '';
        $dob       = $_POST['dob'] ?? '';
        $address   = trim($_POST['address'] ?? '');
        $city      = trim($_POST['city'] ?? '');
        $state     = trim($_POST['state'] ?? '');
        $pincode   = trim($_POST['pincode'] ?? '');
        $gender    = trim($_POST['gender'] ?? '');

        if ($full_name !== '' && $email !== false && strlen($password) >= 4) {
            $check = $mysqli->prepare('SELECT id FROM customers WHERE email = ?');
            $check->bind_param('s', $email);
            $check->execute();
            $email_exists = $check->get_result()->num_rows > 0;
            $check->close();

            if ($email_exists) {
                $register_message = "<div class='alert error'>That email address is already registered.</div>";
            } else {
                $password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $mysqli->prepare('INSERT INTO customers (full_name, gender, email, phone, password, dob, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $stmt->bind_param('ssssssssss', $full_name, $gender, $email, $phone, $password, $dob, $address, $city, $state, $pincode);

                if ($stmt->execute()) {
                    $_SESSION['user'] = [
                        'id' => $stmt->insert_id,
                        'full_name' => $full_name,
                        'email' => $email
                    ];
                    $stmt->close();
                    header('Location: home.php');
                    exit;
                } else {
                    $register_message = "<div class='alert error'>Registration failed. Please try again.</div>";
                }
                $stmt->close();
            }
        } else {
            $register_message = "<div class='alert error'>Please enter your full name, a valid email, and a password with at least 4 characters.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Customer Registration</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: radial-gradient(circle at 12% 8%, rgba(255, 255, 255, .9), transparent 28%), linear-gradient(135deg, #dcecff 0%, #f7fbff 48%, #dff4ec 100%);
            color: #172033;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 32px 18px;
        }

        .container {
            background: rgba(255, 255, 255, 0.96);
            width: 100%;
            max-width: 1040px;
            padding: 10px;
            border: 1px solid rgba(148, 163, 184, 0.35);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(35, 67, 104, 0.14);
        }

        .auth-layout {
            display: grid;
            grid-template-columns: minmax(280px, .82fr) minmax(0, 1.18fr);
            min-height: 610px;
        }

        .welcome-panel {
            position: relative;
            overflow: hidden;
            padding: 42px 36px;
            border-radius: 14px;
            color: #ffffff;
            background: linear-gradient(145deg, #0e4f73, #1769aa 58%, #238b8b);
        }

        .welcome-panel::after {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            right: -90px;
            bottom: -85px;
            border: 32px solid rgba(255, 255, 255, .12);
            border-radius: 50%;
        }

        .brand {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand-mark {
            display: grid;
            width: 38px;
            height: 38px;
            place-items: center;
            border-radius: 11px;
            color: #0e4f73;
            background: #ffffff;
            font-size: 18px;
        }

        .welcome-panel h1 {
            max-width: 330px;
            margin-top: 82px;
            font-size: 38px;
            line-height: 1.08;
            letter-spacing: 0;
        }

        .welcome-panel p {
            max-width: 330px;
            margin-top: 18px;
            color: rgba(255, 255, 255, .8);
            line-height: 1.65;
        }

        .welcome-points {
            display: grid;
            gap: 14px;
            margin-top: 42px;
            color: #e7f7f7;
            font-size: 14px;
        }

        .welcome-points span {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .welcome-points i {
            color: #9de3c8;
        }

        .auth-content {
            padding: 34px 42px;
        }

        .tabs {
            display: flex;
            background: #edf2f7;
            border-radius: 10px;
            padding: 5px;
            margin-bottom: 28px;
        }

        .tab-btn {
            flex: 1;
            padding: 13px 12px;
            border: none;
            background: transparent;
            font-size: 16px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
        }

        .tab-btn.active {
            background: #ffffff;
            color: #1769aa;
            box-shadow: 0 3px 9px rgba(30, 64, 96, 0.12);
        }

        .form-box {
            display: none;
        }

        .form-box.active {
            display: block;
        }

        h2 {
            margin-bottom: 22px;
            color: #172033;
            font-size: 25px;
            letter-spacing: 0;
        }

        .form-group {
            margin-bottom: 17px;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: #495057;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            color: #172033;
            background: #fbfdff;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: #2388c7;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(35, 136, 199, 0.15);
        }

        textarea {
            resize: vertical;
            min-height: 88px;
        }

        .submit-btn {
            width: 100%;
            padding: 12px;
            background-color: #1769aa;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
        }

        .submit-btn:hover {
            background-color: #125486;
            box-shadow: 0 7px 16px rgba(23, 105, 170, 0.24);
            transform: translateY(-1px);
        }

        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .options-row a {
            color: #0d6efd;
            text-decoration: none;
        }

        .options-row a:hover {
            text-decoration: underline;
        }

        /* Notification Styling */
        .alert {
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .alert.success {
            background-color: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
        }

        @media (max-width: 560px) {
            body {
                align-items: flex-start;
                padding: 18px 12px;
            }

            .container {
                padding: 7px;
                border-radius: 12px;
            }

            .auth-layout {
                display: block;
                min-height: 0;
            }

            .welcome-panel {
                padding: 25px 22px;
                border-radius: 8px;
            }

            .welcome-panel h1 {
                margin-top: 38px;
                font-size: 31px;
            }

            .welcome-points {
                margin-top: 25px;
            }

            .auth-content {
                padding: 24px 15px 18px;
            }

            .form-row {
                display: block;
            }

            .form-row .form-group {
                margin-bottom: 17px;
            }

            .options-row {
                align-items: flex-start;
                gap: 12px;
            }
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="auth-layout">
            <aside class="welcome-panel">
                <div class="brand"><span class="brand-mark">24</span> SHOP</div>
                <h1>Everything you love, in one place.</h1>
                <p>Build your 24SHOP account for a smoother, more personal way to discover everyday essentials.</p>
                <div class="welcome-points">
                    <span><i>✓</i> Faster checkout</span>
                    <span><i>✓</i> Order history in one place</span>
                    <span><i>✓</i> Personal account details</span>
                </div>
            </aside>

            <section class="auth-content">
                <div class="tabs">
                    <button id="loginTab" class="tab-btn <?php echo ($active_tab === 'login') ? 'active' : ''; ?>" onclick="switchForm('login')">Login</button>
                    <button id="registerTab" class="tab-btn <?php echo ($active_tab === 'register') ? 'active' : ''; ?>" onclick="switchForm('register')">Create account</button>
                </div>

                <!-- Login Form -->
                <form id="loginForm" class="form-box <?php echo ($active_tab === 'login') ? 'active' : ''; ?>" method="POST" action="">
                    <input type="hidden" name="action" value="login">
                    <h2>Welcome Back</h2>

                    <?php echo $login_message; ?>

                    <div class="form-group">
                        <label for="loginEmail">Email Address</label>
                        <input type="email" name="loginEmail" id="loginEmail" placeholder="e.g., alex@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" name="loginPassword" id="loginPassword" placeholder="Enter password" required>
                    </div>

                    <div class="options-row">
                        <label style="margin: 0;"><input type="checkbox" name="remember" style="width: auto;"> Remember me</label>
                        <a href="forgot-password.php">Forgot Password?</a>
                    </div>

                    <button type="submit" class="submit-btn">Sign In</button>
                </form>

                <!-- Customer Registration Form -->
                <form id="registerForm" class="form-box <?php echo ($active_tab === 'register') ? 'active' : ''; ?>" method="POST" action="">
                    <input type="hidden" name="action" value="register">
                    <h2>Customer Registration</h2>

                    <?php echo $register_message; ?>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" name="full_name" id="full_name" placeholder="John Doe" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">Gender</label>
                            <select name="gender" id="gender" required>
                                <option value="">Select Gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="regEmail">Email Address</label>
                            <input type="email" name="email" id="regEmail" placeholder="john@example.com" required>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" name="phone" id="phone" placeholder="+1 234 567 8900" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="regPassword">Password</label>
                            <input type="password" name="password" id="regPassword" placeholder="At least 4 characters" minlength="4" required>
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" name="dob" id="dob" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address"> Address</label>
                        <textarea name="address" id="address" placeholder="123 Main St, Apt 4B"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="pincode">Pin Code</label>
                        <input type="text" name="pincode" id="pincode" placeholder="123456" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="city">City</label>
                            <input type="text" name="city" id="city" placeholder="New York" required>
                        </div>
                        <div class="form-group">
                            <label for="state">State</label>
                            <input type="text" name="state" id="state" placeholder="NY">
                        </div>
                    </div>

                    <button type="submit" class="submit-btn">Create Customer Account</button>
                </form>
            </section>
        </div>
    </div>

    <script>
        function switchForm(formType) {
            document.getElementById('loginForm').classList.toggle('active', formType === 'login');
            document.getElementById('registerForm').classList.toggle('active', formType === 'register');

            document.getElementById('loginTab').classList.toggle('active', formType === 'login');
            document.getElementById('registerTab').classList.toggle('active', formType === 'register');
        }
    </script>
</body>

</html>