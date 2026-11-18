<?php
// Include database configuration and verify employer role
require_once '../config.php';
checkRole('employer');

$job_id = filter_var($_GET['job_id'] ?? null, FILTER_VALIDATE_INT);

if (!$job_id) {
    header("Location: index.php");
    exit();
}

try {
    // Verify job belongs to this employer
    $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = ? AND employer_id = ?");
    $stmt->execute([$job_id, $_SESSION['user_id']]);
    $job = $stmt->fetch();

    if (!$job) {
        die("Job not found or access denied.");
    }

    // Handle status update form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['app_id'], $_POST['status'])) {
        $app_id = filter_var($_POST['app_id'], FILTER_VALIDATE_INT);
        $new_status = $_POST['status'];
        
        if (in_array($new_status, ['pending', 'reviewed', 'shortlisted', 'rejected'])) {
            $update = $pdo->prepare("UPDATE applications SET status = ? WHERE id = ?");
            $update->execute([$new_status, $app_id]);
            header("Location: applicants.php?job_id=" . $job_id);
            exit();
        }
    }

    // Fetch applications for this job
    $app_stmt = $pdo->prepare("
        SELECT a.*, u.full_name, u.email, u.phone 
        FROM applications a 
        JOIN users u ON a.seeker_id = u.id 
        WHERE a.job_id = ? 
        ORDER BY a.applied_at DESC
    ");
    $app_stmt->execute([$job_id]);
    $applicants = $app_stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants for <?= htmlspecialchars($job['title']) ?> - Employer Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">Employer Panel</a>
        <a href="index.php" class="btn btn-outline-light btn-sm">&larr; Back to Dashboard</a>
    </div>
</nav>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Applicants for: <?= htmlspecialchars($job['title']) ?></h3>
            <p class="text-muted mb-0 small">Total Applications Received: <?= count($applicants) ?></p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Applicant Name</th>
                        <th>Contact Email</th>
                        <th>Phone</th>
                        <th>Cover Letter</th>
                        <th>CV / Resume</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applicants)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No applications received for this job yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applicants as $app): ?>
                            <tr>
                                <td class="fw-semibold"><?= htmlspecialchars($app['full_name']) ?></td>
                                <td><?= htmlspecialchars($app['email']) ?></td>
                                <td><?= htmlspecialchars($app['phone'] ?? 'N/A') ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#coverModal<?= $app['id'] ?>">
                                        View Note
                                    </button>

                                    <!-- Cover Letter Modal -->
                                    <div class="modal fade" id="coverModal<?= $app['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Cover Letter from <?= htmlspecialchars($app['full_name']) ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p style="white-space: pre-line;"><?= !empty($app['cover_letter']) ? htmlspecialchars($app['cover_letter']) : 'No cover letter provided.' ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <a href="../assets/uploads/<?= htmlspecialchars($app['cv_file']) ?>" class="btn btn-sm btn-outline-primary" target="_blank">Download CV</a>
                                </td>
                                <td>
                                    <?php
                                        $badgeClass = match($app['status']) {
                                            'shortlisted' => 'bg-success',
                                            'reviewed'    => 'bg-info text-dark',
                                            'rejected'    => 'bg-danger',
                                            default       => 'bg-warning text-dark',
                                        };
                                    ?>
                                    <span class="badge <?= $badgeClass ?> text-uppercase"><?= htmlspecialchars($app['status']) ?></span>
                                </td>
                                <td>
                                    <form method="POST" action="applicants.php?job_id=<?= $job_id ?>" class="d-flex gap-1">
                                        <input type="hidden" name="app_id" value="<?= $app['id'] ?>">
                                        <select name="status" class="form-select form-select-sm" style="width: 130px;" onchange="this.form.submit()">
                                            <option value="pending" <?= $app['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="reviewed" <?= $app['status'] === 'reviewed' ? 'selected' : '' ?>>Reviewed</option>
                                            <option value="shortlisted" <?= $app['status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlisted</option>
                                            <option value="rejected" <?= $app['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                        </select>
                                    </form>
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