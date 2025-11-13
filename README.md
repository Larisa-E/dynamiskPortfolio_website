# Dynamisk Portfolio Website

I built this project to have a living, visual résumé that I can update without editing raw PHP every time. It lets me showcase recent work, tweak my story, and read messages in one place—everything runs on a simple PHP/MySQL stack that I can host almost anywhere.

![Public site walkthrough](public/assets/gifs/home.gif "Landing page scroll through projects")

## What I focussed on
- Crafting a dark, cinematic layout that highlights each project card and demo video.
- Building an admin dashboard so I can manage projects, bio content, and messages quickly.
- Adding support for hosted video links, responsive imagery, and modern typography.
- Keeping the stack lightweight (plain PHP, MySQL, Composer autoloading) so deployment stays easy.

## How I put it together
- **Backend:** I structured the content in MySQL (`projects`, `about`, `messages`, `admins`) and used PDO for secure database access.
- **Admin tools:** I created CRUD forms for projects and a simple editor for the About page, both protected behind session-based auth.
- **Frontend:** I refreshed the theme with layered gradients, Playfair Display headlines, and grid-based layouts that adapt from mobile to desktop.
- **Contact flow:** The public form writes into the messages table, and the admin inbox lets me archive or delete submissions right away.

![Admin login and dashboard](public/assets/gifs/admin.gif "Login and dashboard overview")

## Want to run it yourself?
Here is exactly how I set it up on a fresh machine:

1. **Clone the repository**
   ```bash
   git clone https://github.com/Larisa-E/dynamiskPortfolio_website.git
   cd dynamiskPortfolio_website
   ```
2. **Install Composer dependencies**
   ```bash
   composer install
   ```
3. **Create the configuration file**
   ```bash
   cp config/config.sample.php config/config.php
   ```
   PowerShell alternative:
   ```powershell
   copy config/config.sample.php config/config.php
   ```
   Then I update `config/config.php` with my base URL (pointing at `/public`) and database credentials.
4. **Provision the database**
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
5. **Seed an admin account**
   ```bash
   php -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"
   ```
   I copy the hash and insert it manually:
   ```sql
   USE portfolio_db;
   INSERT INTO admins (username, password_hash)
   VALUES ('admin', '$2y$...paste-your-hash-here...');
   ```
6. **Start the stack and browse**
   - Public site: `http://localhost/dynamiskPortfolio_website/public`
   - Admin login: `http://localhost/dynamiskPortfolio_website/admin/login.php`

## Inside the admin area
- **Projects:** I can add titles, descriptions, tech tags, screenshots, and demo video URLs.

  ![Editing projects and about page](public/assets/gifs/admin_edit.gif "Project CRUD and About editor demo")
- **About Page:** Updating my bio, hero copy, and profile photo instantly refreshes the public site.
- **Messages:** Every contact form submission lands in the inbox so I can read or remove it with a click.

  ![Managing contact messages](public/assets/gifs/mess.gif "Message inbox view and delete flow")

## Database snapshot
Here is a quick peek at the tables I designed.

![Database tables overview](public/assets/gifs/db.gif "projects, admins, messages tables in phpMyAdmin")

Thanks for checking out my portfolio build! If you see improvements or want to collaborate, feel free to open an issue or reach out.
