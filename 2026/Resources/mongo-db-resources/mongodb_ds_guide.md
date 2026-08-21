1.

`C:\ProgramData\laragon\www\Sources\Repos\saas-bed-task-api\.env`

Set:

```dotenv
# DB_CONNECTION=sqlite

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

2. After changing `.env`, run:

```bash
php artisan config:clear
php artisan optimize:clear
php artisan config:cache
```

3.

```bash
php artisan migrate:status
```

4. I have check all available
   Mongodb

<https://www.php.net/mongodb>
<https://github.com/mongodb/mongo-php-driver/releases/>
<https://www.mongodb.com/docs/php-library/current/>
<https://www.mongodb.com/try/download/community>

5. PHP 8.4.10

Blackboard had an incompatible MongoDB DLL, which produced:

```text
Module compiled with module API=20250925
PHP compiled with module API=20240924
These options need to match
```

We corrected this by using the PHP 8.4-compatible MongoDB extension.

PHP installation:

`C:\ProgramData\laragon\bin\php\php-8.4.10-nts-Win32-vs17-x64`

6. Install Laravel MongoDB Package

```bash
composer require mongodb/laravel-mongodb
```

Composer installed:

```text
mongodb/laravel-mongodb 5.9.1
mongodb/mongodb 2.4.0
```

7. MongoDB Community Server

downloaded:

```text
MongoDB Community Server 8.3.8
Windows x64
ZIP
```

We extracted it under:

`C:\ProgramData\laragon\bin\mongodb\`

The important executable is:

`C:\ProgramData\laragon\bin\mongodb\mongodb\bin\mongod.exe`

MongoDB data:

`C:\ProgramData\laragon\data\mongodb-8`

8 .

## Start MongoDB

This is Terminal 1.

Run:

```bash
"C:\ProgramData\laragon\bin\mongodb\mongodb-8.3.8\bin\mongod.exe" --dbpath="C:\ProgramData\laragon\data\mongodb-8"
```

MongoDB successfully started.

We confirmed this with:

```text
Waiting for connections
```

and:

```text
mongod startup complete
```

MongoDB is therefore listening on:

`127.0.0.1:27017`

### Important

Terminal 1 must remain open while MongoDB is running.

When you are finished for the day, you can safely stop MongoDB with:

`Ctrl + C`

## Terminal 2

```bash
php artisan tinker
```

```php
DB::connection('mongodb')->getCollection('test')->insertOne([
    'message' => 'Hello MongoDB',
    'created_at' => now()->toDateTimeString(),
]);
```
