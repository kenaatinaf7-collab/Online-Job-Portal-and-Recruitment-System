# Step 5: Testing

## 5.1 Strategy
| Type | What it checks |
|---|---|
| Functional (black-box) | Inputs and outputs of each feature, with valid and invalid data |
| Security | CSRF, role access, SQL/HTML injection, CV link validation |
| Access control | Each role can open only its own pages |
| User acceptance | Manual walkthrough of every role on WAMP (see 5.4) |

## 5.2 Test cases
| ID | Test | Expected result |
|---|---|---|
| T01 | Open home page | 200 OK |
| T02 | Register + login with valid data | Account created, welcome message |
| T03 | Register with an existing email | "already registered" |
| T04 | Register with 3-character password | "at least 6 characters" |
| T05 | Register with role = admin | Rejected |
| T06 | Login with wrong password | "Incorrect email or password" |
| T07 | POST without CSRF token | 400 |
| T08 | Logout then open dashboard | Redirected to login |
| T09 | Employer posts a job | Job shown on home page with company |
| T10 | Search by keyword and by category | Only matching jobs shown |
| T11 | Seeker opens post-job page | 403 Forbidden |
| T12 | Anonymous opens post-job page | Redirect to login |
| T13 | Post job with past deadline | "Deadline cannot be in the past" |
| T14 | Job title containing `<script>` | Escaped, not executed |
| T15 | Employer closes a job | Job disappears from public list |
| T16 | Seeker applies | "Application submitted"; shown in dashboard |
| T17 | Seeker applies twice | "already applied" |
| T18 | CV link `javascript:alert(1)` | Rejected |
| T19 | Employer views applicants and sets status | Status updated, visible to seeker |
| T20 | Another employer opens applicants / changes status | 403 Forbidden |
| T21 | Invalid status value | 400 |
| T22 | Employer tries to apply | 403 Forbidden |
| T23 | Non-admin opens admin panel | 403 Forbidden |
| T24 | Admin hides a job | Job disappears from public list |
| T25 | Admin disables a user | User cannot log in; admin account cannot be disabled |

## 5.3 Results
All 25 test cases (32 checks) were run automatically against the PHP code with PHP's built-in web server and a **SQLite** test database (same tables, same queries): **32/32 passed, no PHP warnings or notices.**

**Not yet verified on a real WAMP/MySQL installation:** `install.php` (it creates the MySQL database) and the MySQL-specific parts of `database.sql`. Run the manual checklist below on WAMP to confirm them.

## 5.4 Manual acceptance checklist (on WAMP)
1. Run `install.php`, create the admin, tick *Add demo data*, then delete `install.php`.
2. Open phpMyAdmin and confirm the database `jobportal` has the tables `users`, `jobs`, `applications`.
3. Sign up as employer, post two jobs.
4. Sign up as seeker, search, apply to one job.
5. Employer opens applicants, sets "shortlisted".
6. Seeker sees the new status.
7. Admin logs in, disables a user, hides a job.
8. Open the site in a phone-size browser window and confirm the layout fits.
