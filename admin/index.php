<?php
// Include database configuration and verify admin role
require_once '../config.php';
checkRole('admin');

// Fetch summary statistics for the dashboard
try {
    $total_users = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $total_jobs  = $pdo->query("SELECT COUNT(*) FROM jobs")->fetchColumn();
    $total_apps  = $pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn();

    // Fetch recent users
    $recent_users = $pdo->query("SELECT id, full_name, email, role, status, created_at FROM users ORDER BY created_at DESC LIMIT 8")->fetchAll();
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Job Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Top Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">JobPortal Admin</a>
        <div class="d-flex align-items-center">
            <span class="text-white me-3 small">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="../index.php" class="btn btn-outline-light btn-sm me-2">View Site</a>
            <a href="../logout.php" class="btn btn-danger btn-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container my-5">
    <h3 class="fw-bold mb-4">System Overview</h3>

    <!-- Summary Metric Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-primary text-white p-3">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Registered Users</h6>
                    <h2 class="fw-bold mb-0"><?= $total_users ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-success text-white p-3">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Posted Jobs</h6>
                    <h2 class="fw-bold mb-0"><?= $total_jobs ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm bg-info text-white p-3">
                <div class="card-body">
                    <h6 class="text-uppercase small">Total Job Applications</h6>
                    <h2 class="fw-bold mb-0"><?= $total_apps ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Recently Registered Users</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_users as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($u['full_name']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td><span class="badge bg-secondary text-uppercase"><?= htmlspecialchars($u['role']) ?></span></td>
                            <td>
                                <span class="badge <?= $u['status'] === 'active' ? 'bg-success' : 'bg-danger' ?>">
                                    <?= htmlspecialchars($u['status']) ?>
                                </span>
                            </td>
                            <td class="small text-muted"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>