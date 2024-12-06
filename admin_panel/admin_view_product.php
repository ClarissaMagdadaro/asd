<?php
include '../components/connect.php';

// Function to delete a product and notify the seller
function deleteProduct($product_id) {
    global $conn;

    // Get the seller ID associated with the product
    $get_seller = $conn->prepare("SELECT seller_id FROM products WHERE id = ?");
    $get_seller->execute([$product_id]);
    $seller = $get_seller->fetch(PDO::FETCH_ASSOC);

    if ($seller) {
        $seller_id = $seller['seller_id'];

        // Delete the product
        $delete_product = $conn->prepare("DELETE FROM products WHERE id = ?");
        $delete_product->execute([$product_id]);

        // Notify the seller by email
        $get_seller_email = $conn->prepare("SELECT email FROM sellers WHERE id = ?");
        $get_seller_email->execute([$seller_id]);
        $seller_email = $get_seller_email->fetchColumn();

        if ($seller_email) {
            $subject = "Product Removed from Marketplace";
            $message = "Dear Seller, \n\nYour product with ID: $product_id has been removed from our marketplace due to policy violations or administrative reasons. Please contact us for more details.\n\nBest regards,\nAdmin Team";
            $headers = "From: admin@yourwebsite.com";

            mail($seller_email, $subject, $message, $headers);
        }

        return true;
    }
    return false;
}

// Function to ban a seller and notify them
function banSeller($seller_id) {
    global $conn;

    // Update the seller's status to banned
    $ban_seller = $conn->prepare("UPDATE sellers SET status = 'banned' WHERE id = ?");
    $ban_seller->execute([$seller_id]);

    // Notify the seller by email
    $get_seller_email = $conn->prepare("SELECT email FROM sellers WHERE id = ?");
    $get_seller_email->execute([$seller_id]);
    $seller_email = $get_seller_email->fetchColumn();

    if ($seller_email) {
        $subject = "Your Seller Account has been Banned";
        $message = "Dear Seller, \n\nYour account has been banned due to violation of our terms and conditions. Please contact us for more information.\n\nBest regards,\nAdmin Team";
        $headers = "From: admin@yourwebsite.com";

        mail($seller_email, $subject, $message, $headers);
    }

    return true;
}

// Handle product deletion
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    if (deleteProduct($product_id)) {
        echo "<script>alert('Product has been deleted and seller notified.'); window.location.href='view_products.php';</script>";
    } else {
        echo "<script>alert('Product deletion failed.'); window.location.href='view_products.php';</script>";
    }
}

// Handle banning a seller
if (isset($_POST['ban_seller'])) {
    $seller_id = $_POST['seller_id'];

    if (banSeller($seller_id)) {
        echo "<script>alert('Seller has been banned and notified.'); window.location.href='view_products.php';</script>";
    } else {
        echo "<script>alert('Failed to ban the seller.'); window.location.href='view_products.php';</script>";
    }
}

// Select all products
$select_products = $conn->prepare("SELECT * FROM products");
$select_products->execute();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Products</title>
    <link rel="stylesheet" type="text/css" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
</head>
<body>
    
<?php include '../components/admin_header.php'; ?>
    <div class="main-container">
<section class="view-products">
    <div class="heading">
        <h1>View Products</h1>
    </div>

    <div class="product-container">
        <?php while ($product = $select_products->fetch(PDO::FETCH_ASSOC)) { ?>
        <div class="product-box">
            <h3><?= $product['name']; ?></h3>
            <p><?= $product['product_detail']; ?></p>
            <p>$<?= $product['price']; ?></p>
            
            <!-- Product action buttons -->
            <form action="" method="POST">
                <!-- Delete Product -->
                <button type="submit" name="delete_product" onclick="return confirm('Are you sure you want to delete this product?');">Delete</button>
                <input type="hidden" name="product_id" value="<?= $product['id']; ?>">
            </form>
            
            <!-- Ban Seller -->
            <form action="" method="POST">
                <button type="submit" name="ban_seller" onclick="return confirm('Are you sure you want to ban this seller?');">Ban Seller</button>
                <input type="hidden" name="seller_id" value="<?= $product['seller_id']; ?>">
            </form>
        </div>
        <?php } ?>
    </div>
</section>
