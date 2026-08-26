<?php
require_once 'config/database.php';
require_once 'includes/session.php';

$user = null;
if (isset($_GET['id'])) {
    $userId = $_GET['id'];
    try {
        $pdo = getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        // Log error or handle it gracefully
        $user = null;
    }
}

if (!$user) {
    // Redirect to admin users page or show an error
    header("Location: admin.php#users");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> - User Details</title>
    <link rel="stylesheet" href="public/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Basic styles for user detail page */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .user-detail-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }
        .user-detail-header h1 {
            font-size: 2.5em;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .user-detail-header .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #e5e7eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3em;
            font-weight: 600;
            color: #6b7280;
            margin: 0 auto 15px;
        }
        .user-detail-info {
            padding: 20px;
        }
        .user-detail-info div {
            margin-bottom: 15px;
        }
        .user-detail-info strong {
            color: #1f2937;
            display: inline-block;
            width: 120px;
        }
        .user-detail-info span {
            color: #4b5563;
        }
        .back-button {
            display: inline-block;
            background-color: #6b7280;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #4b5563;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; // Assuming you have a header file ?>

    <div class="container">
        <div class="user-detail-header">
            <div class="user-avatar"><?php echo htmlspecialchars(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?></div>
            <h1><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></h1>
            <span class="status-badge <?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span>
            <span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst($user['status']); ?></span>
        </div>

        <div class="user-detail-info">
            <div><strong>Email:</strong> <span><?php echo htmlspecialchars($user['email']); ?></span></div>
            <div><strong>Phone:</strong> <span><?php echo htmlspecialchars($user['phone'] ?: 'N/A'); ?></span></div>
            <div><strong>Join Date:</strong> <span><?php echo date('M j, Y', strtotime($user['join_date'])); ?></span></div>
            <div><strong>Items Reported:</strong> <span><?php echo htmlspecialchars($user['items_reported']); ?></span></div>
        </div>

        <a href="admin.php#users" class="back-button">Back to Users</a>
    </div>

    <?php include 'includes/footer.php'; // Assuming you have a footer file ?>
    <script src="app.js"></script>
</body>
</html>