<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle deletion request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $confirm = isset($_POST['confirm']) ? $_POST['confirm'] : '';

    if ($confirm === 'yes') {
        // Check if there's a pending deletion request
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM delete_requests WHERE user_id = ?");
        if (!$check_stmt) {
            $error = "Error preparing statement: " . $conn->error;
        } else {
            $check_stmt->bind_param("i", $user_id);
            $check_stmt->execute();
            $check_stmt->bind_result($request_count);
            $check_stmt->fetch();
            $check_stmt->close();

            if ($request_count > 0) {
                // Pending request found
                $error = "You have already requested deletion. Please wait a minute before requesting again.";
            } else {
                // Insert a new deletion request into the delete_requests table
                $stmt = $conn->prepare("INSERT INTO delete_requests (user_id) VALUES (?)");
                if (!$stmt) {
                    $error = "Error preparing statement: " . $conn->error;
                } else {
                    $stmt->bind_param("i", $user_id);
                    if ($stmt->execute()) {
                        // Success message
                        $success = "Your account deletion request has been submitted to the admin. Please wait for confirmation.";
                    } else {
                        $error = "Error executing statement: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    } else {
        // Redirect to edit_account.php if "No" is selected
        header('Location: edit_account.php');
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
   
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .sidebar {
            width: 200px;
            background-color: #333;
            color: #fff;
            padding: 15px;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
        }
        .sidebar a {
            display: block;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            background-color: #4CAF50;
        }
        .sidebar a:hover {
            background-color: #45a049;
        }
        .container {
            max-width: 600px;
            margin-left: 220px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #fff;
        }
        .container h2 {
            margin-top: 0;
        }
        .container input[type="submit"] {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
        }
        .container input[type="submit"]:hover {
            background-color: #d32f2f;
        }
        .error {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <a href="dashboard.php">Dashboard</a>
    <a href="edit_account.php">Edit Account</a>
    <a href="logout.php">Logout</a>
</div>

<div class="container">
    <h2>Delete Account</h2>
    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php elseif (isset($success)): ?>
        <p class="success"><?php echo $success; ?></p>
    <?php endif; ?>
    <p>Are you sure you want to delete your account? This action is irreversible.</p>
    <form method="post" action="delete_account.php">
        <input type="radio" id="yes" name="confirm" value="yes" required>
        <label for="yes">Yes</label><br>
        <input type="radio" id="no" name="confirm" value="no">
        <label for="no">No</label><br><br>
        <input type="submit" value="Submit Request">
    </form>
</div>

</body>
</html>
