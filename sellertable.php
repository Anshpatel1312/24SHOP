<?php
session_start();
require_once __DIR__ . "/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Seller Details
    $sellername = trim($_POST['sellername'] ?? '');
    $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $mobile     = trim($_POST['mobile'] ?? '');
    $password   = $_POST['password'] ?? '';
    $gender     = trim($_POST['gender'] ?? 'Male');
    $dob        = !empty($_POST['dob']) ? $_POST['dob'] : null;
    $address    = trim($_POST['address'] ?? '');
    $city       = trim($_POST['city'] ?? '');
    $state      = trim($_POST['state'] ?? '');
    $pincode    = trim($_POST['pincode'] ?? '');

    // Shop Details
    $shop_name           = trim($_POST['shop_name'] ?? '');
    $owner_name          = trim($_POST['owner_name'] ?? '');
    $gst_number          = trim($_POST['gst_number'] ?? '');
    $shop_address        = trim($_POST['shop_address'] ?? '');
    $shop_city           = trim($_POST['shop_city'] ?? '');
    $shop_state          = trim($_POST['shop_state'] ?? '');
    $shop_pincode        = trim($_POST['shop_pincode'] ?? '');
    $bank_account_number = trim($_POST['bank_account_number'] ?? '');
    $ifsc_code           = trim($_POST['ifsc_code'] ?? '');
    $upi_id              = trim($_POST['upi_id'] ?? '');

    if ($sellername === '' || $email === false || strlen($password) < 4 || $shop_name === '') {
        echo "<script>
                alert('Please fill all required fields properly.');
                window.history.back();
              </script>";
        exit;
    }

    // Check for duplicate email
    $check_stmt = $mysqli->prepare("SELECT seller_id FROM seller_details WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $email_exists = $check_stmt->get_result()->num_rows > 0;
    $check_stmt->close();

    if ($email_exists) {
        echo "<script>
                alert('This email is already registered as a seller. Please log in.');
                window.location='shoplogin.php';
              </script>";
        exit;
    }

    // File Upload
    $filename = '';
    if (!empty($_FILES['registration_certificate']['name'])) {
        $upload_dir = __DIR__ . "/uploads/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $ext = pathinfo($_FILES['registration_certificate']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($_FILES['registration_certificate']['name'], PATHINFO_FILENAME)) . '.' . $ext;
        $dest     = $upload_dir . $filename;

        if (!move_uploaded_file($_FILES['registration_certificate']['tmp_name'], $dest)) {
            echo "<script>
                    alert('File upload failed. Please try again.');
                    window.history.back();
                  </script>";
            exit;
        }
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Insert into seller_details
    $stmt1 = $mysqli->prepare("INSERT INTO seller_details (sellername, email, mobile, password, gender, dob, address, city, state, pincode) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt1->bind_param("ssssssssss", $sellername, $email, $mobile, $password_hash, $gender, $dob, $address, $city, $state, $pincode);

    if (!$stmt1->execute()) {
        echo "<script>alert('Error registering seller details: " . addslashes($stmt1->error) . "'); window.history.back();</script>";
        exit;
    }

    $seller_id = $stmt1->insert_id;
    $stmt1->close();

    // Insert into shop_details
    $stmt2 = $mysqli->prepare("INSERT INTO shop_details (seller_id, shop_name, owner_name, gst_number, shop_address, shop_city, shop_state, shop_pincode, bank_account_number, ifsc_code, upi_id, registration_certificate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt2->bind_param("isssssssssss", $seller_id, $shop_name, $owner_name, $gst_number, $shop_address, $shop_city, $shop_state, $shop_pincode, $bank_account_number, $ifsc_code, $upi_id, $filename);

    if ($stmt2->execute()) {
        $_SESSION['seller_id'] = $seller_id;
        $_SESSION['email'] = $email;
        $stmt2->close();
        echo "<script>
                alert('Seller and Shop Registered Successfully! Redirecting to Dashboard.');
                window.location='admin.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('Error registering shop details: " . addslashes($stmt2->error) . "'); window.history.back();</script>";
        exit;
    }
}
?>

