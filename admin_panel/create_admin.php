<?php
include '../components/connect.php';

// Check if admin already exists
$query = $conn->prepare("SELECT COUNT(*) FROM admins WHERE email = ?");
$query->execute(['admin@example.com']);
$exists = $query->fetchColumn();

if ($exists == 0) {
    // Insert default admin account
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_admin = $conn->prepare("INSERT INTO admins (email, password, name) VALUES (?, ?, ?)");
    $insert_admin->execute(['admin@example.com', $hashed_password, 'Admin']);
    echo "Default admin account created successfully.";
} else {
    echo "Admin account already exists.";
}
?>
