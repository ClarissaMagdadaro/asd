<?php
include 'components/connect.php';

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
} else {
    $user_id = '';
    header('location:web_login.php');
}

if (isset($_POST['place_order'])) {
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $number = filter_var($_POST['number'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

    $address = filter_var($_POST['flat'], FILTER_SANITIZE_STRING) . ', ' . 
               filter_var($_POST['street'], FILTER_SANITIZE_STRING) . ', ' . 
               filter_var($_POST['city'], FILTER_SANITIZE_STRING) . ', ' . 
               filter_var($_POST['country'], FILTER_SANITIZE_STRING) . ', ' . 
               filter_var($_POST['pin'], FILTER_SANITIZE_STRING);

    $address_type = filter_var($_POST['address_type'], FILTER_SANITIZE_STRING);
    $method = 'Cash on Delivery'; // Fixed to Cash on Delivery

    // Check if 'Save my details for next time' is checked
    if (isset($_POST['save_details']) && $_POST['save_details'] == 'on') {
        // Update user details in the database
        $update_user = $conn->prepare("UPDATE `users` SET name = ?, number = ?, email = ?, address = ?, address_type = ? WHERE id = ?");
        $update_user->execute([$name, $number, $email, $address, $address_type, $user_id]);
    }

    try {
        $conn->beginTransaction();

        // Handle single product checkout
        if (isset($_GET['get_id'])) {
            $get_product = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
            $get_product->execute([$_GET['get_id']]);

            if ($get_product->rowCount() > 0) {
                $fetch_p = $get_product->fetch(PDO::FETCH_ASSOC);
                $seller_id = $fetch_p['seller_id'];

                $insert_order = $conn->prepare("
                    INSERT INTO `orders` 
                    (user_id, seller_id, name, number, email, address, address_type, method, product_id, price, qty) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert_order->execute([ 
                    $user_id, $seller_id, $name, $number, $email, $address, $address_type, $method,
                    $fetch_p['id'], $fetch_p['price'], 1
                ]);
            } else {
                throw new Exception('Product not found.');
            }
        } else {
            // Handle cart checkout
            $verify_cart = $conn->prepare("SELECT * FROM `cart` WHERE user_id = ?");
            $verify_cart->execute([$user_id]);

            if ($verify_cart->rowCount() > 0) {
                while ($f_cart = $verify_cart->fetch(PDO::FETCH_ASSOC)) {
                    $s_products = $conn->prepare("SELECT * FROM `products` WHERE id = ? LIMIT 1");
                    $s_products->execute([$f_cart['product_id']]);

                    if ($s_products->rowCount() > 0) {
                        $f_product = $s_products->fetch(PDO::FETCH_ASSOC);
                        $seller_id = $f_product['seller_id'];

                        $insert_order = $conn->prepare("
                            INSERT INTO `orders` 
                            (user_id, seller_id, name, number, email, address, address_type, method, product_id, price, qty) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ");
                        $insert_order->execute([ 
                            $user_id, $seller_id, $name, $number, $email, $address, $address_type, $method,
                            $f_product['id'], $f_cart['price'], $f_cart['qty']
                        ]);
                    } else {
                        throw new Exception('Product in cart not found.');
                    }
                }

                // Clear cart after successful order placement
                $delete_cart = $conn->prepare("DELETE FROM `cart` WHERE user_id = ?");
                $delete_cart->execute([$user_id]);
            } else {
                throw new Exception('Cart is empty.');
            }
        }

        $conn->commit();
        header('location:order.php');
    } catch (Exception $e) {
        $conn->rollBack();
        $warning_msg[] = 'Error placing order: ' . $e->getMessage();
    }
}

// Fetch user details if the save option is checked for autofill
$user_details = [];
if (isset($user_id)) {
    $get_user_details = $conn->prepare("SELECT * FROM `users` WHERE id = ?");
    $get_user_details->execute([$user_id]);
    if ($get_user_details->rowCount() > 0) {
        $user_details = $get_user_details->fetch(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Figuras D' Arte - Checkout Page</title>
        <link rel="stylesheet" type="text/css" href="css/user_style.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
        <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel='stylesheet'>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@100..900&display=swap" rel="stylesheet">
    </head>
    <body>
        <?php include 'components/user_header.php'; ?>
        <div class="checkout">
            <div class="heading">
                <h1>Checkout Summary</h1>
                <img src="image/separator-img.png">
            </div>
            <div class="row">
                <form action="order.php" method="post" class="register">
                    <input type="hidden" name="p_id" value="<?= isset($get_id) ? $get_id : ''; ?>">
                    <h3>Billing Details</h3>
                    <div class="flex">
                        <div class="box">
                            <div class="input-field">
                                <p>Your Name <span>*</span></p>
                                <input type="text" name="name" required maxlength="50" placeholder="Enter your name..." class="input" value="<?= isset($user_details['name']) ? $user_details['name'] : ''; ?>">
                            </div>
                            <div class="input-field">
                                <p>Contact Number <span>*</span></p>
                                <input type="tel" name="number" required placeholder="Enter your contact number..." class="input" value="<?= isset($user_details['number']) ? $user_details['number'] : ''; ?>" maxlength="12" inputmode="numeric">
                            </div>
                            <div class="input-field">
                                <p>Your Email <span>*</span></p>
                                <input type="email" name="email" required maxlength="50" placeholder="Enter your email..." class="input" value="<?= isset($user_details['email']) ? $user_details['email'] : ''; ?>">
                            </div>
                            <div class="input-field">
                                <p>Payment Method <span>*</span></p>
                                <select name="method" class="input" disabled>
                                    <option value="Cash on Delivery" selected>Cash on Delivery</option>
                                </select>
                            </div>
                            <div class="input-field">
                                <p>Address Type <span>*</span></p>
                                <select name="address_type" class="input">
                                    <option value="Home" <?= isset($user_details['address_type']) && $user_details['address_type'] == 'Home' ? 'selected' : ''; ?>>Home</option>
                                    <option value="Office" <?= isset($user_details['address_type']) && $user_details['address_type'] == 'Office' ? 'selected' : ''; ?>>Office</option>
                                </select>
                            </div>
                        </div>
                        <div class="box">
                            <div class="input-field">
                                <p>Address Line 01<span>*</span></p>
                                <input type="text" name="flat" required maxlength="50" placeholder="e.g. flat or building name..." class="input" value="<?= isset($user_details['address']) ? explode(',', $user_details['address'])[0] : ''; ?>">
                            </div>
                            <div class="input-field">
                                <p>Address Line 02<span>*</span></p>
                                <input type="text" name="street" required maxlength="50" placeholder="e.g. street name..." class="input" value="<?= isset($user_details['address']) ? explode(',', $user_details['address'])[1] : ''; ?>">
                            </div>
                            <div class="input-field">
                                <p>City Name<span>*</span></p>
                                <input type="text" name="city" required maxlength="50" placeholder="e.g. city..." class="input" value="<?= isset($user_details['address']) ? explode(',', $user_details['address'])[2] : ''; ?>">
                            </div>
                            <div class="input-field">
                                <p>Country<span>*</span></p>
                                <input type="text" name="country" required maxlength="50" placeholder="e.g. country..." class="input" value="<?= isset($user_details['address']) ? explode(',', $user_details['address'])[3] : ''; ?>">
                            </div>
                            <div class="input-field">
                                <p>Postal Code<span>*</span></p>
                                <input type="text" name="pin" required maxlength="6" placeholder="e.g. postal code..." class="input" value="<?= isset($user_details['address']) ? explode(',', $user_details['address'])[4] : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="checkbox-container">
                        <input type="checkbox" name="save_details" value="on" id="save_details">
                        <label for="save_details">Save my details for next time</label>
                    </div>
                    <input type="submit" name="place_order" value="Place Order" class="btn">
                </form>
            </div>
        </div>
        <script src="js/script.js"></script>
    </body>
</html>
