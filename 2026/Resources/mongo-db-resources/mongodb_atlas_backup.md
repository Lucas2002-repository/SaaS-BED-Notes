# Session 5 Fallback — Use MongoDB Atlas Instead of Local MongoDB

## Purpose

Use this guide only if the local MongoDB/Laragon installation is unavailable.

This is a backup plan for your classroom setup, not a guide to MongoDB Atlas Cloud Backups.

According to the official MongoDB Atlas backup documentation, Atlas Cloud Backups are not available for free-tier clusters. For Session 5, the practical workaround is to use a free Atlas cluster as the database host instead of a local MongoDB server.

The Session 5 application code remains the same. The main difference is the MongoDB connection:

```text
Local
mongodb://127.0.0.1:27017/

Atlas
mongodb+srv://<user>:<password>@<cluster>/
```

MongoDB Atlas provides a managed cloud MongoDB deployment. A free cluster is suitable for this classroom exercise.

## Quick Flow

1. Create an Atlas project and free cluster.
2. Create a database user.
3. Add your current IP address to the Atlas access list.
4. Copy the Atlas connection string.
5. Confirm PHP has the MongoDB extension enabled.
6. Update Laravel `.env` to use Atlas.
7. Clear config and continue the Session 5 practical.

---

## 1. Create a MongoDB Atlas Account and Project

Open the official MongoDB Atlas site:

https://www.mongodb.com/atlas

Create or sign in to your MongoDB account.

Create a new Atlas project if required.

Suggested project name:

```text
MyJamJar
```

---

## 2. Create a Free Cluster

From the Atlas project:

1. Open **Database / Clusters**.
2. Choose **Create** or **Build a Database**.
3. Select the **Free** cluster option.
4. Choose an available cloud provider and region.
5. Give the cluster a recognisable name.

Example:

```text
myjamjar-cluster
```

Create the cluster and wait for Atlas to make it available.

> One free cluster is enough for the Session 5 practical.

### Important

The official Atlas backup documentation uses the term **Cloud Backup** for snapshot-based backup features. That is separate from what you are doing here.

For this practical:

- you are using Atlas as the live database host
- you are not configuring Atlas Cloud Backups
- a free cluster is sufficient for this fallback workflow

---

## 3. Create a Database User

Atlas database users are different from the account used to sign in to the Atlas website.

Create a database user for the Laravel application.

Example username:

```text
myjamjar_user
```

Create a strong password and save it securely.

Do not use your Atlas account password.

### Important

The username and password will become part of the application's MongoDB connection string.

Do not:

- commit the password to Git
- place it in screenshots
- paste it into assessment evidence
- share another student's credentials

Keep credentials in `.env`.

---

## 4. Allow Your Computer to Connect

Atlas restricts which IP addresses can connect.

During the connection setup, choose:

```text
Add My Current IP Address
```

Atlas adds the current public IP address to the project's IP Access List.

If your network changes later, you may need to add the new IP address.

### Classroom Note

If Atlas reports a timeout while the credentials appear correct, check the **Network Access / IP Access List** before changing Laravel code.

---

## 5. Get the Laravel Connection String

Open the cluster and choose:

```text
Connect
→ Drivers
```

Select the PHP driver where Atlas asks for a driver.

Atlas will provide a connection string similar to:

```text
mongodb+srv://myjamjar_user:<db_password>@myjamjar-cluster.xxxxx.mongodb.net/
```

Copy the connection string.

Replace:

```text
<db_password>
```

with the database user's password.

If Atlas also shows setup helpers for Compass or Shell, ignore those for now. For this practical, you only need the PHP/Laravel driver connection string.

Do not place the real connection string in source-controlled PHP files.

---

## 6. Check the PHP MongoDB Extension

Atlas replaces the **MongoDB server**, not the PHP driver.

Laravel still needs the MongoDB PHP extension.

Run:

```bash
php --ri mongodb
```

If PHP cannot find the MongoDB extension, Atlas will not solve that problem.

Enable/install the MongoDB PHP extension before continuing.

---

## 7. Install Laravel MongoDB Support

If this has not already been completed in the practical, run:

```bash
composer require mongodb/laravel-mongodb:^5.8
```

The same Laravel MongoDB package is used for both:

```text
Local MongoDB
and
MongoDB Atlas
```

---

## 8. Keep the MongoDB Laravel Connection

**File:**

```text
config/database.php
```

Use the same MongoDB connection from the Session 5 practical:

```php
'mongodb' => [
    'driver' => 'mongodb',
    'dsn' => env('DB_URI'),
    'database' => env(
        'DB_DATABASE',
        'myjamjar'
    ),
],
```

The application does not need separate model code for Atlas.

---

## 9. Change the `.env` Connection

**File:**

```text
.env
```

Instead of the local connection:

```text
DB_URI=mongodb://127.0.0.1:27017/
```

use the Atlas connection string:

```text
DB_CONNECTION=mongodb
DB_URI="mongodb+srv://myjamjar_user:YOUR_PASSWORD@YOUR_CLUSTER.mongodb.net/"
DB_DATABASE=myjamjar
```

Use your actual Atlas values.

Keep:

```text
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

### Important

Do not copy the example URI literally.

Your Atlas hostname, username and password will be different.

If your password contains special characters, use the Atlas-generated connection string format carefully so the URI remains valid.

---

## 10. Clear Laravel Configuration

After changing `.env`, run:

```bash
php artisan config:clear
```

Then:

```bash
php artisan about
```

This prevents Laravel from continuing to use a cached local MongoDB connection.

If you still see connection issues after changing `.env`, re-run:

```bash
php artisan config:clear
```

---

## 11. Continue the Normal Session 5 Practical

From this point, return to the normal Session 5 practical.

The application should still migrate:

```text
User
PersonalAccessToken
Task
```

and use:

```text
users
personal_access_tokens
tasks
```

The Request, Payload, Job, Resource and Response work does not change because Atlas is being used.

---

## 12. Seed the Atlas Database

Run:

```bash
php artisan db:seed
```

If successful, the application should create MongoDB data in Atlas.

Do not run the original SQL migrations as the MongoDB setup step.

---

## 13. Inspect the Database in Atlas

In Atlas, open the cluster and use the database/collection browser.

Locate:

```text
myjamjar
```

You should begin to see:

```text
myjamjar
├── users
├── tasks
└── personal_access_tokens
```

`personal_access_tokens` may not appear until a successful login creates a token.

## Practical Checks

Use these checks if the Atlas setup is not working:

- confirm the Atlas cluster status is ready
- confirm the database user exists and the password is correct
- confirm your public IP is in the Atlas IP Access List
- run `php --ri mongodb` to confirm PHP can use the MongoDB extension
- run `php artisan config:clear` after every `.env` change
- confirm `DB_CONNECTION=mongodb`
- confirm `DB_URI` is using the Atlas `mongodb+srv://` format

## Important Distinction

For this document:

- "backup" means a fallback option when local MongoDB is unavailable
- it does not mean Atlas snapshot backups

MongoDB's official Atlas backup documentation uses **Cloud Backup** for managed snapshot and restore features, and those features have separate access rules and limitations from the free-cluster workflow used in this practical.

Open `tasks` to inspect the Task documents created by the API.

---

## 14. Test Authentication

Use the normal MyJamJar login endpoint:

```text
POST /api/v1/auth/login
```

Use the seeded credentials from the practical.

Confirm:

```text
Laravel
   ↓
Atlas users collection
   ↓
authentication
   ↓
Atlas personal_access_tokens collection
   ↓
bearer token
```

Then use the token with the protected Task routes.

---

## 15. Test the Task Vertical Slice

Continue using the same API routes:

```text
GET    /api/v1/tasks
POST   /api/v1/tasks
GET    /api/v1/tasks/{task}
PATCH  /api/v1/tasks/{task}
DELETE /api/v1/tasks/{task}
```

The expected architecture remains:

```text
Request
→ Payload
→ Job
→ MongoDB Model
→ Atlas
→ Resource
→ Response
```

Atlas changes **where MongoDB runs**, not the application architecture.

---

## 16. Common Atlas Problems

### Connection timeout

Check:

```text
Atlas
→ Network Access
→ IP Access List
```

Confirm the computer's current public IP address is allowed.

---

### Authentication failed

Check:

```text
Database Access
```

Confirm:

- the database username
- the database password
- the password in `DB_URI`

Remember that the Atlas website account and MongoDB database user are separate.

---

### Password contains special characters

Special characters in a password may need URL encoding when used inside a connection URI.

The simplest classroom option is to generate/use a strong password that can be safely placed into the supplied Atlas URI, or carefully use the Atlas-provided connection instructions.

---

### Laravel still tries `127.0.0.1`

Run:

```bash
php artisan config:clear
```

Then inspect:

```text
.env
```

Confirm:

```text
DB_URI="mongodb+srv://..."
```

rather than:

```text
mongodb://127.0.0.1:27017/
```

---

### `Driver [mongodb] not supported`

This is not an Atlas problem.

Check:

```bash
composer show mongodb/laravel-mongodb
```

and confirm the MongoDB provider/configuration from the Session 5 practical.

---

### `php --ri mongodb` fails

This is also not an Atlas problem.

Atlas provides the MongoDB **server**.

PHP still requires its MongoDB extension to communicate with MongoDB.

---

## Local vs Atlas

| Local MongoDB | MongoDB Atlas |
|---|---|
| MongoDB runs on the classroom PC | MongoDB runs in the cloud |
| Laragon starts the server | Atlas manages the server |
| `127.0.0.1:27017` | `mongodb+srv://...` |
| No Atlas IP rules | IP Access List required |
| Local data directory | Atlas-managed storage |
| Same Laravel models | Same Laravel models |
| Same Requests/Payloads/Jobs | Same Requests/Payloads/Jobs |
| Same API routes | Same API routes |

---

## Switching Back to Local MongoDB

When the local installation is working again, only the connection needs to change.

**File:**

```text
.env
```

Change:

```text
DB_URI="mongodb+srv://..."
```

back to:

```text
DB_URI=mongodb://127.0.0.1:27017/
```

Then run:

```bash
php artisan config:clear
```

No Task architecture changes should be required.

---

## Security Reminder

Never commit an Atlas URI containing credentials.

Check:

```text
.gitignore
```

and ensure:

```text
.env
```

is excluded.

A safe repository may contain:

```text
.env.example
```

with placeholders such as:

```text
DB_CONNECTION=mongodb
DB_URI=
DB_DATABASE=myjamjar
```

but never the real Atlas password.

---

## Fallback Summary

If Laragon MongoDB fails:

```text
1. Create free Atlas cluster
2. Create database user
3. Add current IP address
4. Copy the Atlas driver connection string
5. Confirm PHP MongoDB extension
6. Keep Laravel MongoDB package/configuration
7. Put Atlas URI in .env
8. php artisan config:clear
9. Continue the normal Session 5 practical
10. Seed, authenticate and test Tasks
11. Inspect documents in Atlas
```

> **Atlas replaces the local MongoDB server. It does not replace the Laravel MongoDB integration or the Session 5 API architecture.**

---

## Official References

MongoDB Atlas:
https://www.mongodb.com/atlas

Free Atlas cluster:
https://www.mongodb.com/docs/atlas/tutorial/deploy-free-tier-cluster/

Atlas Cloud Backup overview:
https://www.mongodb.com/docs/atlas/backup/cloud-backup/overview/

Atlas snapshot management:
https://www.mongodb.com/docs/atlas/backup/cloud-backup/snapshot-management/

Atlas restore from snapshot:
https://www.mongodb.com/docs/atlas/backup/cloud-backup/restore-from-snapshot/

Laravel MongoDB:
https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/

Laravel MongoDB connection guide:
https://www.mongodb.com/docs/drivers/php/laravel-mongodb/current/fundamentals/connection/connect-to-mongodb/
