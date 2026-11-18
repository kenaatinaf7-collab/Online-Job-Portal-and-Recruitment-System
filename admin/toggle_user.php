<?php
require __DIR__ . '/../includes/functions.php';

require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int)($_POST['user_id'] ?? 0);
    $st = db()->prepare('SELECT * FROM users WHERE id = ?');
    $st->execute([$userId]);
    $target = $st->fetch();
    if (!$target) {
        abort(404, 'User not found.');
    }
    if ($target['role'] === 'admin') {
        flash('error', 'Admin accounts cannot be disabled here.');
    } else {
        $st = db()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = ?');
        $st->execute([$userId]);
        flash('success', 'User status updated.');
    }
}
redirect('admin/index.php');
