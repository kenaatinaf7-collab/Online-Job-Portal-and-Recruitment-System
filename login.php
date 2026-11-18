<?php
// Include database configuration and helper functions
require_once 'config.php';

// Redirect if user is already logged in based on their role
if (isLoggedIn()) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: " . BASE_URL . "admin/index.php");
    } elseif ($_SESSION['role'] === 'employer') {
        header("Location: " . BASE_URL . "employer/index.php");
    } else {
        header("Location: " . BASE_URL . "index.php");
    }
    exit();
}

$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        try {
            // Fetch user record by email
            $stmt = $pdo->prepare("SELECT id, full_name, email, password, role, status FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            // Verify user existence and password hash
            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    $error = "Your account is currently inactive. Please contact the administrator.";
                } else {
                    // Regenerate session ID to prevent session fixation attacks
                    session_regenerate_id(true);

                    // Store user details in session variables
                    $_SESSION['user_id']   = $user['id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['email']     = $user['email'];
                    $_SESSION['role']      = $user['role'];

                    // Redirect user based on role
                    if ($user['role'] === 'admin') {
                        header("Location: " . BASE_URL . "admin/index.php");
                    } elseif ($user['role'] === 'employer') {
                        header("Location: " . BASE_URL . "employer/index.php");
                    } else {
                        header("Location: " . BASE_URL . "seeker/index.php");
                    }
                    exit();
                }
            } else {
                $error = "Invalid email address or password.";
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
    <title>Login - Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .login-card {
            max-width: 440px;
            margin: 80px auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card login-card p-4 p-md-5 bg-white">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Welcome Back</h3>
            <p class="text-muted small">Log in to access your Job Portal dashboard</p>
        </div>

        <!-- Display Error Message -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="name@example.com" required>
            </div>

            <!-- Password -->
            <div class="mb-3">
                <label for="password" class="form-label fw-semibold">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="small text-muted mb-0">Don't have an account? <a href="register.php" class="text-decoration-none fw-semibold">Register here</a></p>
            <p class="small mt-1"><a href="index.php" class="text-secondary text-decoration-none">&larr; Back to Home</a></p>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>