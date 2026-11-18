<?php
require __DIR__ . '/../includes/functions.php';

require_role('admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = (int)($_POST['job_id'] ?? 0);
    $st = db()->prepare('SELECT 1 FROM jobs WHERE id = ?');
    $st->execute([$jobId]);
    if (!$st->fetch()) {
        abort(404, 'Job not found.');
    }
    $st = db()->prepare('UPDATE jobs SET is_active = 1 - is_active WHERE id = ?');
    $st->execute([$jobId]);
    flash('success', 'Job status updated.');
}
redirect('admin/index.php');
