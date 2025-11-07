# Dynamisk Portfolio Website# Dynamisk Portfolio Website



## Description## Description

This project is a dynamic portfolio website built with PHP to showcase projects and skills as an interactive online resume.This project is a dynamic portfolio website built using PHP. It is designed to showcase various projects and skills in an interactive manner, serving as a comprehensive online resume.



## Technology Stack## Technology Stack

- **PHP**- **PHP:** 67.0%

- **CSS**- **CSS:** 29.4%

- **JavaScript**- **JavaScript:** 3.6%

- **AOS** (Animate On Scroll)- **AOS** (Animate On Scroll)

- **Bootstrap**- **Bootstrap**



## Features## Features

- **Dynamic Content Management:** Projects and copy are stored in MySQL and rendered server-side.- **Dynamic Content Management:** Utilizes PHP to dynamically manage and display content.

- **Interactive Project Displays:** Highlight work with imagery, tech tags, and rich descriptions.- **Interactive Project Displays:** Engaging project presentations.

- **Responsive Design:** Optimised layouts across desktop, tablet, and mobile.- **Responsive Design:** Optimized for multiple devices.



## Installation



### Requirements## Installation

- PHP 8.1+To set up the project locally, follow these steps:

- MySQL or MariaDB

- Composer1. **Clone the Repository:**

- Apache/Nginx (XAMPP, WAMP, MAMP, etc.)   ```bash

   git clone https://github.com/Larisa-E/dynamiskPortfolio_website.git

### Quick Start
1. **Clone the repository**
   ```bash
   git clone https://github.com/Larisa-E/dynamiskPortfolio_website.git
   cd dynamiskPortfolio_website
   ```
2. **Install PHP dependencies**
   ```bash
   composer install
   ```
3. **Create the config file**
   ```bash
   cp config/config.sample.php config/config.php
   ```
   Update `config/config.php` with your database credentials and set `base_url` to the URL that serves `public/` (for example `http://localhost/dynamiskPortfolio_website/public`).
4. **Provision the database**
   ```bash
   mysql -u root -p < sql/schema.sql
   ```
   Generate an admin password hash and insert it into the `admins` table:
   ```bash
   php -r "echo password_hash('admin123', PASSWORD_DEFAULT), PHP_EOL;"
   ```
   ```sql
   USE portfolio_db;
   INSERT INTO admins (username, password_hash)
   VALUES ('admin', '$2y$...your hash...');
   ```
5. **Serve the application**
   Start Apache/MySQL (e.g. XAMPP) and open `http://localhost/dynamiskPortfolio_website/public`.
   The admin dashboard is available at `http://localhost/dynamiskPortfolio_website/admin/login.php`.

### Optional: Custom Hostname
Add `127.0.0.1 portfolio.local` to your hosts file and update `base_url` to `http://portfolio.local/public` if you prefer a shorter URL.

### Running Tests
(Add instructions here when automated tests are introduced.)
