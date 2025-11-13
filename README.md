# Dynamisk Portfolio Website

This project is a friendly PHP site that lets you show your work, describe who you are, and keep track of contact messages in one place. The goal is to feel like a polished online resume that you can update without touching code every time.

## Why this project helps
- Keeps your projects, about story, and contact inbox together.
- Lets you add images and demo videos so visitors see your work fast.
- Uses a simple admin dashboard so you can edit content safely.
- Runs on common tools (PHP, MySQL) so you can host it almost anywhere.

## What you need
- PHP 8.1 or newer with Composer.
- MySQL or MariaDB (XAMPP, WAMP, MAMP, Docker, etc. all work).
- A web server that points to the `public/` folder.

## Fast setup guide
1. **Download the code.**
	```bash
	git clone https://github.com/Larisa-E/dynamiskPortfolio_website.git
	cd dynamiskPortfolio_website
	```
2. **Install PHP dependencies.**
	```bash
	composer install
	```
3. **Create your config file.**
	```bash
	cp config/config.sample.php config/config.php
	```
	On Windows PowerShell you can run:
	```powershell
	copy config/config.sample.php config/config.php
	```
	Open `config/config.php` and set:
	- `base_url` to the URL that serves the `public/` folder.
	- Database host, name, username, and password.
4. **Load the database tables.**
	```bash
	mysql -u root -p < sql/schema.sql
	```
5. **Make an admin login.**
	```bash
	php -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"
	```
	Copy the hash that prints out, then insert it:
	```sql
	USE portfolio_db;
	INSERT INTO admins (username, password_hash)
	VALUES ('admin', '$2y$...paste-your-hash-here...');
	```
6. **Run it.** Start Apache/MySQL (or your preferred stack) and open:
	- Public site: `http://localhost/dynamiskPortfolio_website/public`
	- Admin login: `http://localhost/dynamiskPortfolio_website/admin/login.php`

## Admin tour
- **Projects:** Add work with title, description, tools, screenshot, and optional demo video link (YouTube, Vimeo, or mp4).
- **About Page:** Update your bio, hero text, and profile photo so the public page always feels current.
- **Messages:** Read, archive, or delete contact form submissions without digging in the database.

## Media and uploads
- Uploads live in `public/uploads/`. That folder is git-ignored so it will not appear on GitHub. Copy it manually when you deploy.
- Large videos are best hosted on YouTube/Vimeo, then link them in the demo video field.
- Keep a local `assets` folder with screenshots or GIFs you want to show in the README or portfolio.

## Customization ideas
- Swap colors, fonts, or spacing in `public/assets/css/style.css` (check the CSS variables at the top).
- Add new fields to the database if you want categories, testimonials, or a blog.
- Plug in an email service (SMTP, SendGrid, Mailgun) to forward contact messages straight to your inbox.
- Add automated tests later (PHPUnit, Pest, Cypress) if you want to guard against regressions.

## Troubleshooting
- **Blank page?** Check `config/config.php` for the correct `base_url` and database info.
- **Login fails?** Generate a fresh password hash and be sure it matches the username in `admins`.
- **Images missing?** Confirm your local server can read files inside `public/uploads/` and that the folder exists.

## Next steps
1. Record a short demo video and add the link to a featured project.
2. Add screenshots or GIFs to `public/assets/images/` for extra polish.
3. Document your deployment process (shared hosting, VPS, etc.) once you push the site live.

Thanks for exploring the project! Feel free to open an issue or share ideas for improvements.
