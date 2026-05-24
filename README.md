# Tripistry

A travel package platform built for COS 221 Practical Assignment 5 at the University of Pretoria.

Travellers browse and book packages; travel agencies create and manage them.

---

## Team Members

| Name | Student Number | GitHub |
|------|---------------|--------|
| Member 1 | u05174776 | @Tshedi-Sefula |
| Member 2 | u25588304 | @mohammedlutch74 |
| Member 3 | u24596397 | @yasar-rahman |
| Member 4 | u24983439 | @mueezgani |
| Member 5 | uXXXXXXXX | @github |

---

## Tech Stack

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP 8+
- **Database:** MariaDB
- **Version Control:** Git / GitHub

---

## Getting Started

### Prerequisites

- PHP 8.0 or higher
- MariaDB 10.5 or higher
- A local server (XAMPP / MAMP / Laravel Herd / plain Apache)
- Composer (for PHP dependencies)

### 1. Clone the repository

```bash
git clone https://github.com/YOUR_USERNAME/tripistry.git
cd tripistry
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Set up the database

Create the database and import the schema + data:

```bash
mariadb -u root -p -e "CREATE DATABASE tripistry CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mariadb -u root -p tripistry < database/tripistry_data.sql
mariadb -u root -p tripistry < database/role_patch.sql
```

### 4. Configure environment

Copy the example config and fill in your credentials:

```bash
cp config.example.php config.php
```

Edit `config.php`:

```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tripistry');
define('DB_USER', 'root');
define('DB_PASS', 'your_password_here');
```

### 5. Start the server

If using PHP's built-in server:

```bash
php -S localhost:8000
```

Then open [http://localhost:8000](http://localhost:8000) in your browser.

---

## Project Structure

```
tripistry/
├── database/
│   ├── tripistry_data.sql   # Full schema + data
│   └── role_patch.sql       # Role-based user generalisation
├── public/
│   ├── index.php            # Landing page
│   ├── login.php
│   ├── register.php
│   ├── traveller/           # Traveller-only pages
│   └── agency/              # Agency-only pages
├── includes/
│   ├── db.php               # PDO connection
│   ├── auth.php             # Session & role helpers
│   └── functions.php        # Shared utilities
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config.example.php       # Template config (commit this, not config.php)
├── composer.json
├── .gitignore
└── README.md
```

---

## Branching Strategy

We follow a feature-branch workflow:

| Branch | Purpose |
|--------|---------|
| `main` | Stable, demo-ready code only |
| `dev`  | Integration branch — merge features here first |
| `feature/xxx` | Individual features (e.g. `feature/traveller-booking`) |
| `fix/xxx` | Bug fixes (e.g. `fix/login-redirect`) |

**Workflow:**
1. Branch off `dev` → `feature/your-feature`
2. Commit with meaningful messages (see below)
3. Open a Pull Request into `dev`
4. One teammate reviews and merges
5. `dev` is merged into `main` before demos

### Commit Message Format

```
type: short description (max 50 chars)

Optional longer explanation if needed.
```

Types: `feat`, `fix`, `docs`, `style`, `refactor`, `test`, `chore`

Examples:
```
feat: add traveller booking form with payment status
fix: prevent agency accessing traveller dashboard
docs: update README with setup instructions
chore: add .gitignore for PHP vendor folder
```

---

## Features

### Traveller
- Browse destinations, flights, accommodation, attractions and restaurants
- Compare travel packages across agencies
- Book a package
- Leave reviews and ratings for agencies and packages

### Travel Agency
- Create, edit and delete travel packages
- Manage group trips
- Manage destinations, flights, accommodation and attractions per package

---

## Security

- Passwords hashed with PHP `password_hash()` (bcrypt)
- All database queries use PDO prepared statements (SQL injection prevention)
- Role-based access control enforced via session and database triggers
- CSRF protection on all forms
