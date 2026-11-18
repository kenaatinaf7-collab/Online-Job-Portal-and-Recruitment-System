<?php
require __DIR__ . '/../includes/functions.php';

$user = require_role('seeker');
$st = db()->prepare(
    'SELECT a.*, j.title, u.company FROM applications a
     JOIN jobs j ON j.id = a.job_id JOIN users u ON u.id = j.employer_id
     WHERE a.seeker_id = ? ORDER BY a.created_at DESC, a.id DESC');
$st->execute([$user['id']]);
$apps = $st->fetchAll();

$pageTitle = 'My applications';
include __DIR__ . '/../includes/header.php';
?>
<h1>My applications</h1>
<?php if ($apps): ?>
<div class="table-wrap"><table>
  <tr><th>Job</th><th>Company</th><th>Applied</th><th>Status</th></tr>
  <?php foreach ($apps as $a): ?>
  <tr>
    <td><a href="<?= e(url('job.php?id=' . $a['job_id'])) ?>"><?= e($a['title']) ?></a></td>
    <td><?= e($a['company']) ?></td><td><?= e($a['created_at']) ?></td>
    <td><span class="tag <?= e($a['status']) ?>"><?= e($a['status']) ?></span></td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php else: ?>
<div class="card">You have not applied for any job yet. <a href="<?= e(url('index.php')) ?>">Browse jobs</a></div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
