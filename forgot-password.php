<?php
session_start();
require_once __DIR__ . '/db.php';

$mysqli->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (customer_id),
    CONSTRAINT fk_password_resets_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
$mysqli->query("CREATE TABLE IF NOT EXISTS seller_password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL UNIQUE,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX (seller_id),
    CONSTRAINT fk_seller_password_resets_seller FOREIGN KEY (seller_id) REFERENCES seller_details(seller_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$message = '';
$reset_link = '';
$token = trim($_GET['token'] ?? '');
$reset_type = ($_GET['type'] ?? '') === 'seller' ? 'seller' : 'customer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $reset_type = ($_POST['reset_type'] ?? '') === 'seller' ? 'seller' : 'customer';

    if ($email === false) {
        $message = '<div class="alert error">Please enter a valid email address.</div>';
    } else {
        $account_table = $reset_type === 'seller' ? 'seller_details' : 'customers';
        $account_id = $reset_type === 'seller' ? 'seller_id' : 'id';
        $stmt = $mysqli->prepare("SELECT $account_id AS account_id FROM $account_table WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $account = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($account) {
            $plain_token = bin2hex(random_bytes(32));
            $token_hash = hash('sha256', $plain_token);
            $reset_table = $reset_type === 'seller' ? 'seller_password_resets' : 'password_resets';
            $owner_column = $reset_type === 'seller' ? 'seller_id' : 'customer_id';
            $stmt = $mysqli->prepare("DELETE FROM $reset_table WHERE $owner_column = ? OR expires_at < NOW()");
            $stmt->bind_param('i', $account['account_id']);
            $stmt->execute();
            $stmt->close();
            $stmt = $mysqli->prepare("INSERT INTO $reset_table ($owner_column, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
            $stmt->bind_param('is', $account['account_id'], $token_hash);
            $stmt->execute();
            $stmt->close();

            $reset_link = 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . dirname($_SERVER['PHP_SELF']) . '/forgot-password.php?type=' . $reset_type . '&token=' . urlencode($plain_token);
        }

        $message = '<div class="alert success">If that email is registered, a password reset link has been created.</div>';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
    $token = trim($_POST['token'] ?? '');
    $reset_type = ($_POST['reset_type'] ?? '') === 'seller' ? 'seller' : 'customer';
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'] ?? '';
    $token_hash = hash('sha256', $token);

    if ($token === '' || strlen($new_password) < 4 || $new_password !== $confirm_password) {
        $message = '<div class="alert error">Use matching passwords with at least 4 characters.</div>';
    } else {
        $reset_table = $reset_type === 'seller' ? 'seller_password_resets' : 'password_resets';
        $owner_column = $reset_type === 'seller' ? 'seller_id' : 'customer_id';
        $stmt = $mysqli->prepare("SELECT id, $owner_column AS account_id FROM $reset_table WHERE token_hash = ? AND expires_at > NOW()");
        $stmt->bind_param('s', $token_hash);
        $stmt->execute();
        $reset = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$reset) {
            $message = '<div class="alert error">This reset link is invalid or expired.</div>';
        } else {
            $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $account_table = $reset_type === 'seller' ? 'seller_details' : 'customers';
            $account_id = $reset_type === 'seller' ? 'seller_id' : 'id';
            $stmt = $mysqli->prepare("UPDATE $account_table SET password = ? WHERE $account_id = ?");
            $stmt->bind_param('si', $password_hash, $reset['account_id']);
            $stmt->execute();
            $stmt->close();

            $stmt = $mysqli->prepare('DELETE FROM password_resets WHERE id = ?');
            $stmt->bind_param('i', $reset['id']);
            $stmt->execute();
            $stmt->close();

            $login_page = $reset_type === 'seller' ? 'shoplogin.php' : 'login.php';
            $message = '<div class="alert success">Password changed successfully. <a href="' . $login_page . '">Sign in now</a>.</div>';
            $token = '';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | 24SHOP</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 20px; background: linear-gradient(135deg, #dcecff, #f7fbff 50%, #dff4ec); color: #172033; font-family: 'Segoe UI', Tahoma, sans-serif; }
        .panel { width: min(100%, 460px); padding: 34px; background: #fff; border: 1px solid #dbe3ec; border-radius: 16px; box-shadow: 0 20px 50px rgba(35, 67, 104, .14); }
        h1 { margin: 0 0 10px; font-size: 28px; }
        p { color: #64748b; line-height: 1.5; }
        label { display: block; margin: 18px 0 6px; font-size: 14px; font-weight: 600; }
        input { width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 15px; }
        button { width: 100%; margin-top: 22px; padding: 12px; border: 0; border-radius: 8px; background: #1769aa; color: #fff; font-size: 16px; font-weight: 600; cursor: pointer; }
        a { color: #1769aa; }
        .alert { margin: 18px 0; padding: 12px; border-radius: 8px; font-size: 14px; }
        .success { background: #d1e7dd; color: #0f5132; }
        .error { background: #f8d7da; color: #842029; }
        .dev-link { overflow-wrap: anywhere; }
    </style>
</head>
<body>
    <main class="panel">
        <?php if ($token !== ''): ?>
            <h1>Create a new password</h1>
            <p>Choose a password with at least 4 characters.</p>
            <?php echo $message; ?>
            <form method="post">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token, ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="reset_type" value="<?php echo htmlspecialchars($reset_type, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="new_password">New password</label>
                <input id="new_password" type="password" name="new_password" minlength="4" required>
                <label for="confirm_password">Confirm password</label>
                <input id="confirm_password" type="password" name="confirm_password" minlength="4" required>
                <button type="submit">Change password</button>
            </form>
        <?php else: ?>
            <h1>Forgot your password?</h1>
            <p>Enter your customer email address and we will create a password reset link.</p>
            <?php echo $message; ?>
            <?php if ($reset_link !== ''): ?>
                <p class="dev-link"><strong>Reset link:</strong><br><a href="<?php echo htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8'); ?></a></p>
            <?php endif; ?>
            <form method="post">
                <input type="hidden" name="reset_type" value="<?php echo htmlspecialchars($reset_type, ENT_QUOTES, 'UTF-8'); ?>">
                <label for="email">Email address</label>
                <input id="email" type="email" name="email" required>
                <button type="submit">Create reset link</button>
            </form>
            <p><a href="login.php">Back to login</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
