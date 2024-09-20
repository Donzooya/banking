<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'banking');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle new transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $type = $_POST['type'];
    $amount = $_POST['amount'];

    // Validate amount
    if ($amount <= 0) {
        $error = "Amount must be greater than zero.";
    } else {
        // Start transaction
        $conn->begin_transaction();

        try {
            // Insert transaction into database
            $stmt = $conn->prepare("INSERT INTO transactions (user_id, type, amount) VALUES (?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Error preparing transaction statement: " . $conn->error);
            }
            $stmt->bind_param("ssi", $user_id, $type, $amount);
            if (!$stmt->execute()) {
                throw new Exception("Error executing transaction statement: " . $stmt->error);
            }
            $stmt->close();

            // Update account balance
            if ($type == 'deposit') {
                $stmt = $conn->prepare("UPDATE accounts SET balance = balance + ? WHERE user_id = ?");
            } elseif ($type == 'withdrawal' || $type == 'payment') {
                $stmt = $conn->prepare("UPDATE accounts SET balance = balance - ? WHERE user_id = ?");
            } else {
                throw new Exception("Invalid transaction type.");
            }

            if (!$stmt) {
                throw new Exception("Error preparing balance update statement: " . $conn->error);
            }
            $stmt->bind_param("di", $amount, $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Error executing balance update statement: " . $stmt->error);
            }
            $stmt->close();

            // Commit transaction
            $conn->commit();

            header('Location: transactions.php');  // Redirect to avoid form resubmission
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $conn->rollback();
            $error = $e->getMessage();
        }
    }
}

// Fetch transaction history
$transactions_stmt = $conn->prepare("SELECT type, amount, date FROM transactions WHERE user_id = ? ORDER BY date DESC");
if (!$transactions_stmt) {
    die("Error preparing transaction fetch statement: " . $conn->error);
}
$transactions_stmt->bind_param("i", $user_id);
if (!$transactions_stmt->execute()) {
    die("Error executing transaction fetch statement: " . $transactions_stmt->error);
}
$transactions_stmt->store_result();
$transactions_stmt->bind_result($type, $amount, $date);

$transactions_exist = $transactions_stmt->num_rows > 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }
        .sidebar {
            width: 20%;
            background-color: white;
            color: #fff;
            height: 100vh;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            position:fixed;
        }
        .sidebar a {
            display: block;
            background-color: black;
            color: white;
            text-align: center;
            padding: 10px;
            margin: 20px 0;
            text-decoration: none;
            border-radius: 5px;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: white;
            justify-content: center;
            align-items: center;
            margin-left: 25%;
            margin-top:2%;
            padding: 20px;
            width: 70%;
            height :88vh;
            overflow: scroll;
            position:fixed;
        }
        .main-content h2 {
            margin-bottom: 20px;
        }
        .transactions table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            flex-direction: column;
            background-color: white;
            justify-content: center;
            align-items: center;
        }
        .transactions table th, .transactions table td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .transactions table th {
            background-color: #f4f4f4;
        }
        .transaction-form {
            margin-top: 30%;
            position:fixed;
            left:25%;
        }
        .transaction-form input, .transaction-form select {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .transaction-form input[type="submit"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .transaction-form input[type="submit"]:hover {
            background-color: #45a049;
        }
        .error {
            color: red;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="dashboard.php">Dashboard</a>
        
        <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h2>Transaction History</h2>
        <div class="transactions">
            <?php if ($transactions_exist): ?>
                <table>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Date</th>
                    </tr>
                    <?php while ($transactions_stmt->fetch()): ?>
                        <tr>
                            <td><?php echo ucfirst($type); ?></td>
                            <td>$<?php echo number_format($amount, 2); ?></td>
                            <td><?php echo date("Y-m-d H:i:s", strtotime($date)); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>No transaction history.</p>
            <?php endif; ?>
        </div>

        <!-- Transaction Form -->
        <div class="transaction-form">
            <?php if (isset($error)): ?>
                <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <h2>New Transaction</h2>
                <form method="post" action="transactions.php">
                    <label for="type">Type</label>
                    <select name="type" id="type" required>
                    <option value="deposit">Deposit</option>
                    <option value="withdrawal">Withdrawal</option>
                    <option value="payment">Payment</option>
                </select>
                
                <label for="amount">Amount</label>
                <input type="number" name="amount" id="amount" step="0.01" min="0.01" required>
                
                <input type="submit" value="Submit">
            </form>
        </div>
    </div>

</body>
</html>
