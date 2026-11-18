<?php
// Include database configuration and verify seeker role
require_once 'config.php';
checkRole('seeker');

$job_id = filter_var($_GET['job_id'] ?? $_POST['job_id'] ?? null, FILTER_VALIDATE_INT);

if (!$job_id) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

try {
    // Verify job exists and is open
    $stmt = $pdo->prepare("SELECT j.*, u.company_name FROM jobs j JOIN users u ON j.employer_id = u.id WHERE j.id = ? AND j.status = 'open'");
    $stmt->execute([$job_id]);
    $job = $stmt->fetch();

    if (!$job) {
        die("Invalid or closed job posting.");
    }

    // Check if already applied
    $check = $pdo->prepare("SELECT id FROM applications WHERE job_id = ? AND seeker_id = ?");
    $check->execute([$job_id, $_SESSION['user_id']]);
    if ($check->fetch()) {
        header("Location: seeker/index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Process application submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cover_letter = trim($_POST['cover_letter'] ?? '');

    // Handle CV file upload
    if (!isset($_FILES['cv_file']) || $_FILES['cv_file']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = "Please upload your CV/Resume file.";
    } else {
        $file = $_FILES['cv_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed_exts)) {
            $error = "Invalid file format. Only PDF, DOC, and DOCX files are allowed.";
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB limit
            $error = "File size exceeds the maximum limit of 5MB.";
        } else {
            // Create uploads directory if it doesn't exist
            $upload_dir = 'assets/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = 'cv_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
            $destination = $upload_dir . $filename;

            if (move_uploaded_file($file['tmp_name'], $destination)) {
                try {
                    $insert = $pdo->prepare("
                        INSERT INTO applications (job_id, seeker_id, cover_letter, cv_file, status) 
                        VALUES (?, ?, ?, ?, 'pending')
                    ");
                    if ($insert->execute([$job_id, $_SESSION['user_id'], $cover_letter, $filename])) {
                        header("Location: seeker/index.php?applied=success");
                        exit();
                    } else {
                        $error = "Failed to submit application. Please try again.";
                    }
                } catch (PDOException $e) {
                    $error = "Database error: " . $e->getMessage();
                }
            } else {
                $error = "Failed to upload file. Please check folder permissions.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?= htmlspecialchars($job['title']) ?> - Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold text-primary" href="index.php">JobPortal</a>
        <a href="job.php?id=<?= $job_id ?>" class="btn btn-outline-secondary btn-sm">&larr; Cancel</a>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4 p-md-5 bg-white">
                <h3 class="fw-bold mb-1">Apply for Position</h3>
                <p class="text-primary fw-semibold mb-4"><?= htmlspecialchars($job['title']) ?> <span class="text-muted fw-normal">at <?= htmlspecialchars($job['company_name']) ?></span></p>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form method="POST" action="apply.php?job_id=<?= $job_id ?>" enctype="multipart/form-data">
                    <input type="hidden" name="job_id" value="<?= $job_id ?>">

                    <!-- Cover Letter -->
                    <div class="mb-3">
                        <label for="cover_letter" class="form-label fw-semibold">Cover Letter / Message to Employer</label>
                        <textarea class="form-control" id="cover_letter" name="cover_letter" rows="5" 
                                  placeholder="Briefly explain why you are a great fit for this role..."><?= htmlspecialchars($_POST['cover_letter'] ?? '') ?></textarea>
                    </div>

                    <!-- CV / Resume File Upload -->
                    <div class="mb-4">
                        <label for="cv_file" class="form-label fw-semibold">Upload CV / Resume (PDF, DOC, DOCX - Max 5MB) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="cv_file" name="cv_file" accept=".pdf,.doc,.docx" required>
                        <div class="form-text text-muted">Make sure your contact information is up to date in your resume.</div>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">Submit Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>