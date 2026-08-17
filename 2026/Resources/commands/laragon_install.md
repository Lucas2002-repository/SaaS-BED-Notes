# Laravel Installation Guide (Windows + Git Bash + Laragon)

This guide walks through creating a fresh Laravel project on Windows using **Git Bash** and **Laragon**.

## Before You Start

Install or confirm:

- Laragon with PHP, MySQL, and Composer
- Git for Windows
- Node.js LTS
- Visual Studio Code (recommended)

Check that the core tools are available:

```bash
php -v
composer -V
node -v
npm -v
git --version
```

## Quick Setup Flow

1. Start Laragon and make sure web and database services are running.
2. Open Git Bash in Laragon's `www` folder.
3. Create the Laravel project with Composer.
4. Install Node dependencies and build assets.
5. Create the database and update `.env`.
6. Run migrations.
7. Open the app using either Laragon's `.test` host or `php artisan serve`.

## 1. Start Laragon

Open **Laragon** and click:

```text
Start All
```

Confirm these services are running:

- Apache or Nginx
- MySQL

## 2. Open Git Bash in the Web Root

Move to Laragon's `www` directory.

Common locations:

- TAFE/lab setup:

```bash
cd /c/ProgramData/Laragon/www
```

- Default home install:

```bash
cd /c/laragon/www
```

## 3. Create a New Laravel Project

Replace `my-project` with your own project name.

```bash
composer create-project laravel/laravel my-project
```

Example:

```bash
composer create-project laravel/laravel student-management
```

## 4. Enter the Project Folder

```bash
cd my-project
```

Example:

```bash
cd student-management
```

## 5. Install Frontend Dependencies

```bash
npm install
```

## 6. Build Vite Assets

Use one of the following:

- For active development:

```bash
npm run dev
```

Leave that terminal running while you work.

- For a one-off production build:

```bash
npm run build
```

## 7. Create the Environment File If Needed

Laravel usually creates `.env` automatically. If it does not, run:

```bash
cp .env.example .env
```

## 8. Generate the Application Key

```bash
php artisan key:generate
```

## 9. Create the Database

In Laragon, open:

```text
Menu
→ MySQL
→ HeidiSQL
```

Create a new database.

Example:

```text
student_management
```

## 10. Configure the Database Connection

Open `.env` and update the database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_management
DB_USERNAME=root
DB_PASSWORD=
```

Laragon's default MySQL password is blank.

## 11. Run Database Migrations

```bash
php artisan migrate
```

Expected result:

```text
Migrating...

DONE
```

## 12. Install Authentication (Optional)

If you want Laravel authentication scaffolding, one common option is Breeze.

Install the package:

```bash
composer require laravel/breeze --dev
```

Run the installer:

```bash
php artisan breeze:install
```

Rebuild assets:

```bash
npm install
npm run dev
```

Run migrations again:

```bash
php artisan migrate
```

## 13. Open the Application

### Option A: Use Laragon Virtual Hosts

If the project sits inside Laragon's `www` folder, Laragon usually creates a local virtual host automatically.

Example project path:

```text
C:\ProgramData\Laragon\www\student-management
```

Open:

```text
http://student-management.test
```

If this works, you do not need to run `php artisan serve`.

### Option B: Use Laravel's Built-In Server

If you are not using the Laragon virtual host, start Laravel manually:

```bash
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

## Practical Checks

Use these checks if something does not work as expected:

- `php artisan about` to confirm Laravel is installed correctly
- `php artisan migrate:status` to confirm the database connection works
- `npm run dev` to confirm Vite is building assets
- Check that the project folder is inside Laragon's `www` directory if the `.test` URL does not resolve

---

## Useful Artisan Commands

Clear cache:

```bash
php artisan optimize:clear
```

Run migrations:

```bash
php artisan migrate
```

Rollback last migration:

```bash
php artisan migrate:rollback
```

Fresh database:

```bash
php artisan migrate:fresh
```

Fresh database with seeders:

```bash
php artisan migrate:fresh --seed
```

List all routes:

```bash
php artisan route:list
```

List Artisan commands:

```bash
php artisan list
```

## Useful NPM Commands

Development server:

```bash
npm run dev
```

Production build:

```bash
npm run build
```

## Typical Git Bash Workflow

Open project:

```bash
cd /c/ProgramData/Laragon/www/student-management
```

If you use the default home install instead of the lab setup, adjust the path accordingly.

Start Vite:

```bash
npm run dev
```

(Optional) Start Laravel server:

```bash
php artisan serve
```

If using Laragon virtual hosts, only `npm run dev` is typically required.

## Dependency Maintenance

Composer dependencies:

```bash
composer update
```

Install dependencies from an existing project:

```bash
composer install
```

---

NPM dependencies:

Install packages:

```bash
npm install
```

Update packages:

```bash
npm update
```

## Common Troubleshooting

## Missing Application Key

```bash
php artisan key:generate
```

---

## Database Connection Error

Check:

- MySQL is running.
- Database exists.
- `.env` settings are correct.

---

## Clear All Laravel Caches

```bash
php artisan optimize:clear
```

---

## Vite Manifest Not Found

Run:

```bash
npm install
npm run dev
```

or

```bash
npm run build
```

---

## Permission Issues

Reinstall dependencies:

```bash
composer install
npm install
```

# Recommended Folder Structure

```
C:\ProgramData\Laragon\www\
│
├── student-management
├── inventory-system
├── blog
└── api-demo
```

Git Bash equivalent:

```bash
cd /c/ProgramData/Laragon/www
```

---

# Quick Start Summary

```bash
cd /c/ProgramData/Laragon/www

composer create-project laravel/laravel student-management

cd student-management

npm install

cp .env.example .env

php artisan key:generate

php artisan migrate

npm run dev
```

Open the application in your browser:

- **Laragon Virtual Host:** `http://student-management.test`
- **Laravel Development Server:** `http://127.0.0.1:8000`
