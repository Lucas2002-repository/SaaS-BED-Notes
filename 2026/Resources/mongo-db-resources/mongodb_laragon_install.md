# MongoDB Installation Guide (Windows + Laragon)

This guide explains a practical way to install MongoDB inside Laragon so students can run it alongside their usual Laravel environment.

## What This Setup Is For

The aim is to keep MongoDB self-contained inside Laragon so it can be started and managed as part of the normal classroom workflow.

For Session 5, students should only need to:

- start Laragon
- confirm MongoDB is running
- connect with `mongosh`
- let Laravel connect using `.env`

Students do not need to manually edit `mongod.conf` unless something is broken.

## Quick Overview

There are four separate pieces in this setup:

1. MongoDB Server
   `mongod.exe` runs the database server on `127.0.0.1:27017`
2. MongoDB Shell
   `mongosh` lets students inspect the database manually
3. PHP MongoDB Extension
   allows PHP and Laravel to talk to MongoDB
4. Laravel MongoDB Package
   gives Laravel Eloquent-style MongoDB integration

Do not treat these as the same thing. MongoDB can be running correctly while PHP still cannot connect to it.

## 1. Download MongoDB Community Server

Use the official MongoDB Community Server download.

Choose:

- Version: `7.0.x`
- Platform: `Windows x64`
- Package: `ZIP`

Use the ZIP package rather than the MSI. For Laragon, the ZIP is preferable because it keeps MongoDB inside the Laragon folder structure instead of scattering files across Windows.

## 2. Extract MongoDB into Laragon

Create this folder if needed:

```text
C:\laragon\bin\mongodb\
```

Then extract MongoDB so the layout looks like this:

```text
C:\laragon\bin\mongodb\
└── mongodb-windows-x86_64-7.0.14\
    └── bin\
        ├── mongod.exe
        └── mongos.exe
```

Your existing setup may instead use:

```text
C:\ProgramData\Laragon\bin\mongodb\
```

If Laragon is installed under `C:\ProgramData\Laragon`, use that location instead of `C:\laragon`.

## 3. Create the MongoDB Data Directory

Your `mongod.conf` points to this data folder:

```text
C:\ProgramData\Laragon\data\mongodb
```

Create that directory if it does not already exist.

The overall layout should look something like this:

```text
Laragon
├── bin
│   └── mongodb
│       └── mongodb-windows-7.0.14
│           ├── bin
│           │   └── mongod.exe
│           └── mongod.conf
└── data
    └── mongodb
```

## 4. Confirm `mongod.conf`

The configuration you showed is suitable for a local classroom setup:

```yaml
systemLog:
  destination: file
  logAppend: true
  path: C:\ProgramData\Laragon\bin\mongodb\mongodb-windows-7.0.14\mongod.log
storage:
  dbPath: C:\ProgramData\Laragon\data\mongodb
  engine: wiredTiger
```

You can also make the local network settings explicit:

```yaml
net:
  port: 27017
  bindIp: 127.0.0.1
```

That makes it clear MongoDB is only listening locally on the standard MongoDB port.

If the `net` section is commented out, MongoDB will still normally use its default local port `27017`.

## 5. Test MongoDB Manually First

Before worrying about Laragon integration, test the server directly from the MongoDB `bin` folder:

```bash
mongod --config "..\\mongod.conf"
```

You should see MongoDB start without errors.

Laravel will eventually connect to:

```text
mongodb://127.0.0.1:27017/
```

## 6. Install `mongosh` Separately

Modern MongoDB Community Server ZIP packages do not always include `mongosh`.

Download and install the official MongoDB Shell separately.

Once it is installed and available on `PATH`, test it:

```bash
mongosh
```

You should get a prompt similar to:

```text
test>
```

Then run:

```bash
show dbs
```

## 7. Install the PHP MongoDB Extension

This is a separate requirement.

MongoDB running successfully does not mean PHP can use it.

In a Laragon terminal, first check:

```bash
php -v
php --ri mongodb
```

If `php --ri mongodb` reports that the extension is unavailable, install the MongoDB PHP extension that matches the PHP version and architecture used by Laragon.

After enabling it in the relevant `php.ini`, restart Laragon and test again:

```bash
php --ri mongodb
```

Do not continue to the Laravel package until that command succeeds.

## 8. Install Laravel MongoDB Support

Inside the MyJamJar Laravel project, install the package:

```bash
composer require mongodb/laravel-mongodb:^5.8
```

Then configure `.env` like this:

```env
DB_CONNECTION=mongodb
DB_URI=mongodb://127.0.0.1:27017/
DB_DATABASE=myjamjar
```

## How the Pieces Fit Together

This is the intended flow:

```text
Laragon
↓
mongod.exe
↓
mongod.conf
↓
MongoDB server
127.0.0.1:27017
↓
myjamjar database
↓
users
tasks
personal_access_tokens
```

Laravel then connects using:

```env
DB_CONNECTION=mongodb
DB_URI=mongodb://127.0.0.1:27017/
DB_DATABASE=myjamjar
```

Your `mongod.conf` is mainly useful for confirming:

- where MongoDB stores its data
- where MongoDB writes its logs

Based on the configuration shown earlier:

- database files are stored in `C:\ProgramData\Laragon\data\mongodb`
- logs are written to `C:\ProgramData\Laragon\bin\mongodb\mongodb-windows-7.0.14\mongod.log`

## Starting MongoDB in Laragon

If MongoDB has been added to Laragon correctly, the normal classroom workflow should be:

```text
Laragon
→ Start All
```

Then verify from a terminal:

```bash
mongosh
show dbs
```

After running Laravel seeders:

```bash
php artisan db:seed
```

Students can inspect the database in `mongosh`:

```bash
use myjamjar
show collections
db.tasks.find()
db.tasks.find().pretty()
```

Expected collections will eventually include:

- `tasks`
- `users`
- `personal_access_tokens`

## Suggested Practical Addition

If you want to make the practical more usable, add a short section like this:

## Inspect MongoDB

Open a Laragon terminal:

```bash
mongosh
```

Select the MyJamJar database:

```bash
use myjamjar
```

List the collections:

```bash
show collections
```

Inspect the Task documents:

```bash
db.tasks.find().pretty()
```

## Practical Checks

Use these checks when troubleshooting:

- `mongod --config "..\\mongod.conf"` to verify the server starts manually
- `mongosh` to verify shell access
- `show dbs` to confirm the server is responding
- `php --ri mongodb` to confirm PHP has the MongoDB extension enabled
- confirm `.env` points to `mongodb://127.0.0.1:27017/`

## Important Teaching Note

For Session 5, students should not be editing `mongod.conf`. At this stage they only need to understand:

- `mongod.conf` configures the local MongoDB server
- Laravel's `.env` tells the application how to connect to that server
