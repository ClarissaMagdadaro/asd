<?php
session_start();
include '../components/connect.php';

// Fetch users and sellers from the database
$select_users = $conn->prepare("SELECT id, name AS username, profile_picture, status FROM users");
$select_users->execute();

$select_sellers = $conn->prepare("SELECT id, name AS username, profile_picture, status FROM sellers");
$select_sellers->execute();

// Function to handle approval, rejection, and deletion
function handleApprovalRejectionDeletion($type, $id, $action) {
    global $conn;
    $table = $type === 'user' ? 'users' : 'sellers';

    if ($action === 'approve') {
        $query = $conn->prepare("UPDATE $table SET status = 'approved' WHERE id = ?");
        $query->execute([$id]);
        return ucfirst($type) . ' approved successfully.';
    } elseif ($action === 'reject') {
        $query = $conn->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
        $query->execute([$id]);
        return ucfirst($type) . ' rejected.';
    } elseif ($action === 'delete') {
        $query = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $query->execute([$id]);
        return ucfirst($type) . ' deleted successfully.';
    }
}

// Handle form submission for approval, rejection, and deletion
if (isset($_POST['action_type']) && isset($_POST['id'])) {
    $type = $_POST['type'];
    $id = $_POST['id'];
    $action = $_POST['action_type'];
    $msg = handleApprovalRejectionDeletion($type, $id, $action);

    // Store the message based on action
    if (strpos($msg, 'approved') !== false || strpos($msg, 'deleted') !== false) {
        $success_msg[] = $msg;
    } else {
        $warning_msg[] = $msg;
    }
}
?>
<?php
session_start();
include '../components/connect.php';

// Fetch users and sellers from the database
$select_users = $conn->prepare("SELECT id, name AS username, profile_picture, status FROM users");
$select_users->execute();

$select_sellers = $conn->prepare("SELECT id, name AS username, profile_picture, status FROM sellers");
$select_sellers->execute();

// Function to handle approval, rejection, and deletion
function handleApprovalRejectionDeletion($type, $id, $action) {
    global $conn;
    $table = $type === 'user' ? 'users' : 'sellers';

    if ($action === 'approve') {
        $query = $conn->prepare("UPDATE $table SET status = 'approved' WHERE id = ?");
        $query->execute([$id]);
        return ucfirst($type) . ' approved successfully.';
    } elseif ($action === 'reject') {
        $query = $conn->prepare("UPDATE $table SET status = 'rejected' WHERE id = ?");
        $query->execute([$id]);
        return ucfirst($type) . ' rejected.';
    } elseif ($action === 'delete') {
        $query = $conn->prepare("DELETE FROM $table WHERE id = ?");
        $query->execute([$id]);
        return ucfirst($type) . ' deleted successfully.';
    }
}

// Handle form submission for approval, rejection, and deletion
if (isset($_POST['action_type']) && isset($_POST['id'])) {
    $type = $_POST['type'];
    $id = $_POST['id'];
    $action = $_POST['action_type'];
    $msg = handleApprovalRejectionDeletion($type, $id, $action);

    // Store the message based on action
    if (strpos($msg, 'approved') !== false || strpos($msg, 'deleted') !== false) {
        $success_msg[] = $msg;
    } else {
        $warning_msg[] = $msg;
    }
}
?>
