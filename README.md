# Online Job Portal and Recruitment System (PHP + MySQL, WAMP)

A web application where **job seekers** find and apply for jobs, **employers** post jobs and manage applicants, and an **admin** moderates the platform.

Built with **PHP 8 + MySQL (PDO) + HTML/CSS**, runs on **WAMP**.

## Project documentation (step by step)

| Step | Document |
|---|---|
| 1. Title | This file |
| 2. Analysis | [docs/ANALYSIS.md](docs/ANALYSIS.md) |
| 3. Design | [docs/DESIGN.md](docs/DESIGN.md) |
| 4. Implementation | [docs/IMPLEMENTATION.md](docs/IMPLEMENTATION.md) |
| 5. Testing | [docs/TESTING.md](docs/TESTING.md) |
| 6. Deployment | [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) |

## Run it on WAMP (5 steps)

1. **Start WAMP.** Wait until the WAMP icon in the taskbar is **green**.
2. **Copy the folder.** Put the whole `jobportal-php` folder into `C:\wamp64\www\` and rename it to **`jobportal`**
   (so the path is `C:\wamp64\www\jobportal\index.php`).
3. **Check `config.php`.** Defaults fit a fresh WAMP (user `root`, empty password, folder `/jobportal`). Change them only if yours differ.
4. **Install.** Open **http://localhost/jobportal/install.php**, type the admin email and password, tick *Add demo data* if you want sample jobs, click **Install**.
   This creates the database `jobportal` and all tables automatically.
5. **Delete `install.php`**, then open **http://localhost/jobportal/** and log in.

Demo accounts (only if you ticked *Add demo data*): `employer@demo.com` / `demo123`, `seeker@demo.com` / `demo123`.

Prefer phpMyAdmin? Create a database named `jobportal`, select it, and import `database.sql`. Then run `install.php` to create the admin account.

## Features

- Registration and login with roles (seeker, employer, admin), hashed passwords (`password_hash`)
- Job search by keyword (title, company, location) and category
- Employers: post jobs, close/reopen jobs, see applicants, set status (pending, shortlisted, rejected, hired)
- Seekers: apply with CV link and cover letter, track application status
- Admin: statistics, enable/disable users, hide/show jobs
- Security: CSRF protection, prepared statements (PDO), HTML escaping, role-based access control, session ID regeneration on login

## Folder structure

```
jobportal/
  index.php login.php register.php logout.php job.php apply.php install.php
  config.php          database settings
  database.sql        MySQL tables
  includes/           functions.php (DB, auth, CSRF), header.php, footer.php
  seeker/dashboard.php
  employer/           dashboard.php post_job.php applicants.php set_status.php toggle_job.php
  admin/              index.php toggle_user.php toggle_job.php
  assets/style.css
  docs/               analysis, design, implementation, testing, deployment
```
