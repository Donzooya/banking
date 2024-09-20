<?php 
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Ensure the admin is logged in and has admin privileges
 {
   

// Fetch all users for admin to manage
$result = $conn->query("SELECT id, username, account_status FROM users");

$conn->close();}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #2C3E50;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
        }
        .sidebar h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            background-color: #3498DB;
            transition: background-color 0.3s;
        }
        .sidebar a:hover {
            background-color: #2980B9;
        }
        .main-content {
            margin-left: 350px;
            padding: 20px;
            width: calc(100% - 350px);
        }
        h2 {
            margin-top: 0;
            color: #2C3E50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table th, table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
        }
        table th {
            background-color: #f4f4f4;
            color: #333;
        }
        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        form {
            display: flex;
            align-items: center;
        }
        select {
            padding: 8px;
            margin-right: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            background-color: #27ae60;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #2ecc71;
        }
        .message {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            color: #fff;
            background-color: #e74c3c;
        }
        .message.success {
            background-color: #2ecc71;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="manage_users.php">Manage Users</a>
    <a href="logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2>Manage User Accounts</h2>

    <!-- Display session messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="message <?php echo ($_SESSION['message_type'] ?? ''); ?>">
            <?php 
                echo htmlspecialchars($_SESSION['message']);
                unset($_SESSION['message']); // Clear message after displaying
            ?>
        </div>
    <?php endif; ?>

    <table>
        <tr>
            <th>Username</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars(ucfirst($row['account_status'])); ?></td>
            <td>
                <form method="post" action="update_status.php">
                    <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
                    <select name="account_status">
                        <option value="active" <?php if ($row['account_status'] == 'active') echo 'selected'; ?>>Active</option>
                        <option value="on_hold" <?php if ($row['account_status'] == 'on_hold') echo 'selected'; ?>>On Hold</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</body>
</html>
        
