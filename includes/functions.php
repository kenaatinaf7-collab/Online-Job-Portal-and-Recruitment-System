<?php
require_once __DIR__ . '/../config.php';

const CATEGORIES = ['IT & Software', 'Education', 'Health', 'Finance & Accounting',
                    'Engineering', 'Agriculture', 'Sales & Marketing', 'Other'];
const JOB_TYPES  = ['Full-time', 'Part-time', 'Contract', 'Internship'];
const STATUSES   = ['pending', 'shortlisted', 'rejected', 'hired'];

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax', 'secure' => COOKIE_SECURE]);
    session_start();
}

/* ---------------------------------------------------------------- helpers */
function e($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path)
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

function pull_flashes(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $items;
}

function abort(int $code, string $message = '')
{
    http_response_code($code);
    $names = [400 => 'Bad Request', 403 => 'Forbidden', 404 => 'Not Found'];
    $name = $names[$code] ?? 'Error';
    $pageTitle = "$code $name";
    include __DIR__ . '/header.php';
    echo '<div class="card narrow"><h1>' . e("$code · $name") . '</h1><p>' . e($message) .
         '</p><a class="btn" href="' . e(url('index.php')) . '">Back to home</a></div>';
    include __DIR__ . '/footer.php';
    exit;
}

/* --------------------------------------------------------------- database */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $sqlite = getenv('JP_SQLITE');          // used only by automated tests
        if ($sqlite) {
            $pdo = new PDO('sqlite:' . $sqlite, null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');
        } else {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            try {
                $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $ex) {
                http_response_code(500);
                exit('<h1>Database connection failed</h1><p>Start WAMP (green icon), then open '
                     . '<a href="' . e(url('install.php')) . '">install.php</a> once to create the database.</p>');
            }
        }
    }
    return $pdo;
}

/* ------------------------------------------------------------------- auth */
function current_user(): ?array
{
    static $user = false;
    if ($user === false) {
        $user = null;
        if (!empty($_SESSION['user_id'])) {
            $st = db()->prepare('SELECT * FROM users WHERE id = ? AND is_active = 1');
            $st->execute([$_SESSION['user_id']]);
            $row = $st->fetch();
            if ($row) {
                $user = $row;
            } else {
                unset($_SESSION['user_id']);
            }
        }
    }
    return $user;
}

function require_role(string ...$roles): array
{
    $user = current_user();
    if (!$user) {
        flash('error', 'Please log in first.');
        redirect('login.php');
    }
    if ($roles && !in_array($user['role'], $roles, true)) {
        abort(403, 'You do not have permission to open this page.');
    }
    return $user;
}

function home_for(string $role): string
{
    return ['seeker' => 'seeker/dashboard.php',
            'employer' => 'employer/dashboard.php',
            'admin' => 'admin/index.php'][$role];
}

/* ------------------------------------------------------------------- CSRF */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/* ------------------------------------------------------------ job helpers */
function own_job_or_abort(int $jobId, array $user): array
{
    $st = db()->prepare('SELECT * FROM jobs WHERE id = ?');
    $st->execute([$jobId]);
    $job = $st->fetch();
    if (!$job) {
        abort(404, 'Job not found.');
    }
    if ((int)$job['employer_id'] !== (int)$user['id']) {
        abort(403, 'You can only manage your own jobs.');
    }
    return $job;
}

/* Every POST request must carry a valid CSRF token. */
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $sent = (string)($_POST['csrf_token'] ?? '');
    $expected = (string)($_SESSION['csrf'] ?? '');
    if ($expected === '' || !hash_equals($expected, $sent)) {
        abort(400, 'Invalid or missing CSRF token. Go back, reload the page and try again.');
    }
}
