<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function current_user() {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'role' => $_SESSION['user_role'],
    ];
}

// Call at the top of every admin page
function require_admin() {
    if (!is_logged_in() || $_SESSION['user_role'] !== 'admin') {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// Call at the top of every technician page
function require_technician() {
    if (!is_logged_in() || $_SESSION['user_role'] !== 'technician') {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

function e($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Recalculate AMC status based on end_date and visits used.
// Run this whenever AMC list is displayed to keep status fresh.
function refresh_amc_statuses(PDO $pdo) {
    $today = date('Y-m-d');
    $soon  = date('Y-m-d', strtotime('+30 days'));

    $pdo->prepare("UPDATE amc_contracts SET status='expired' WHERE end_date < ? AND status != 'expired'")
        ->execute([$today]);

    $pdo->prepare("UPDATE amc_contracts SET status='expiring' WHERE end_date >= ? AND end_date <= ? AND status NOT IN ('expired')")
        ->execute([$today, $soon]);

    $pdo->prepare("UPDATE amc_contracts SET status='active' WHERE end_date > ? AND status NOT IN ('expired')")
        ->execute([$soon]);
}
