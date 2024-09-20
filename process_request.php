<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Start transaction
        $conn->begin_transaction();
        try {
            // Delete transactions related to the user
            $stmt = $conn->prepare("DELETE FROM transactions WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Delete the user account
            $stmt = $conn->prepare("DELETE FROM accounts WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            // Update the deletion request status to 'approved'
            $stmt = $conn->prepare("UPDATE delete_requests SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "Error: " . $e->getMessage();
        }
    } elseif ($action == 'deny') {
        // Update the deletion request status to 'denied'
        $stmt = $conn->prepare("UPDATE delete_requests SET status = 'denied' WHERE id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();
    }

    // Redirect back to the admin page
    header('Location: admin.php');
    exit();
}

$conn->close();
?>
