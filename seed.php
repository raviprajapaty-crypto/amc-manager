<?php
// =========================================================
// ONE-TIME SETUP SCRIPT
// Run this once in your browser after importing database/schema.sql
// It creates the default Admin + Technician logins with securely
// hashed passwords. DELETE this whole install/ folder after use.
// =========================================================
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

$done = [];
$errors = [];

function create_user($pdo, $name, $email, $mobile, $password, $role, &$done, &$errors) {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        $errors[] = "User with email $email already exists - skipped.";
        return;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, password, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $mobile, $hash, $role]);
    $done[] = "$role created -> Email: $email | Password: $password";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    create_user($pdo, 'Admin', 'admin@amc.com', '9999999999', 'admin123', 'admin', $done, $errors);
    create_user($pdo, 'Ramesh Kumar', 'tech@amc.com', '9876543210', 'tech123', 'technician', $done, $errors);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Setup - AMC System</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box" style="max-width:480px;">
        <h1>Initial Setup</h1>
        <p class="sub">Default Admin aur Technician login banayein</p>

        <?php foreach ($done as $d): ?>
            <div class="alert alert-success"><?= e($d) ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $er): ?>
            <div class="alert alert-error"><?= e($er) ?></div>
        <?php endforeach; ?>

        <?php if (empty($done) && empty($errors)): ?>
            <form method="POST">
                <p style="font-size:13px;color:#6b7280;margin-bottom:16px;">
                    Ye button dabane par 2 default logins ban jayenge:<br>
                    <b>Admin:</b> admin@amc.com / admin123<br>
                    <b>Technician:</b> tech@amc.com / tech123
                </p>
                <button type="submit" class="btn btn-block">Create Default Logins</button>
            </form>
        <?php else: ?>
            <a href="<?= BASE_URL ?>/index.php" class="btn btn-block">Go to Login Page</a>
            <p style="font-size:12px;color:#b91c1c;margin-top:16px;text-align:center;">
                ⚠ Security ke liye ab yeh <b>install/</b> folder server se delete kar dein.
            </p>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
