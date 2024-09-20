<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ensure the admin is logged in and has admin privileges

// Fetch all users for admin to manage
$result = $conn->query("SELECT id, username, account_status, profile_picture FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            padding: 20px;
            background-color: burlywood;
            margin: 0;
            display: flex;
            justify-content:space-evenly;
            flex-direction: column;
            align-items: center;
            
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background-color: #6a1b9a;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .back-button:hover {
            background-color: #7e4dc2;
        }
        table {
            
            width: 50%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        table th, table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        table th {
            background-color: #6a1b9a;
            color: white;
            text-transform: uppercase;
            font-weight: 500;
        }
        table tr:hover {
            background-color: #f5f5f5;
        }
        img.profile-pic {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }
        textarea {
            width: 80%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
            margin-bottom: 10px;
            font-family: inherit;
        }
        button {
            padding: 10px 15px;
            background-color: #6a1b9a;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }
        button:hover {
            background-color: #7e4dc2;
        }
        .success-message {
            color: green;
            margin: 15px 0;
            text-align: center;
        }
        .error-message {
            color: red;
            margin: 15px 0;
            text-align: center;
        }
        .logout {
            text-align: center;
            margin-top: 20px;
        }
        .logout a {
            color: #6a1b9a;
            text-decoration: none;
            font-weight: bold;
        }
        .logout a:hover {
            text-decoration: underline;
        }
        form {
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- Back Button -->
    <a href="admin_panel.php" class="back-button">Back to Dashboard</a>

    <h2>Admin Panel: Manage User Accounts</h2>
    
    <?php if (isset($_GET['message_content'])): ?>
        <p class="success-message"><?php echo htmlspecialchars($_GET['message_content']); ?></p>
    <?php elseif (isset($_GET['error'])): ?>
        <p class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></p>
    <?php endif; ?>
    
    <table>
        <tr>
            <th>Profile Picture</th>
            <th>Username</th>
            <th>Status</th>
            <th>Send Message</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <?php if ($row['profile_picture']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['profile_picture']); ?>" class="profile-pic" alt="Profile Picture">
                <?php else: ?>
                    <img src="default_profile.png" class="profile-pic" alt="Default Profile Picture">
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['username']); ?></td>
            <td><?php echo htmlspecialchars($row['account_status']); ?></td>
            <td>
                <form method="post" action="send_message.php">
                    <input type="hidden" name="receiver_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                    <textarea name="message_content" placeholder="Write your message here..." required></textarea>
                    <button type="submit">Send Message</button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <div class="logout">
        <p><a href="logout.php">Logout</a></p>
    </div>
</body>
</html>

<?php $conn->close(); ?>
