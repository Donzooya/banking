<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($username) || empty($password)) {
        echo "Please fill in all fields.";
    } elseif ($password !== $confirm_password) {
        echo "Passwords do not match!";
    } else {
        // Check if the username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            echo "Username already taken!";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into the users table
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $user_id = $stmt->insert_id; // Get the inserted user ID

                // Create an account with a default balance (e.g., $1000.00)
                $default_balance = 1000.00;
                $stmt = $conn->prepare("INSERT INTO accounts (user_id, balance) VALUES (?, ?)");
                $stmt->bind_param("id", $user_id, $default_balance);
                $stmt->execute();

                echo "Registration successful! You can now <a href='login.php'>login</a>.";
            } else {
                echo "Registration failed. Please try again!";
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!-- HTML form -->
<form method="post">
    <label for="username">Username:</label>
    <input type="text" name="username" id="username" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" id="password" required><br>

    <label for="confirm_password">Confirm Password:</label>
    <input type="password" name="confirm_password" id="confirm_password" required><br>

    <input type="submit" value="Register">

    <p>ALready have an account..? <a href="login.php">Login here</a>.</p>
</form>

<style>
     body {
            font-family: Arial, sans-serif;
            background-image:url("./images./pexels-arthousestudio-4534200.jpg");
            background-size: cover;
            display: flex;
           justify-content: center;
            align-items: center;
            height: 98vh;
            width: 99%;
        }
        form {
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