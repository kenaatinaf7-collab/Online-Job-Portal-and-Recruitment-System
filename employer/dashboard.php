<?php
require __DIR__ . '/../includes/functions.php';

$user = require_role('employer');
$st = db()->prepare(
    'SELECT j.*, (SELECT COUNT(*) FROM applications a WHERE a.job_id = j.id) AS applicants
     FROM jobs j WHERE j.employer_id = ? ORDER BY j.created_at DESC, j.id DESC');
$st->execute([$user['id']]);
$jobs = $st->fetchAll();

$pageTitle = 'My jobs';
include __DIR__ . '/../includes/header.php';
?>
<h1>My job postings</h1>
<p><a class="btn" href="<?= e(url('employer/post_job.php')) ?>">Post a new job</a></p>
<?php if ($jobs): ?>
<div class="table-wrap"><table>
  <tr><th>Title</th><th>Status</th><th>Applicants</th><th>Actions</th></tr>
  <?php foreach ($jobs as $j): ?>
  <tr>
    <td><a href="<?= e(url('job.php?id=' . $j['id'])) ?>"><?= e($j['title']) ?></a></td>
    <td><?= (int)$j['is_active'] ? '<span class="tag hired">Open</span>' : '<span class="tag closed">Closed</span>' ?></td>
    <td><?= (int)$j['applicants'] ?></td>
    <td>
      <a class="btn secondary small" href="<?= e(url('employer/applicants.php?id=' . $j['id'])) ?>">View applicants</a>
      <form class="inline" method="post" action="<?= e(url('employer/toggle_job.php')) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="job_id" value="<?= (int)$j['id'] ?>">
        <button class="btn secondary small" type="submit"><?= (int)$j['is_active'] ? 'Close' : 'Reopen' ?></button>
      </form>
    </td>
  </tr>
  <?php endforeach; ?>
</table></div>
<?php else: ?>
<div class="card">You have not posted any job yet.</div>
<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>
