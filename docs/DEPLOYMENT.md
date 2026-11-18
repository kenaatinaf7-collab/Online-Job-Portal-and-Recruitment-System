# Step 6: Deployment

## 6.1 Local deployment on WAMP
See the 5 steps in `README.md`: copy the folder to `C:\wamp64\www\jobportal`, open `install.php`, create the admin, delete `install.php`.

## 6.2 Push to GitHub
Create an empty repository `jobportal` on github.com, then in the VS Code terminal:

```bash
cd jobportal
git init
git add .
git commit -m "Online Job Portal (PHP + MySQL)"
git branch -M main
git remote add origin https://github.com/<your-username>/jobportal.git
git push -u origin main
```
Never commit real passwords: if you change `DB_PASS` in `config.php` for a real server, keep that version out of GitHub.

## 6.3 Put it on the internet (shared hosting)
Any PHP + MySQL hosting works (cPanel hosts, InfinityFree, etc.):
1. In the hosting panel create a **MySQL database**, a database **user** and **password**.
2. Upload all project files into `public_html` (or a sub-folder) with File Manager or FTP.
3. Edit `config.php` on the server: `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, and `BASE_URL` (`''` if the site is in the root, or `'/folder'`). Set `COOKIE_SECURE` to `true` when the site uses HTTPS.
4. Hosting panels usually do not allow `CREATE DATABASE` from PHP. In that case import `database.sql` with phpMyAdmin first, then open `install.php` to create the admin.
5. **Delete `install.php`** after installation.
6. Enable HTTPS (free certificate in most panels).

## 6.4 After deployment
- Log in as admin and run the manual checklist in `TESTING.md` on the live URL.
- Back up the database regularly (phpMyAdmin > Export).
- Keep PHP updated.

## 6.5 Future improvements
- CV file upload, email notifications, password reset
- Pagination, company profiles, saved jobs
