<?php
// Expects $page_title and $active to be set by the including page
$user = current_user();
$role = $user['role'] ?? '';
$root = $role === 'admin' ? BASE_URL . '/admin' : BASE_URL . '/technician';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($page_title ?? 'AMC System') ?></title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">CCTV<span>AMC</span></div>
        <nav>
        <?php if ($role === 'admin'): ?>
            <a href="<?= $root ?>/dashboard.php" class="<?= $active==='dashboard'?'active':'' ?>">📊 Dashboard</a>
            <a href="<?= $root ?>/customers.php" class="<?= $active==='customers'?'active':'' ?>">👥 Customers</a>
            <a href="<?= $root ?>/amc.php" class="<?= $active==='amc'?'active':'' ?>">🛠 AMC Contracts</a>
            <a href="<?= $root ?>/expiring_amc.php" class="<?= $active==='expiring'?'active':'' ?>">⏰ Expiring AMCs</a>
            <a href="<?= $root ?>/technicians.php" class="<?= $active==='technicians'?'active':'' ?>">🧑‍🔧 Technicians</a>
            <a href="<?= $root ?>/invoices.php" class="<?= $active==='invoices'?'active':'' ?>">🧾 Invoices</a>
            <a href="<?= $root ?>/reports.php" class="<?= $active==='reports'?'active':'' ?>">📈 Reports</a>
        <?php elseif ($role === 'technician'): ?>
            <a href="<?= $root ?>/dashboard.php" class="<?= $active==='dashboard'?'active':'' ?>">📊 Dashboard</a>
            <a href="<?= $root ?>/assigned_sites.php" class="<?= $active==='sites'?'active':'' ?>">📍 Assigned Sites</a>
        <?php endif; ?>
            <a href="<?= BASE_URL ?>/logout.php">🚪 Logout</a>
        </nav>
    </aside>
    <div class="main">
        <div class="topbar">
            <h2><?= e($page_title ?? '') ?></h2>
            <div class="user-chip">👤 <?= e($user['name'] ?? '') ?> (<?= e(ucfirst($role)) ?>)</div>
        </div>
        <div class="content">
