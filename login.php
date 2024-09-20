<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT id, password, account_status FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    // Check if the user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashed_password, $account_status);
        $stmt->fetch();

        // Check if the account is on hold (under audit)
        if ($account_status === 'on_hold') {
            $error = "Your account is currently under audit. Please contact support.";
        }
        // Verify the password if the account is active
        elseif (password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $id; // Set session for user
            $_SESSION['role'] = 'user'; // Assuming this is a user login
            header('Location: dashboard.php');  // Redirect to the dashboard
            exit();
        } else {
            $error = "Invalid credentials!";
        }
    } else {
        $error = "Invalid credentials!";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
  
    <style>
        body {
            font-family: Arial, sans-serif;
            background-image: url("./images/pexels-arthousestudio-4534200.jpg");
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 98vh;
            width: 99%;
        }
        .login-container {
            width: 300px;
            margin: 100px auto;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
            text-align: center;
        }
        p {
            text-align: center;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Login</h2>
    
    <!-- Error message -->
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <form method="post" action="login.php">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        
        <input type="submit" value="Login">
    </form>

    <!-- Link to Registration -->
    <p>Not a member yet? <a href="register.php">Register here</a>.</p>
    <p><a href="admin_login.php">Login as Admin</a>.</p>
</div>

</body>
</html>
