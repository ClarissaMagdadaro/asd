<?php
include '../components/connect.php'; 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel - Pending Approvals</title>
    <link rel="stylesheet" type="text/css" href="admin_style.css">
</head>
<body>
    <?php include '../components/admin_header.php'; ?>

    <section class="test-section">
        <div class="heading">
            <h1>Pending Approvals</h1>
            <img src="../image/separator-img.png" alt="separator">
        </div>
        <div class="box-container">
            <?php
            $pending_posts = $conn->prepare("SELECT * FROM seller_posts WHERE status = 'pending'");
            $pending_posts->execute();

            if ($pending_posts->rowCount() > 0) {
                while ($post = $pending_posts->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <div class="box">
                        <h3><?= htmlspecialchars($post['title']); ?></h3>
                        <p>Description: <?= htmlspecialchars($post['description']); ?></p>
                        <p>Status: <span style="color: orange;"><?= htmlspecialchars($post['status']); ?></span></p>
                        <form action="approve_seller.php" method="post">
                            <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
                            <button type="submit" name="approve" class="btn">Approve</button>
                            <button type="submit" name="reject" class="btn">Reject</button>
                        </form>
                    </div>
                    <?php
                }
            } else {
                echo '<p>No pending approvals at this time.</p>';
            }
            ?>
        </div>
    </section>
</body>
</html>
