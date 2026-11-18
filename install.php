<?php
/*
 * One-time setup page.
 *  1. Creates the database "jobportal" (if missing) and its tables.
 *  2. Creates the admin account with the email/password you type here.
 *  3. Optionally adds demo data.
 * DELETE this file after the installation is finished.
 */
require __DIR__ . '/includes/functions.php';

$done = false;
$errors = [];
$demo = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    $password = (string)($_POST['password'] ?? '');
    $demo = !empty($_POST['demo']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid admin email.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Admin password must be at least 8 characters.';
    }

    if (!$errors) {
        try {
            // 1. create the database
            $server = new PDO('mysql:host=' . DB_HOST . ';charset=utf8mb4', DB_USER, DB_PASS,
                              [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $server->exec('CREATE DATABASE IF NOT EXISTS `' . str_replace('`', '', DB_NAME) .
                          '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

            // 2. create the tables
            $pdo = db();
            $sql = file_get_contents(__DIR__ . '/database.sql');
            foreach (array_filter(array_map('trim', explode(';', $sql))) as $statement) {
                $pdo->exec($statement);
            }

            // 3. create the admin account
            $exists = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            if ($exists > 0) {
                $errors[] = 'Already installed: an admin account exists. Delete install.php.';
            } else {
                $st = $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES ('Administrator', ?, ?, 'admin')");
                $st->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);

                if ($demo) {
                    $pw = password_hash('demo123', PASSWORD_DEFAULT);
                    $pdo->prepare("INSERT INTO users (name, email, password_hash, role, company) VALUES ('Demo Employer', 'employer@demo.com', ?, 'employer', 'Abay Tech PLC')")->execute([$pw]);
                    $pdo->prepare("INSERT INTO users (name, email, password_hash, role) VALUES ('Demo Seeker', 'seeker@demo.com', ?, 'seeker')")->execute([$pw]);
                    $empId = (int)$pdo->query("SELECT id FROM users WHERE email = 'employer@demo.com'")->fetchColumn();
                    $jobs = [
                        ['Junior Web Developer', 'IT & Software', 'Addis Ababa', 'Full-time', '15,000 ETB', 'Build and maintain web applications using PHP and JavaScript.'],
                        ['Secondary School Teacher (Math)', 'Education', 'Adama', 'Full-time', '9,000 ETB', 'Teach mathematics for grades 9-12 and support student mentoring.'],
                        ['Accounting Intern', 'Finance & Accounting', 'Jimma', 'Internship', 'Negotiable', 'Assist the finance team with bookkeeping and monthly reports.'],
                    ];
                    $ins = $pdo->prepare('INSERT INTO jobs (employer_id, title, category, location, job_type, salary, description) VALUES (?, ?, ?, ?, ?, ?, ?)');
                    foreach ($jobs as $j) {
                        $ins->execute(array_merge([$empId], $j));
                    }
                }
                $done = true;
            }
        } catch (Throwable $ex) {
            $errors[] = 'Database error: ' . $ex->getMessage() .
                        ' (Is WAMP running and the icon green? Check DB_USER / DB_PASS in config.php.)';
        }
    }
}

$pageTitle = 'Install';
include __DIR__ . '/includes/header.php';
?>
<div class="narrow card">
  <h1>Install Job Portal</h1>
  <?php if ($done): ?>
    <div class="flash success">Installation finished.</div>
    <p><strong>Now delete the file <code>install.php</code></strong> from the project folder, then
       <a href="<?= e(url('login.php')) ?>">log in</a> with the admin account you just created.</p>
  <?php else: ?>
    <?php foreach ($errors as $err): ?><div class="flash error"><?= e($err) ?></div><?php endforeach; ?>
    <p class="meta">Choose the admin account. The database and tables are created automatically.</p>
    <form method="post" class="stack">
      <?= csrf_field() ?>
      <label for="email">Admin email</label>
      <input id="email" type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" required>
      <label for="password">Admin password (min 8 characters)</label>
      <input id="password" type="password" name="password" required>
      <label class="show-pw">
        <input type="checkbox" onchange="document.getElementById('password').type = this.checked ? 'text' : 'password'">
        Show password
      </label>
      <label class="show-pw"><input type="checkbox" name="demo" value="1" <?= $demo ? 'checked' : '' ?>> Add demo data (employer, seeker, 3 jobs)</label>
      <p><button class="btn" type="submit">Install</button></p>
    </form>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
