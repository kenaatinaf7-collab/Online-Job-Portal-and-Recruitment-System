# Step 4: Implementation

## 4.1 Technology
| Layer | Tool |
|---|---|
| Local server | WAMP (Apache + PHP 8.1+ + MySQL) |
| Language | PHP |
| Database | MySQL, accessed with PDO |
| Front end | HTML + plain CSS (`assets/style.css`) |
| Database tool | phpMyAdmin (comes with WAMP) |

## 4.2 Build order used
1. **Database:** `database.sql` (users, jobs, applications) and `install.php` to create everything automatically.
2. **Core:** `includes/functions.php` with `db()` (PDO), `e()` (escape), `flash()`, `csrf_field()`, `current_user()`, `require_role()`.
3. **Layout:** `includes/header.php` and `footer.php` shared by every page.
4. **Authentication:** `register.php`, `login.php`, `logout.php`.
5. **Jobs:** `index.php` (search + category filter), `job.php`, `employer/post_job.php`.
6. **Applications:** `apply.php` (duplicate and deadline checks), `seeker/dashboard.php`.
7. **Employer tools:** dashboard, applicants, status update, close/reopen job.
8. **Admin:** statistics, enable/disable users, hide/show jobs.
9. **Hardening:** CSRF, validation, ownership checks, error pages.

## 4.3 Key code ideas
- `require_role('employer')` at the top of a page sends anonymous visitors to the login page and shows 403 to users with the wrong role.
- `own_job_or_abort()` makes sure an employer touches only their own jobs.
- `includes/functions.php` checks the CSRF token for every POST request, so no form can forget it.
- All pages use the same pattern: include `functions.php`, handle the form, query with prepared statements, then include `header.php`, print HTML with `e()`, include `footer.php`.
- Database settings live only in `config.php`.
