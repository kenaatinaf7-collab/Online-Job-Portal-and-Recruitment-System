<?php $u = current_user(); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle ?? 'Job Portal') ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body>
<header class="nav">
  <div class="inner">
    <a class="brand" href="<?= e(url('index.php')) ?>">JobPortal</a>
    <a href="<?= e(url('index.php')) ?>">Jobs</a>
    <?php if ($u): ?>
      <?php if ($u['role'] === 'seeker'): ?><a href="<?= e(url('seeker/dashboard.php')) ?>">My applications</a><?php endif; ?>
      <?php if ($u['role'] === 'employer'): ?>
        <a href="<?= e(url('employer/dashboard.php')) ?>">My jobs</a>
        <a href="<?= e(url('employer/post_job.php')) ?>">Post a job</a>
      <?php endif; ?>
      <?php if ($u['role'] === 'admin'): ?><a href="<?= e(url('admin/index.php')) ?>">Admin</a><?php endif; ?>
      <span class="spacer"></span>
      <span class="meta"><?= e($u['name']) ?> (<?= e($u['role']) ?>)</span>
      <form class="inline" method="post" action="<?= e(url('logout.php')) ?>">
        <?= csrf_field() ?>
        <button class="btn secondary small" type="submit">Log out</button>
      </form>
    <?php else: ?>
      <span class="spacer"></span>
      <a href="<?= e(url('login.php')) ?>">Log in</a>
      <a class="btn small" href="<?= e(url('register.php')) ?>">Sign up</a>
    <?php endif; ?>
  </div>
</header>
<main>
  <?php foreach (pull_flashes() as [$type, $message]): ?>
    <div class="flash <?= e($type) ?>"><?= e($message) ?></div>
  <?php endforeach; ?>
