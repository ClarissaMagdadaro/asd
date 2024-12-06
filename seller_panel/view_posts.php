<?php
// Include the database connection
include '../components/connect.php';

// Check if the user is logged in via cookies or session
if (!isset($_COOKIE['user_id']) || empty($_COOKIE['user_id'])) {
    echo "You must be logged in to view posts.";
    exit();
}

$user_id = $_COOKIE['user_id']; // Logged-in user ID

// Fetch all posts from the database
$query = $conn->prepare("SELECT * FROM posts ORDER BY id DESC");
$query->execute();
$posts = $query->fetchAll(PDO::FETCH_ASSOC);

// Handle like functionality
if (isset($_POST['like'])) {
    $post_id = $_POST['post_id'];

    // Check if the user has already liked the post
    $check_like = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
    $check_like->execute([$post_id, $user_id]);

    if ($check_like->rowCount() == 0) {
        // If not liked, insert into likes table
        $insert_like = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
        $insert_like->execute([$post_id, $user_id]);
    } else {
        // If already liked, remove like (unlike)
        $remove_like = $conn->prepare("DELETE FROM likes WHERE post_id = ? AND user_id = ?");
        $remove_like->execute([$post_id, $user_id]);
    }
}

// Handle comment functionality
if (isset($_POST['comment'])) {
    $post_id = $_POST['post_id'];
    $comment = htmlspecialchars($_POST['comment_text']);
    
    // Insert the comment into the database
    $insert_comment = $conn->prepare("INSERT INTO comments (post_id, user_id, comment) VALUES (?, ?, ?)");
    $insert_comment->execute([$post_id, $user_id, $comment]);
}

// Fetch the like counts and comments for each post
$like_counts = [];
foreach ($posts as $post) {
    $post_id = $post['id'];

    // Get the like count for each post
    $like_count = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $like_count->execute([$post_id]);
    $like_counts[$post_id] = $like_count->fetchColumn();
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>View Posts</title>
    <link rel="stylesheet" type="text/css" href="../css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css">
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
            <h1>Post Feed</h1>

            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-header">
                            <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                            <p class="category">Category: <?php echo htmlspecialchars($post['category']); ?></p>
                        </div>

                        <div class="post-image">
                            <img src="../uploads/<?php echo htmlspecialchars($post['image']); ?>" alt="Post Image">
                        </div>

                        <div class="post-content">
                            <p><?php echo htmlspecialchars($post['description']); ?></p>

                            <!-- Display Event Date if the Post is an Event -->
                            <?php if ($post['is_event']): ?>
                                <p class="event-date">
                                    <strong>Event Date:</strong> 
                                    <?php echo htmlspecialchars(date("F j, Y", strtotime($post['event_date']))); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Post Actions -->
                        <div class="post-actions">
                            <form method="POST" class="like-form">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                <button type="submit" name="like" class="like-btn">
                                    <?php if (isset($like_counts[$post['id']]) && $like_counts[$post['id']] > 0): ?>
                                        <i class="fa fa-thumbs-down"></i> Unlike
                                    <?php else: ?>
                                        <i class="fa fa-thumbs-up"></i> Like
                                    <?php endif; ?>
                                </button>
                                <span class="like-count"><?php echo $like_counts[$post['id']]; ?> Likes</span>
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
