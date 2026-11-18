<?php
// Include database configuration and verify employer role
require_once '../config.php';
checkRole('employer');

$error = '';
$success = '';

// Process job posting form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $category     = trim($_POST['category'] ?? '');
    $job_type     = trim($_POST['job_type'] ?? '');
    $location     = trim($_POST['location'] ?? '');
    $salary       = trim($_POST['salary'] ?? '');
    $description  = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $deadline     = trim($_POST['deadline'] ?? '');

    // Validate fields
    if (empty($title) || empty($category) || empty($job_type) || empty($location) || empty($description) || empty($requirements) || empty($deadline)) {
        $error = "Please fill in all required fields.";
    } elseif (strtotime($deadline) < strtotime(date('Y-m-d'))) {
        $error = "The application deadline cannot be in the past.";
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO jobs (employer_id, title, category, job_type, location, salary, description, requirements, deadline, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')
            ");
            
            if ($stmt->execute([$_SESSION['user_id'], $title, $category, $job_type, $location, $salary, $description, $requirements, $deadline])) {
                header("Location: index.php?posted=success");
                exit();
            } else {
                $error = "Failed to post job. Please try again.";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a New Job - Employer Panel</title>
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
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
                <h3 class="fw-bold mb-4">Post a New Job Vacancy</h3>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="post-job.php">
                    <div class="mb-3">
                        <label for="title" class="form-label fw-semibold">Job Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($_POST['title'] ?? '') ?>" placeholder="e.g. Senior Software Engineer" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label fw-semibold">Category / Industry <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="category" name="category" 
                                   value="<?= htmlspecialchars($_POST['category'] ?? '') ?>" placeholder="e.g. Information Technology" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="job_type" class="form-label fw-semibold">Job Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="job_type" name="job_type" required>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Remote">Remote</option>
                                <option value="Contract">Contract</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="location" class="form-label fw-semibold">Location <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="location" name="location" 
                                   value="<?= htmlspecialchars($_POST['location'] ?? '') ?>" placeholder="e.g. Addis Ababa / Remote" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="salary" class="form-label fw-semibold">Salary Range</label>
                            <input type="text" class="form-control" id="salary" name="salary" 
                                   value="<?= htmlspecialchars($_POST['salary'] ?? '') ?>" placeholder="e.g. $1000 - $1500 / Negotiable">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label fw-semibold">Job Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="5" 
                                  placeholder="Provide a detailed description of the role..." required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="requirements" class="form-label fw-semibold">Requirements & Qualifications <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="requirements" name="requirements" rows="4" 
                                  placeholder="List skills, experience, or education required..." required><?= htmlspecialchars($_POST['requirements'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="deadline" class="form-label fw-semibold">Application Deadline <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="deadline" name="deadline" 
                               value="<?= htmlspecialchars($_POST['deadline'] ?? '') ?>" required>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">Publish Job Opening</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>