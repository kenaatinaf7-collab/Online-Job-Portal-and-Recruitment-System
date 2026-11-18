<?php
require __DIR__ . '/../includes/functions.php';

$user = require_role('employer');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $job = own_job_or_abort((int)($_POST['job_id'] ?? 0), $user);
    $st = db()->prepare('UPDATE jobs SET is_active = 1 - is_active WHERE id = ?');
    $st->execute([$job['id']]);
    flash('success', 'Job status updated.');
}
redirect('employer/dashboard.php');
