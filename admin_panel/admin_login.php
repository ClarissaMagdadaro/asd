<?php
session_start();
include '../components/connect.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $query = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    $query->execute([$email]);
    $admin = $query->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            header('Location: admin_dashboard.php');
            exit();
        } else {
            echo "Password mismatch.";
        }
    } else {
        echo "Email not found: " . htmlspecialchars($email);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="../css/admin_style.css">
</head>
<body>
    <div class="login-container">
        <h1>Admin Login</h1>
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?= htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <input type="email" name="email" placeholder="Enter email" required>
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit" name="login">Login</button>
        </form>
    </div>
</body>
</html>
