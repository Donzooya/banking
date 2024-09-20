<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the admin is logged in and has admin privileges
if (!isset($_SESSION['id'])) {
    die('Admin ID is not set in the session.');
} else {
    $sender_id = $_SESSION['id'];
}

// Handle sending messages
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_content'], $_POST['receiver_id'])) {
    $message_content = $_POST['message_content'];
    $receiver_id = $_POST['receiver_id'];

    // Debug message content and receiver ID
    if (empty($message_content) || empty($receiver_id)) {
        die("Message content or receiver ID is missing.");
    }

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message_content) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Error preparing the query: " . $conn->error);
    }
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message_content);

    if ($stmt->execute()) {
        header("Location: manage_users.php?message=Message sent successfully!");
        exit();
    } else {
        die("Error sending message: " . $stmt->error);  // Display the exact error for debugging
    }

    $stmt->close();
} else {
    header("Location: admin_dashboard.php?error=Invalid request.");
    exit();
}

$conn->close();
?>
