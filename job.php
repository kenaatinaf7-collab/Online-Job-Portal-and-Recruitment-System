<?php
// Include database configuration and helper functions
require_once 'config.php';

// Get and validate Job ID from URL
$job_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if (!$job_id) {
    header("Location: index.php");
    exit();
}

try {
    // Fetch job details along with employer's company name
    $stmt = $pdo->prepare("
        SELECT j.*, u.company_name, u.email AS employer_email, u.phone AS employer_phone 
        FROM jobs j 
        JOIN users u ON j.employer_id = u.id 
        WHERE j.id = ?
    ");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();

    if (!$job) {
        die("Job opening not found.");
    }

    // Check if current logged-in seeker has already applied for this job
    $already_applied = false;
    if (isLoggedIn() && $_SESSION['role'] === 'seeker') {
        $checkApp = $pdo->prepare("SELECT id, status FROM applications WHERE job_id = ? AND seeker_id = ?");
        $checkApp->execute([$job_id, $_SESSION['user_id']]);
        $existing_application = $checkApp->fetch();
        if ($existing_application) {
            $already_applied = true;
        }
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($job['title']) ?> - Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">JobPortal</a>
        <a href="index.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Listings</a>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
                
                <!-- Job Header -->
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary fw-semibold mb-2"><?= htmlspecialchars($job['job_type']) ?></span>
                        <h2 class="fw-bold text-dark mb-1"><?= htmlspecialchars($job['title']) ?></h2>
                        <p class="text-primary fw-semibold fs-5 mb-0"><?= htmlspecialchars($job['company_name']) ?></p>
                    </div>
                    <span class="badge <?= $job['status'] === 'open' ? 'bg-success' : 'bg-secondary' ?>">
                        <?= ucfirst(htmlspecialchars($job['status'])) ?>
                    </span>
                </div>

                <hr class="my-4">

                <!-- Job Quick Info -->
                <div class="row g-3 mb-4 bg-light p-3 rounded-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Location</small>
                        <span class="fw-semibold"><?= htmlspecialchars($job['location']) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Category</small>
                        <span class="fw-semibold"><?= htmlspecialchars($job['category']) ?></span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Salary</small>
                        <span class="fw-semibold"><?= !empty($job['salary']) ? htmlspecialchars($job['salary']) : 'Negotiable' ?></span>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Application Deadline</small>
                        <span class="fw-semibold text-danger"><?= date('M d, Y', strtotime($job['deadline'])) ?></span>
                    </div>
                </div>

                <!-- Job Description -->
                <div class="mb-4">
                    <h5 class="fw-bold text-dark">Job Description</h5>
                    <p class="text-secondary" style="white-space: pre-line;"><?= htmlspecialchars($job['description']) ?></p>
                </div>

                <!-- Job Requirements -->
                <div class="mb-5">
                    <h5 class="fw-bold text-dark">Requirements & Qualifications</h5>
                    <p class="text-secondary" style="white-space: pre-line;"><?= htmlspecialchars($job['requirements']) ?></p>
                </div>

                <!-- Action Section -->
                <div class="border-top pt-4 text-center">
                    <?php if (!isLoggedIn()): ?>
                        <p class="text-muted mb-3">You must be logged in as a Job Seeker to apply for this position.</p>
                        <a href="login.php" class="btn btn-primary px-4 fw-semibold">Login to Apply</a>
                    <?php elseif ($_SESSION['role'] !== 'seeker'): ?>
                        <div class="alert alert-warning mb-0">Only Job Seeker accounts can apply for job vacancies.</div>
                    <?php else: ?>
                        <?php if ($already_applied): ?>
                            <div class="alert alert-info mb-0">
                                You have already applied for this job. Status: <strong><?= ucfirst($existing_application['status']) ?></strong>
                            </div>
                        <?php elseif ($job['status'] !== 'open' || strtotime($job['deadline']) < time()): ?>
                            <div class="alert alert-secondary mb-0">This job posting is closed or has passed its deadline.</div>
                        <?php else: ?>
                            <a href="apply.php?job_id=<?= $job['id'] ?>" class="btn btn-success btn-lg px-5 fw-bold">Apply For This Job Now</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>