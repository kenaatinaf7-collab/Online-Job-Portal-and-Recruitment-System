<?php
// Include database configuration and helper functions
require_once 'config.php';

// Redirect if user is already logged in
if (isLoggedIn()) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$error = '';
$success = '';

// Process registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name    = trim($_POST['full_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $role         = $_POST['role'] ?? 'seeker';
    $company_name = trim($_POST['company_name'] ?? '');
    $password     = $_POST['password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';

    // Validate required fields
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_pass)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (!in_array($role, ['seeker', 'employer'])) {
        $error = "Invalid account role selected.";
    } elseif ($role === 'employer' && empty($company_name)) {
        $error = "Company name is required for Employer accounts.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        try {
            // Check if email already exists in the database
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = "An account with this email already exists.";
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $comp_val = ($role === 'employer') ? $company_name : null;

                // Insert new user record
                $insert = $pdo->prepare("
                    INSERT INTO users (full_name, email, password, role, phone, company_name) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                if ($insert->execute([$full_name, $email, $hashed_password, $role, $phone, $comp_val])) {
                    $success = "Registration successful! You can now log in to your account.";
                } else {
                    $error = "Registration failed. Please try again later.";
                }
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
    <title>Register - Job Portal</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f6f9;
        }
        .register-card {
            max-width: 520px;
            margin: 50px auto;
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card register-card p-4 p-md-5 bg-white">
        <div class="text-center mb-4">
            <h3 class="fw-bold text-primary">Create an Account</h3>
            <p class="text-muted small">Join our Job Portal as a Job Seeker or Employer</p>
        </div>

        <!-- Display Error or Success Messages -->
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success" role="alert">
                <?= htmlspecialchars($success) ?>
                <div class="mt-2">
                    <a href="login.php" class="btn btn-sm btn-success">Go to Login</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="register.php">
            <!-- Account Role Selection -->
            <div class="mb-3">
                <label for="role" class="form-label fw-semibold">I am registering as a: <span class="text-danger">*</span></label>
                <select class="form-select" id="role" name="role" onchange="toggleCompanyField()" required>
                    <option value="seeker" <?= (($_POST['role'] ?? '') === 'seeker') ? 'selected' : '' ?>>Job Seeker</option>
                    <option value="employer" <?= (($_POST['role'] ?? '') === 'employer') ? 'selected' : '' ?>>Employer / Recruiter</option>
                </select>
            </div>

            <!-- Full Name -->
            <div class="mb-3">
                <label for="full_name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="full_name" name="full_name" 
                       value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" placeholder="Enter your full name" required>
            </div>

            <!-- Company Name (Visible only for Employers) -->
            <div class="mb-3 d-none" id="company_field_group">
                <label for="company_name" class="form-label fw-semibold">Company Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="company_name" name="company_name" 
                       value="<?= htmlspecialchars($_POST['company_name'] ?? '') ?>" placeholder="Enter company or organization name">
            </div>

            <!-- Email Address -->
            <div class="mb-3">
                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="name@example.com" required>
            </div>

            <!-- Phone Number -->
            <div class="mb-3">
                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" 
                       value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" placeholder="+251 9XX XXX XXX">
            </div>

            <!-- Password -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Min 6 characters" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="confirm_password" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Repeat password" required>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid mt-3">
                <button type="submit" class="btn btn-primary btn-lg">Register</button>
            </div>
        </form>

        <div class="text-center mt-4">
            <p class="small text-muted mb-0">Already have an account? <a href="login.php" class="text-decoration-none fw-semibold">Login here</a></p>
            <p class="small mt-1"><a href="index.php" class="text-secondary text-decoration-none">&larr; Back to Home</a></p>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Show or hide the Company Name field based on selected role
    function toggleCompanyField() {
        const role = document.getElementById('role').value;
        const companyGroup = document.getElementById('company_field_group');
        const companyInput = document.getElementById('company_name');

        if (role === 'employer') {
            companyGroup.classList.remove('d-none');
            companyInput.setAttribute('required', 'required');
        } else {
            companyGroup.classList.add('d-none');
            companyInput.removeAttribute('required');
        }
    }

    // Run on page load to maintain state after form validation errors
    document.addEventListener('DOMContentLoaded', toggleCompanyField);
</script>

</body>
</html>