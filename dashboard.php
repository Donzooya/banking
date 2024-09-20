<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user information
$user_stmt = $conn->prepare("SELECT username, profile_picture FROM users WHERE id = ?");
if ($user_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_stmt->bind_result($username, $profile_picture);
$user_stmt->fetch();
$user_stmt->close();

// Fetch account balance
$balance_stmt = $conn->prepare("SELECT balance FROM accounts WHERE user_id = ?");
if ($balance_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$balance_stmt->bind_param("i", $user_id);
$balance_stmt->execute();
$balance_stmt->bind_result($balance);
$balance_stmt->fetch();
$balance_stmt->close();

// Fetch transaction history
$transactions_stmt = $conn->prepare("SELECT type, amount, date FROM transactions WHERE user_id = ? ORDER BY date DESC");
if ($transactions_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$transactions_stmt->bind_param("i", $user_id);
$transactions_stmt->execute();
$transactions_stmt->store_result();
$transactions_stmt->bind_result($type, $amount, $date);

$transactions = [];
while ($transactions_stmt->fetch()) {
    $transactions[] = [
        'type' => $type,
        'amount' => $amount,
        'date' => $date
    ];
}
$transactions_stmt->close();

// Fetch stock data (dummy data for now)
$stock_data = [
    ["symbol" => "AAPL", "price" => "150.00"],
    ["symbol" => "GOOG", "price" => "2800.50"],
    ["symbol" => "TSLA", "price" => "750.30"]
];

// Fetch messages sent to the logged-in user
$messages_stmt = $conn->prepare("SELECT m.id, m.sender_id, m.message_content, m.timestamp, a.username as sender 
                                 FROM messages m 
                                 JOIN admins a ON m.sender_id = a.id 
                                 WHERE m.receiver_id = ? 
                                 ORDER BY m.timestamp DESC");
$messages_stmt->bind_param("i", $user_id);
$messages_stmt->execute();
$messages_stmt->store_result();
$messages_stmt->bind_result($message_id, $sender_id, $message_text, $timestamp, $sender);

$messages = [];
while ($messages_stmt->fetch()) {
    $messages[] = [
        'id' => $message_id,
        'sender' => $sender,
        'message' => $message_text,
        'timestamp' => $timestamp
    ];
}
$messages_stmt->close();

$conn->close(); // Close the connection after everything is done
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Banking Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <?php include 'refreshing_pages.php'; ?> <!-- Include the auto-refresh script -->
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            margin: 0;
            padding: 0;
        }

        .sidebar {
            width: 16%;
            background-color: rgb(255, 255, 255);
            padding: 20px;
            height: 100vh;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            position: fixed;
            top: 0;
            left: 0;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            margin: 20px 0;
        }

        .sidebar ul li a {
            color: #333;
            text-decoration: none;
            font-weight: bold;
            display: block;
            padding: 10px;
            border-radius: 5px;
        }

        .sidebar ul li a:hover {
            background-color: #e0e0e0;
        }

        .main-content {
            margin-left: 20%;
            padding: 20px;
            width: 77%;
            background-color: white;
            
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-evenly;
            border-bottom: 1px solid #ddd;
            padding-bottom: 18px;
            position: fixed;
            top:1%;
            width:77%;
            background-color: white;
        }

        .header .profile-info {
            display: flex;
            align-items: center;
        }

        .header img {
            border-radius: 10px;
            width: 45px;
            height: 45px;
            margin-right: 30px;
            scale: 110%;
        }

        .header .bell-icon {
            margin: 0px 30px;
            position: relative;
            scale: 80%;
        
        }

        .header .bell-icon::after {
            content: '';
            position: absolute;
            top: -5px;
            right: -5px;
            width: 10px;
            height: 10px;
            background-color: red;
            border-radius: 50%;
            display: <?php echo !empty($messages) ? 'block' : 'none'; ?>;
        }

        .header .search-bar {
            flex: 1;
            margin-left: 10px;
        }

        .search-bar input {
            width: 50%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .total-balance {
            text-align: right;
        }

        .account-summary {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .account-card {
            background-color: #e0f7fa;
            padding: 20px;
            width: 30%;
            border-radius: 8px;
        }

        .file-transfer {
            margin-top: 20px;
            background-color: #fce4ec;
            padding: 20px;
            border-radius: 8px;
        }

        .transactions {
            margin-top: 20px;
            border-radius: 20px;
        }

        .transactions ul {
            list-style-type: none;
            padding: 0;
            background-color: rgba(67, 184, 102, 0.37);
            border-radius: 10px;
        }

        .transactions ul li {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }

        .messages {
            margin-top: 20px;
        }

        .messages .message {
            background-color: #e0f2f1;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        #card-1{
            background-color: rgba(165, 42, 42, 0.774);
        }
        #card-2{
            background-color: rgba(137, 43, 226, 0.596);
        }
        #card-3{
            background-color: rgba(180, 166, 104, 0.596);
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><a href="dashboard.php">Overview</a></li>
            <li><a href="transactions.php">Payments</a></li>
            <li><a href="#">Cards</a></li>
            <li><a href="#">Settings</a></li>
            <li><a href="edit_account.php">Account</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="header">
        
            <div class="search-bar">
                <input type="text" placeholder="Search transactions...">
            </div>
            <div class="bell-icon">
                <img src="bell-icon.png" alt="Messages">
            </div>
            <div class="profile-info">
            <?php if (!empty($profile_picture)): ?>
            <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
        <?php else: ?>
            <img src="default_profile.png" alt="Default Profile Picture">
        <?php endif; ?>
                <p><?php echo htmlspecialchars($username); ?></p>
            </div>
            
        </div>
        <div class="total-balance">
            <h2>Total Assets</h2>
            <p><?php echo htmlspecialchars('$' . number_format($balance, 2)); ?></p>
        </div>
        <div class="account-summary">
            <!-- Example account cards, replace with dynamic content if needed -->
            <div id="card-1" class="account-card">
                <h3>Registered Retirement Savings Plan</h3>
                <p>Account Number: 000645-21225221</p>
                <p><span><p><?php echo htmlspecialchars('$' . number_format($balance, 2)); ?></p></span> </p>
            </div>
            <div id="card-2" class="account-card">
                <h3>Registered Education Savings Plan</h3>
                <p>Account Number: 000655-2154736</p>
                <p><span><p><?php echo htmlspecialchars('$' . number_format($balance, 2)); ?></p></span> </p>
            </div>
            <div id="card-3" class="account-card">
                <h3>Tax-Free Saving Account</h3>
                <p>Account Number: 000681-21222578</p>
                <p> <span><p><?php echo htmlspecialchars('$' . number_format($balance, 2)); ?></p></span> </p>
            </div>
        </div>
        <div class="transactions">
            <h3>Transaction History</h3>
            <ul>
                <?php if (empty($transactions)) { ?>
                    <li>No transactions found.</li>
                <?php } else {
                    foreach ($transactions as $transaction) { ?>
                        <li><span><?php echo htmlspecialchars($transaction['type']); ?></span><span><?php echo htmlspecialchars('$' . number_format($transaction['amount'], 2)); ?></span><span><?php echo htmlspecialchars($transaction['date']); ?></span></li>
                <?php } } ?>
            </ul>
        </div>
        <div class="messages">
            <h3>Messages</h3>
            <?php if (empty($messages)) { ?>
                <p>No new messages.</p>
            <?php } else {
                foreach ($messages as $message) { ?>
                    <div class="message">
                        <p><strong><?php echo htmlspecialchars($message['sender']); ?>:</strong> <?php echo htmlspecialchars($message['message']); ?></p>
                        <p><small><?php echo htmlspecialchars($message['timestamp']); ?></small></p>
                    </div>
            <?php } } ?>
        </div>
        
    </div>
</body>
</html>
