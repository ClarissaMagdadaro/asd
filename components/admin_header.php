<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../components/connect.php'; // Ensure this is the correct path to your connection script

// Initialize $admin_id to prevent "undefined variable" warning
$admin_id = $_COOKIE['admin_id'] ?? ''; 

if (!empty($admin_id)) {
    // Fetch profile only if admin ID exists
    $select_profile = $conn->prepare("SELECT * FROM `admins` WHERE id = ?");
    $select_profile->execute([$admin_id]);

    if ($select_profile->rowCount() > 0) {
        $fetch_profile = $select_profile->fetch(PDO::FETCH_ASSOC);
    } else {
        // If admin ID is invalid, fallback to guest
        $fetch_profile = ['image' => 'default.png', 'name' => 'Guest'];
    }
} else {
    // Fallback for unauthenticated users or missing admin ID
    $fetch_profile = ['image' => 'default.png', 'name' => 'Guest'];
}

// Check if there are any admins in the database, and create a default admin if none exist
$check_admins = $conn->prepare("SELECT * FROM `admins`");
$check_admins->execute();

if ($check_admins->rowCount() === 0) {
    // No admins exist, create a default admin
    $default_admin_name = 'Admin';
    $default_admin_email = 'admin@example.com';
    $default_admin_password = password_hash('admin123', PASSWORD_DEFAULT); // Securely hash the default password
    $default_admin_image = 'default.png';

    $insert_default_admin = $conn->prepare("INSERT INTO `admins` (name, email, password, image) VALUES (?, ?, ?, ?)");
    $insert_default_admin->execute([$default_admin_name, $default_admin_email, $default_admin_password, $default_admin_image]);

    echo "<script>alert('Default admin account created. Email: admin@example.com, Password: admin123');</script>";
}
?>

<header>
    <div class="logo">
        <img src="../image/logo.png" alt="Logo" width="60">
    </div>
    <div class="right">
        <div class="bx bxs-user" id="user-btn"></div>
        <div class="toggle-btn"><i class="bx bx-menu"></i></div>
    </div>
    <div class="profile-detail">
        <div class="profile">
            <img src="../uploaded_files/<?= htmlspecialchars($fetch_profile['image']); ?>" class="logo-img" width="100">
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
            <div class="flex-btn">
                <a href="admin_profile.php" class="btn">Profile</a>
                <a href="../components/admin_logout.php" onclick="return confirm('Logout?');" class="btn">Log Out</a>
            </div>
        </div>
    </div>
</header>
<div class="sidebar-container">
    <div class="sidebar">
        <div class="profile">
            <img src="../uploaded_files/<?= htmlspecialchars($fetch_profile['image']); ?>" class="logo-img">
            <p><?= htmlspecialchars($fetch_profile['name']); ?></p>
        </div>
        <h5>Menu</h5>
        <div class="navbar">
            <ul>
                <li><a href="admin_dashboard.php"><i class="bx bxs-home-smile"></i>Dashboard</a></li>
                <li><a href="admin_view_product.php"><i class="bx bxs-food-menu"></i>View Product</a></li>
                <li><a href="admin_view_post.php"><i class="bx bxs-food-menu"></i>View Post</a></li>
                <li><a href="admin_accounts.php"><i class="bx bxs-user-detail"></i>Accounts</a></li>
                <li><a href="../components/admin_logout.php" onclick="return confirm('Logout?');"><i class="bx bx-log-out"></i>Log Out</a></li>
            </ul>
        </div>
        <h5>Find Us</h5>
        <div class="social-links">
            <i class="bx bxl-facebook"></i>
            <i class="bx bxl-instagram-alt"></i>
            <i class="bx bxl-linkedin"></i>
            <i class="bx bxl-twitter"></i>
            <i class="bx bxl-pinterest-alt"></i>
        </div>
    </div>
</div>
