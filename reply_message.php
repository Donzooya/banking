<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_id'], $_POST['reply'])) {
    $message_id = $_POST['message_id'];
    $reply_content = $_POST['reply'];
    $sender_id = $_SESSION['user_id'];

    // Prepare and execute the statement
    $stmt = $conn->prepare("INSERT INTO message_replies (message_id, sender_id, reply_content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $message_id, $sender_id, $reply_content);

    if ($stmt->execute()) {
        header('Location: dashboard.php?message=Reply sent successfully!');
    } else {
        header('Location: dashboard.php?error=Error sending reply: ' . $stmt->error);
    }

    $stmt->close();
}

$conn->close();
?>
