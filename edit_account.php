<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize variables
$success = $error = '';
$username = $profile_picture = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $profile_picture = ''; // Reset to ensure the logic works correctly

    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Set the allowed file extensions
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExtension, $allowedExtensions)) {
            // Ensure the uploads directory exists
            $uploadFileDir = './uploads/';
            if (!file_exists($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true); // Create directory if it doesn't exist
            }

            $destFilePath = $uploadFileDir . 'profile_' . $user_id . '.' . $fileExtension;

            if (move_uploaded_file($fileTmpPath, $destFilePath)) {
                // Update profile picture path in the database
                $profile_picture = 'profile_' . $user_id . '.' . $fileExtension;
            } else {
                $error = "There was an error uploading the file, please try again.";
            }
        } else {
            $error = "Upload failed. Allowed file types: jpg, jpeg, png, gif.";
        }
    }

    // Fetch current profile picture if no new file was uploaded
    if (empty($profile_picture)) {
        $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($profile_picture);
        $stmt->fetch();
        $stmt->close();
    }

    // Update user information
    $stmt = $conn->prepare("UPDATE users SET username = ?, profile_picture = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $profile_picture, $user_id);

    if ($stmt->execute()) {
        $success = "Account updated successfully.";
    } else {
        $error = "Error updating account: " . $stmt->error;
    }

    $stmt->close();
}

// Fetch current user information
$stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $profile_picture);
$stmt->fetch();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 25%;
            background-color: #333;
            color: #fff;
            height: 100vh;
            padding: 20px;
        }
        .sidebar a {
            display: block;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 10px;
            margin: 20px 0;
            text-decoration: none;
            border-radius: 5px;
        }
        .main-content {
            width: 75%;
            padding: 20px;
        }
        .main-content h2 {
            margin-bottom: 20px;
        }
        .edit-form {
            margin-top: 20px;
        }
        .edit-form input, .edit-form select {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            display: block;
            width: 100%;
        }
        .edit-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .edit-form input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error, .success {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        <a href="transactions.php">Transactions</a>
        <a href="delete_account.php">Request to Delete Account</a>
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Edit Account</h2>
        <?php include 'refreshing_pages.php'; ?> <!-- Include the auto-refresh script -->

        <!-- Display success or error messages -->
        <?php if (!empty($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>

        <!-- Display current profile picture -->
        <?php if (!empty($profile_picture)): ?>
            <img src="./uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" style="max-width: 150px; max-height: 150px;">
        <?php endif; ?>

        <!-- Edit form -->
        <div class="edit-form">
            <form method="post" action="edit_account.php" enctype="multipart/form-data">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>

                <label for="profile_picture">Profile Picture</label>
                <input type="file" name="profile_picture" id="profile_picture">
                
                <input type="submit" value="Update">
            </form>
        </div>
    </div>

</body>
</html>
