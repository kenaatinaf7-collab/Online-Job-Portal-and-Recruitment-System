<?php
// Include database configuration and helper functions
require_once 'config.php';

// Capture search and filter parameters from GET request
$keyword  = trim($_GET['keyword'] ?? '');
$location = trim($_GET['location'] ?? '');
$job_type = trim($_GET['job_type'] ?? '');

// Build dynamic SQL query for open jobs
$sql = "
    SELECT j.*, u.company_name 
    FROM jobs j 
    JOIN users u ON j.employer_id = u.id 
    WHERE j.status = 'open' AND j.deadline >= CURDATE()
";
$params = [];

if (!empty($keyword)) {
    $sql .= " AND (j.title LIKE ? OR j.category LIKE ? OR u.company_name LIKE ?)";
    $searchTerm = "%$keyword%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($location)) {
    $sql .= " AND j.location LIKE ?";
    $params[] = "%$location%";
}

if (!empty($job_type)) {
    $sql .= " AND j.job_type = ?";
    $params[] = $job_type;
}

$sql .= " ORDER BY j.created_at DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $jobs = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Portal - Find Your Dream Career</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 60px 0;
        }
        .job-card {
            border: none;
            border-radius: 10px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .job-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1) !important;
        }
    </style>
</head>
<body class="bg-light">

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary fs-4" href="index.php">JobPortal</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav align-items-center gap-2">
                <li class="nav-item">
                    <a class="nav-link active fw-semibold" href="index.php">Browse Jobs</a>
                </li>
                <?php if (isLoggedIn()): ?>
                    <?php
                        // Compatible with PHP 7.3+ using switch statement
                        $dashboardUrl = 'seeker/index.php';
                        if ($_SESSION['role'] === 'admin') {
                            $dashboardUrl = 'admin/index.php';
                        } elseif ($_SESSION['role'] === 'employer') {
                            $dashboardUrl = 'employer/index.php';
                        }
                    ?>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" href="<?= $dashboardUrl ?>">My Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-outline-danger btn-sm px-3" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-outline-primary btn-sm px-3" href="login.php">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary btn-sm px-3" href="register.php">Register</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero & Search Filter Section -->
<section class="hero-section">
    <div class="container text-center">
        <h1 class="fw-bold display-5 mb-3">Find the Right Job for Your Future</h1>
        <p class="lead mb-4 opacity-75">Browse thousands of verified job opportunities across various industries.</p>

        <!-- Search Form -->
        <form method="GET" action="index.php" class="bg-white p-3 rounded-3 shadow mx-auto" style="max-width: 900px;">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" class="form-control" name="keyword" 
                           value="<?= htmlspecialchars($keyword) ?>" placeholder="Job title, keyword, or company">
                </div>
                <div class="col-md-3">
                    <input type="text" class="form-control" name="location" 
                           value="<?= htmlspecialchars($location) ?>" placeholder="City or location">
                </div>
                <div class="col-md-3">
                    <select class="form-select" name="job_type">
                        <option value="">All Job Types</option>
                        <option value="Full-time" <?= $job_type === 'Full-time' ? 'selected' : '' ?>>Full-time</option>
                        <option value="Part-time" <?= $job_type === 'Part-time' ? 'selected' : '' ?>>Part-time</option>
                        <option value="Remote" <?= $job_type === 'Remote' ? 'selected' : '' ?>>Remote</option>
                        <option value="Contract" <?= $job_type === 'Contract' ? 'selected' : '' ?>>Contract</option>
                    </select>
                </div>
                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary fw-semibold">Search</button>
                </div>
            </div>
        </form>
    </div>
</section>

<!-- Job Listings Section -->
<section class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0">Available Job Openings (<?= count($jobs) ?>)</h4>
        <?php if (!empty($keyword) || !empty($location) || !empty($job_type)): ?>
            <a href="index.php" class="btn btn-sm btn-outline-secondary">Clear Filters</a>
        <?php endif; ?>
    </div>

    <?php if (empty($jobs)): ?>
        <div class="card border-0 shadow-sm p-5 text-center">
            <h5 class="text-muted mb-2">No active job listings found matching your criteria.</h5>
            <p class="small text-secondary mb-0">Try adjusting your search keywords or check back later for new postings.</p>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card job-card shadow-sm h-100 p-3 bg-white">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary-subtle text-primary fw-semibold">
                                    <?= htmlspecialchars($job['job_type']) ?>
                                </span>
                                <small class="text-muted">Deadline: <?= date('M d, Y', strtotime($job['deadline'])) ?></small>
                            </div>
                            
                            <h5 class="card-title fw-bold mb-1">
                                <a href="job.php?id=<?= $job['id'] ?>" class="text-dark text-decoration-none">
                                    <?= htmlspecialchars($job['title']) ?>
                                </a>
                            </h5>
                            <p class="text-primary small fw-semibold mb-2">
                                <?= htmlspecialchars($job['company_name'] ?? 'Verified Employer') ?>
                            </p>
                            
                            <p class="text-muted small mb-3">
                                <strong>Location:</strong> <?= htmlspecialchars($job['location']) ?><br>
                                <strong>Category:</strong> <?= htmlspecialchars($job['category']) ?><br>
                                <?php if (!empty($job['salary'])): ?>
                                    <strong>Salary:</strong> <?= htmlspecialchars($job['salary']) ?>
                                <?php endif; ?>
                            </p>

                            <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center">
                                <small class="text-muted">Posted <?= date('M d', strtotime($job['created_at'])) ?></small>
                                <a href="job.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-primary fw-semibold">
                                    View & Apply &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- Footer -->
<footer class="bg-white border-top py-4 mt-5">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> JobPortal PHP System. All rights reserved.
    </div>
</footer>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>