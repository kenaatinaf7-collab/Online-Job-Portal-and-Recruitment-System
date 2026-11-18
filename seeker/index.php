<?php
// Include database configuration and verify job seeker role
require_once '../config.php';
checkRole('seeker');

$seeker_id = $_SESSION['user_id'];

try {
    // Fetch all applications submitted by the logged-in job seeker
    $stmt = $pdo->prepare("
        SELECT a.*, j.title AS job_title, j.location, j.job_type, u.company_name 
        FROM applications a 
        JOIN jobs j ON a.job_id = j.id 
        JOIN users u ON j.employer_id = u.id 
        WHERE a.seeker_id = ? 
        ORDER BY a.applied_at DESC
    ");
    $stmt->execute([$seeker_id]);
    $applications = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Seeker Dashboard - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Seeker Dashboard</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 small">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Browse Jobs</a>
            <a href="../logout.php" class="btn btn-light btn-sm text-success fw-semibold">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">My Job Applications</h3>
        <a href="../index.php" class="btn btn-primary fw-semibold">Find More Jobs</a>
    </div>

    <!-- Applied Jobs Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Job Title</th>
                        <th>Company</th>
                        <th>Location</th>
                        <th>Applied Date</th>
                        <th>Application Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                You haven't applied for any jobs yet. <a href="../index.php">Explore open vacancies now!</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="fw-semibold">
                                    <a href="../job.php?id=<?= $app['job_id'] ?>" class="text-decoration-none">
                                        <?= htmlspecialchars($app['job_title']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars($app['company_name'] ?? 'N/A') ?></td>
                                <td><?= htmlspecialchars($app['location']) ?></td>
                                <td class="small text-muted"><?= date('M d, Y', strtotime($app['applied_at'])) ?></td>
                                <td>
                                    <?php
                                        $badgeClass = match($app['status']) {
                                            'shortlisted' => 'bg-success',
                                            'reviewed'    => 'bg-info text-dark',
                                            'rejected'    => 'bg-danger',
                                            default       => 'bg-warning text-dark',
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> text-uppercase">
                                        <?= htmlspecialchars($app['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>