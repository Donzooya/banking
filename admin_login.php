<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Admin login logic using the 'admins' table
        $stmt = $conn->prepare("SELECT id, password FROM admins WHERE username = ?");
        if (!$stmt) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $stored_password);
            $stmt->fetch();

            if ($password === $stored_password) {
                $_SESSION['admin_id'] = $id; // Storing the admin ID in the session
                header('Location: admin_dashboard.php'); // Redirect to admin dashboard
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Admin not found.";
        }
        $stmt->close();
    } else {
        $error = "Please fill in all fields.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-image: url("./images/pexels-danielabsi-952670.jpg");
            background-size: cover;
            background-position: center;
        }
        .login-container {
            width: 300px;
            padding: 30px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
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
            margin-top: 10px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>

    <!-- Display error if exists -->
    <?php if (isset($error)): ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="post" action="admin_login.php">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        
        <input type="submit" value="Login">
    </form>
    <p>Users <a href="login.php">Login here</a>.</p>
</div>

</body>
</html>
