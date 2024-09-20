<?php
session_start();

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'banking');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if request_id is set for deletion
if (isset($_GET['request_id'])) {
    $request_id = intval($_GET['request_id']);
    $delete_stmt = $conn->prepare("SELECT user_id FROM delete_requests WHERE request_id = ?");
    $delete_stmt->bind_param("i", $request_id);
    $delete_stmt->execute();
    $delete_stmt->bind_result($user_id);
    $delete_stmt->fetch();
    $delete_stmt->close();

    if ($user_id) {
        // Delete user from accounts and users tables
        $delete_user_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $delete_user_stmt->bind_param("i", $user_id);
        $delete_user_stmt->execute();
        $delete_user_stmt->close();

        // Delete request from delete_requests table
        $delete_request_stmt = $conn->prepare("DELETE FROM delete_requests WHERE request_id = ?");
        $delete_request_stmt->bind_param("i", $request_id);
        $delete_request_stmt->execute();
        $delete_request_stmt->close();

        echo "<script>alert('Account deleted successfully.'); window.location.href = 'admin_dashboard.php';</script>";
    }
}

// Fetch admin details
$admin_id = $_SESSION['admin_id'];
$admin_stmt = $conn->prepare("SELECT username FROM admins WHERE id = ?");
$admin_stmt->bind_param("i", $admin_id);
$admin_stmt->execute();
$admin_stmt->bind_result($admin_username);
$admin_stmt->fetch();
$admin_stmt->close();

// Fetch deletion requests
$requests_stmt = $conn->prepare("
    SELECT dr.request_id, u.username, a.balance AS account_balance
    FROM delete_requests dr
    JOIN users u ON dr.user_id = u.id
    JOIN accounts a ON a.user_id = u.id
");
$requests_stmt->execute();
$requests_result = $requests_stmt->get_result();

// Fetch users
$users_stmt = $conn->prepare("
    SELECT u.id, u.username, a.balance AS account_balance, u.last_login
    FROM users u
    JOIN accounts a ON u.id = a.user_id
");
$users_stmt->execute();
$users_result = $users_stmt->get_result();

// Fetch transactions
$transactions_stmt = $conn->prepare("
    SELECT t.id, u.username, t.type, t.amount, t.date
    FROM transactions t
    JOIN users u ON t.user_id = u.id
");
$transactions_stmt->execute();
$transactions_result = $transactions_stmt->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <?php include 'refreshing_pages.php'; ?>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #ecf0f1;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: #ecf0f1;
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .sidebar img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 20px;
        }
        .sidebar h3 {
            margin: 0;
            text-align: center;
            font-size: 1.2em;
        }
        .sidebar a {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            background-color: #3498db;
            color: #ecf0f1;
            text-decoration: none;
            text-align: center;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #2980b9;
        }
        .main-content {
            margin-left: 350px;
            padding: 20px;
            width: calc(80% - 100px);
        }
        h2 {
            margin-bottom: 20px;
            color: #2c3e50;
            font-size: 1.5em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #bdc3c7;
        }
        table th {
            background-color: #ecf0f1;
            color: #2c3e50;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: #ecf0f1;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <img src="admin_profile.png" alt="Admin Profile Picture">
    <h3><?php echo htmlspecialchars($admin_username); ?></h3>
    <a href="admin_panel.php">Admin Panel</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2>Deletion Requests</h2>
    <table>
        <tr>
            <th>Request ID</th>
            <th>Username</th>
            <th>Account Balance</th>
            <th>Action</th>
        </tr>
        <?php while ($request = $requests_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                <td><?php echo htmlspecialchars($request['username']); ?></td>
                <td>$<?php echo number_format($request['account_balance'], 2); ?></td>
                <td><a class="btn-danger" href="?request_id=<?php echo $request['request_id']; ?>" onclick="return confirm('Are you sure you want to delete this account?')">Delete</a></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Users</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Account Balance</th>
            <th>Last Login</th>
        </tr>
        <?php while ($user = $users_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td>$<?php echo number_format($user['account_balance'], 2); ?></td>
                <td><?php echo htmlspecialchars($user['last_login']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h2>Transactions</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Type</th>
            <th>Amount</th>
            <th>Date</th>
        </tr>
        <?php while ($transaction = $transactions_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($transaction['id']); ?></td>
                <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($transaction['type'])); ?></td>
                <td>$<?php echo number_format($transaction['amount'], 2); ?></td>
                <td><?php echo htmlspecialchars($transaction['date']); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
