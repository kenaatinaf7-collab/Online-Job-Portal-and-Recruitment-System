<?php
// Include database configuration and verify employer role
require_once '../config.php';
checkRole('employer');

$employer_id = $_SESSION['user_id'];

try {
    // Fetch jobs posted by the logged-in employer along with application counts
    $stmt = $pdo->prepare("
        SELECT j.*, COUNT(a.id) AS applicant_count 
        FROM jobs j 
        LEFT JOIN applications a ON j.id = a.job_id 
        WHERE j.employer_id = ? 
        GROUP BY j.id 
        ORDER BY j.created_at DESC
    ");
    $stmt->execute([$employer_id]);
    $my_jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Employer Panel</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 small">Hello, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="../index.php" class="btn btn-outline-light btn-sm me-2">Home</a>
            <a href="../logout.php" class="btn btn-light btn-sm text-primary fw-semibold">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">My Posted Jobs</h3>
        <a href="post-job.php" class="btn btn-success fw-semibold">+ Post a New Job</a>
    </div>

    <!-- Posted Jobs List -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Job Title</th>
                        <th>Type</th>
                        <th>Location</th>
                        <th>Applicants</th>
                        <th>Deadline</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($my_jobs)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                You have not posted any jobs yet. Click <strong>"+ Post a New Job"</strong> to begin.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($my_jobs as $job): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($job['title']) ?></td>
                                <td><span class="badge bg-info text-dark"><?= htmlspecialchars($job['job_type']) ?></span></td>
                                <td><?= htmlspecialchars($job['location']) ?></td>
                                <td>
                                    <span class="badge bg-primary rounded-pill"><?= $job['applicant_count'] ?> Applicants</span>
                                </td>
                                <td class="small"><?= date('M d, Y', strtotime($job['deadline'])) ?></td>
                                <td>
                                    <span class="badge <?= $job['status'] === 'open' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst(htmlspecialchars($job['status'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="applicants.php?job_id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary">View Applicants</a>
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