<?php
include '../components/connect.php';

// Check for seller authentication
if (isset($_COOKIE['seller_id'])) {
    $seller_id = $_COOKIE['seller_id'];
} else {
    header('location:../login.php');
    exit();
}

// Fetch seller profile information
$select_seller = $conn->prepare("SELECT * FROM `sellers` WHERE id = ?");
$select_seller->execute([$seller_id]);
$fetch_profile = $select_seller->fetch(PDO::FETCH_ASSOC);

// Handle case where no profile is found
if (!$fetch_profile) {
    echo "<script>alert('No profile found for the given seller ID. Please try again.');</script>";
    header('location:../login.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Figuras D Arte - Seller Dashboard</title>
        <link rel="stylesheet" type="text/css" href="../css/admin_style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    </head>
    <body>
        <div class="main-container">
            <?php include '../components/seller_header.php'; ?>
            <section class="dashboard">
                <div class="heading">
                    <h1>Dashboard</h1>
                    <img src="../image/separator-img.png" alt="✦ . ⁺ . ✦ . ⁺ . ✦">
                </div>
                <div class="box-container">
                    <div class="box">
                        <h3>Welcome!</h3>
                        <p><?= htmlspecialchars($fetch_profile['name'] ?? 'Unknown'); ?></p>
                        <a href="update.php" class="btn">Update Profile</a>
                    </div>
                    <!-- Additional boxes for messages, products, and other statistics -->
                </div>
            </section>
        </div>
        <script src="../js/admin_script.js"></script>
        <?php include '../components/alert.php'; ?>
    </body>
</html>
