<?php
// Include the database connection
include '../components/connect.php';

// Check if the user is logged in via cookies or session
if (!isset($_COOKIE['user_id']) || empty($_COOKIE['user_id'])) {
    echo "You must be logged in to view posts.";
    exit();
}

$user_id = $_COOKIE['user_id']; // Logged-in user ID

// Fetch the role of the logged-in user to check if they are an admin
$query = $conn->prepare("SELECT role FROM sellers WHERE id = ?");
$query->execute([$user_id]);
$user = $query->fetch(PDO::FETCH_ASSOC);

if (!$user || $user['role'] !== 'admin') {
    echo "Access denied. Admin privileges are required.";
    exit();
}

// Fetch all posts from the database
$query = $conn->prepare("SELECT * FROM posts ORDER BY id DESC");
$query->execute();
$posts = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle admin actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_post'])) {
        $post_id = $_POST['post_id'];

        // Delete the post and related likes and comments
        $delete_post = $conn->prepare("DELETE FROM posts WHERE id = ?");
        $delete_post->execute([$post_id]);

        $delete_likes = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
        $delete_likes->execute([$post_id]);

        $delete_comments = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
        $delete_comments->execute([$post_id]);

        echo "<script>alert('Post deleted successfully!');</script>";
    }

    if (isset($_POST['ban_seller'])) {
        $seller_id = $_POST['seller_id'];

        // Ban the seller
        $ban_seller = $conn->prepare("UPDATE sellers SET banned = 1 WHERE id = ?");
        $ban_seller->execute([$seller_id]);

        echo "<script>alert('Seller banned successfully!');</script>";
    }
}

// Fetch the like counts and comments for each post
$like_counts = [];
$comment_counts = [];
foreach ($posts as $post) {
    $post_id = $post['id'];

    // Get the like count for each post
    $like_count = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $like_count->execute([$post_id]);
    $like_counts[$post_id] = $like_count->fetchColumn();

    // Get the comment count for each post
    $comment_count = $conn->prepare("SELECT COUNT(*) FROM comments WHERE post_id = ?");
    $comment_count->execute([$post_id]);
    $comment_counts[$post_id] = $comment_count->fetchColumn();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - View Posts</title>
    <link rel="stylesheet" href="../css/view_post.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <img src="../image/logo.png" alt="Logo" width="60">
        </div>
        <div class="right">
            <div class="bx bxs-user" id="user-btn"></div>
            <div class="toggle-btn"><i class="bx bx-menu"></i></div>
        </div>
    </header>

    <!-- Sidebar -->
    <div class="sidebar-container">
        <div class="sidebar">
            <h5>Menu</h5>
            <div class="navbar">
                <ul>
                    <li><a href="dashboard.php"><i class="bx bxs-home-smile"></i> Dashboard</a></li>
                    <li><a href="add_products.php"><i class="bx bxs-shopping-bags"></i> Add Products</a></li>
                    <li><a href="view_product.php"><i class="bx bxs-food-menu"></i> View Product</a></li>
                    <li><a href="add_posts.php"><i class="bx bxs-shopping-bags"></i> Add Posts</a></li>
                    <li><a href="view_posts.php"><i class="bx bxs-food-menu"></i> View Post</a></li>
                    <li><a href="user_accounts.php"><i class="bx bxs-user-detail"></i> Accounts</a></li>
                    <li><a href="../components/admin_logout.php" onclick="return confirm('Logout?');"><i class="bx bx-log-out"></i> Log Out</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content Area -->
    <div class="main-container">
        <section class="feed">
            <h1>Admin Post Management</h1>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                            <p class="category">Category: <?php echo htmlspecialchars($post['category']); ?></p>
                            <p>Posted by Seller ID: <?php echo htmlspecialchars($post['user_id']); ?></p>
                        </div>

                        <div class="post-image">
                            <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                        </div>

                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['description']); ?></p>
                        </div>

                        <?php
                        // Fetch the like count and check if the user has liked the post
                        $like_count = $like_counts[$post['id']];
                        ?>

                        <div class="post-actions">
                            <p><span class="like-count"><?php echo $like_count; ?> Likes</span></p>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="delete_post" class="admin-btn delete-btn">
                                    <i class="fa fa-trash"></i> Delete Post
                                </button>
                            </form>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="seller_id" value="<?php echo $post['user_id']; ?>">
                                <button type="submit" name="ban_seller" class="admin-btn ban-btn">
                                    <i class="fa fa-ban"></i> Ban Seller
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts available.</p>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>
