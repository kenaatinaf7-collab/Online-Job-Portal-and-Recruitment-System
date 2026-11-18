<?php
require __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    unset($_SESSION['user_id']);
    session_regenerate_id(true);
    flash('success', 'You have been logged out.');
}
redirect('index.php');
