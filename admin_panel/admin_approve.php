<?php
include '../components/connect.php';

// Check Admin Authentication
if (!isset($_COOKIE['admin_id'])) {
    header('location:login.php');
    exit();
}

$get_admins = $conn->prepare("SELECT * FROM `admins`");
$get_admins->execute();
$admins = $get_admins->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Admin Accounts</title>
    <link rel="stylesheet" type="text/css" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <?php include '../components/admin_header.php'; ?>

        <section class="admin-accounts">
            <div class="heading">
                <h1>Admin Accounts</h1>
                <img src="../image/separator-img.png">
            </div>

            <?php if (!empty($admins)): ?>
                <div class="accounts-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td><?= htmlspecialchars($admin['id'] ?? 'Unknown ID') ?></td>
                                    <td><?= htmlspecialchars($admin['username'] ?? 'Unknown Username') ?></td>
                                    <td><?= htmlspecialchars($admin['status'] ?? 'Unknown Status') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="no-accounts">No admin accounts found.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
