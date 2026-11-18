<?php
require __DIR__ . '/../includes/functions.php';

$user = require_role('employer');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('employer/dashboard.php');
}

$appId = (int)($_POST['app_id'] ?? 0);
$st = db()->prepare(
    'SELECT a.id, a.job_id, j.employer_id FROM applications a
     JOIN jobs j ON j.id = a.job_id WHERE a.id = ?');
$st->execute([$appId]);
$row = $st->fetch();
if (!$row) {
    abort(404, 'Application not found.');
}
if ((int)$row['employer_id'] !== (int)$user['id']) {
    abort(403, 'You can only manage applications for your own jobs.');
}
$status = (string)($_POST['status'] ?? '');
if (!in_array($status, STATUSES, true)) {
    abort(400, 'Invalid status.');
}

$st = db()->prepare('UPDATE applications SET status = ? WHERE id = ?');
$st->execute([$status, $appId]);
flash('success', 'Application status updated.');
redirect('employer/applicants.php?id=' . (int)$row['job_id']);
