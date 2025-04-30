<?php
require 'dbcon.php'; 
session_start();

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // === REGISTER USER ===
    if ($action === 'register') {
        $firstName = trim($_POST['firstName']);
        $lastName = trim($_POST['lastName']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirmPassword'];

        if ($password !== $confirmPassword) {
            echo json_encode(['status' => 'error', 'message' => 'Passwords do not match.']);
            exit;
        }

        // Check if email already exists
        $check = $conn->prepare("SELECT 1 FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => 'Email already exists.']);
            exit;
        }

        // Insert into users
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'customer';

        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $email, $hashedPassword, $role);

        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;

            // Insert into customers
            $stmt2 = $conn->prepare("INSERT INTO customers (user_id, first_name, last_name) VALUES (?, ?, ?)");
            $stmt2->bind_param("iss", $user_id, $firstName, $lastName);

            if ($stmt2->execute()) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to save customer details.']);
            }

            $stmt2->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to register user.']);
        }

        $stmt->close();
    }

    // === LOGIN USER ===
    elseif ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        if (empty($email) || empty($password)) {
            echo json_encode(['status' => 'error', 'message' => 'Please fill in all fields.']);
            exit;
        }

        $stmt = $conn->prepare("SELECT u.user_id, u.email, u.password, u.role, c.first_name, c.last_name 
                                FROM users u 
                                LEFT JOIN customers c ON u.user_id = c.user_id 
                                WHERE u.email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['first_name'] = $user['first_name'];
                $_SESSION['last_name'] = $user['last_name'];
                $_SESSION['logged_in'] = true;

                $redirectUrl = ($user['role'] === 'admin') ? 'admin/admin_dashboard.php' : 'customer/customer_loggedin.php';

                echo json_encode(['status' => 'success', 'redirect' => $redirectUrl]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid email or password.']);
        }

        $stmt->close();
    }
}
?>
