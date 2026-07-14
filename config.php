<?php
// Base URL of your site - update after uploading to hosting
// Example: 'https://yourdomain.com/amc_system'
define('BASE_URL', 'http://localhost/amc_system');

define('UPLOAD_DIR', __DIR__ . '/../uploads/visit_photos/');
define('UPLOAD_URL', BASE_URL . '/uploads/visit_photos/');

date_default_timezone_set('Asia/Kolkata');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
