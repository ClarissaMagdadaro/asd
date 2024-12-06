<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <h1>Welcome, <?= htmlspecialchars($_SESSION['admin_name']); ?></h1>
    <a href="logout.php">Logout</a>
</body>
</html>
