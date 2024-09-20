<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only allow admins to update account status
if ($_SESSION['role'] == 'admin') {
    die("Unauthorized access!");
}

// Handle status update (placing account on hold or activating)
if (isset($_POST['user_id']) && isset($_POST['account_status'])) {
    $user_id = $_POST['user_id'];
    $account_status = $_POST['account_status'];

    $stmt = $conn->prepare("UPDATE users SET account_status = ? WHERE id = ?");
    $stmt->bind_param("si", $account_status, $user_id);
    
    if ($stmt->execute()) {
        echo "Account status updated successfully.";
    } else {
        echo "Error updating account status: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

// Redirect back to the admin panel
header('Location: admin_panel.php');
exit();
?>
