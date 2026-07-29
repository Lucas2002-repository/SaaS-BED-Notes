---
marp: true
theme: nmtafe_v2
paginate: true
footer: ICT50220 Diploma of Information Technology | SaaS 2 | Session 02
header: Session 02 – Laravel 12 Data and APIs
---

# Session 02
## Laravel 12 – Data and APIs

### SaaS 2 – REST API Development

**Today's focus**

- Database structure
- Seeding data
- Application environments
- Database connections
- Resourceful controllers
- API versioning

---

# Learning Outcomes

By the end of today's session you should be able to:

- Explain the difference between migrations and seeders
- Configure database connections using `.env`
- Describe Laravel environments
- Create Resourceful and API Resourceful controllers
- Explain API versioning
- Use common Artisan commands for database development

---

# Session Roadmap

1. Migrations vs Seeders
2. Application Environments
3. Database Connections
4. Resourceful Controllers
5. API Controllers
6. API Versioning
7. Practical Activity

---

# Migrations vs Seeders

Laravel separates **database structure** from **database data**.

| Migrations | Seeders |
|------------|----------|
| Define database structure | Populate data |
| Create tables | Insert records |
| Add columns | Create sample data |
| Remove columns | Insert lookup data |
| Version controlled | Safe to rerun |

---

# Think of it Like Building a House

| Building a House | Laravel |
|------------------|----------|
| Blueprint | Migration |
| Furniture | Seeder |
| Renovation | New Migration |
| Restocking | Run Seeder |

> **Migration = Structure**

> **Seeder = Data**

---

# Creating a Migration

Generate a migration using Artisan.

```bash
php artisan make:migration create_courses_table --create=courses
```

Laravel creates a timestamped migration inside

```
database/migrations
```

Each migration should perform **one structural change**.

---

# Anatomy of a Migration

Every migration contains two methods.

```php
public function up(): void
{
    // Apply changes
}

public function down(): void
{
    // Undo changes
}
```

- **up()** applies the change

- **down()** reverses the change

This allows Laravel to rollback database changes safely.

---

# Example Migration

```php
Schema::create('courses', function (Blueprint $table) {

    $table->id();

    $table->string('code')->unique();

    $table->string('title');

    $table->timestamps();

});
```

This creates the **courses** table.

---

# Running Migrations

Apply every pending migration.

```bash
php artisan migrate
```

Rollback the most recent migration.

```bash
php artisan migrate:rollback
```

Rebuild everything.

```bash
php artisan migrate:fresh
```

---

# Knowledge Check

Which statement is correct?

A. Migrations insert sample data

B. Seeders create tables

C. Migrations define database structure

D. Controllers create databases

- **Answer: C**

---

# Introducing Seeders

Seeders populate your database with data.

Typical uses include:

- Lookup tables
- Default administrator account
- Demo records
- Test data

Seeders live in

```
database/seeders
```

---

# Creating a Seeder

```bash
php artisan make:seeder CourseSeeder
```

Laravel creates

```
database/seeders/CourseSeeder.php
```

---

# Basic Seeder Example

```php
public function run(): void
{
    Course::factory()
        ->count(20)
        ->create();
}
```

Factories create realistic sample records automatically.

---

# Idempotent Seeding

Instead of inserting duplicates...

```php
Course::create(...);
```

Prefer

```php
Course::updateOrCreate(...);
```

Benefits:

- Safe to rerun

- No duplicate data

- Consistent environments

---

# DatabaseSeeder

The **DatabaseSeeder** controls the order seeders run.

```php
public function run(): void
{
    $this->call([
        CourseSeeder::class,
    ]);
}
```

Run everything with

```bash
php artisan db:seed
```

---

# Best Practice

Run seeders in dependency order.

1. Lookup tables

2. Parent tables

3. Child tables

4. Junction tables

This avoids foreign key errors.

---

# Section Summary

You should now understand:

- What migrations are

- What seeders are

- Why Laravel separates structure and data

- The most common Artisan commands

Next we will look at **Application Environments**.

---
# Application Environments

---

# What is an Environment?

An environment defines **how your application behaves** in a particular situation.

Common environments include:

| Environment | Purpose |
|--------------|---------|
| Local | Development on your computer |
| Testing | Automated testing |
| Staging | Pre-production testing |
| Production | Live application |

Each environment uses different configuration values.

---

# The `.env` File

Laravel stores environment-specific settings in the `.env` file.

Typical settings include:

```dotenv
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=college
DB_USERNAME=root
DB_PASSWORD=secret
```

The `.env` file should **never** be committed to Git.

---

# How Laravel Uses Configuration

When Laravel starts it:

1. Reads the `.env` file.
2. Loads values into the configuration files.
3. Makes configuration available to the application.

```
.env
      │
      ▼
config/*.php
      │
      ▼
Laravel Application
```

This allows the same application to run in multiple environments without changing the code.

---

# Development vs Production

| Development | Production |
|-------------|------------|
| `APP_ENV=local` | `APP_ENV=production` |
| `APP_DEBUG=true` | `APP_DEBUG=false` |
| Detailed error messages | Generic error pages |
| Local database | Production database |

Always disable debugging in production.

---

# Best Practice

Use the `config()` helper throughout your application.

```php
config('database.default');
```

Avoid using `env()` directly outside the configuration files.

```php
// Avoid
env('DB_DATABASE');
```

Using `config()` ensures configuration caching works correctly.

---

# Configuration Caching

Laravel can cache configuration to improve performance.

Create the cache:

```bash
php artisan config:cache
```

Clear the cache:

```bash
php artisan config:clear
```

Whenever the `.env` file changes, clear and rebuild the configuration cache.

---

# Other Useful Cache Commands

```bash
php artisan route:cache
```

Caches application routes.

```bash
php artisan route:clear
```

Clears the route cache.

```bash
php artisan view:clear
```

Removes compiled Blade templates.

```bash
php artisan optimize:clear
```

Clears all Laravel caches.

---

# Configuration Best Practices

- Keep secrets in the `.env` file.
- Commit `.env.example` only.
- Use different configuration for each environment.
- Cache configuration in production.
- Clear caches after configuration changes.

---

# Knowledge Check

Which file should contain database passwords?

A. `routes/web.php`

B. `config/database.php`

C. `.env`

D. `composer.json`

**Answer:** C

---

# Section Summary

You should now understand:

- The purpose of application environments.
- How Laravel loads configuration.
- Why the `.env` file is important.
- The difference between development and production.
- When to clear and cache configuration.

Next we will examine **Database Connections**.
---
# Database Connections

---

# Why Database Connections Matter

A Laravel application can communicate with one or more databases.

Examples include:

- MySQL
- MariaDB
- PostgreSQL
- SQLite
- MongoDB (using a package)

Laravel chooses the connection defined as the **default**.

---

# Where are Connections Defined?

Database connections are configured in:

```
config/database.php
```

The default connection is selected using the `.env` file.

```php
'default' => env('DB_CONNECTION', 'mysql'),
```

Changing the `.env` file changes the application's default database.

---

# Typical MySQL Connection

Laravel reads the connection details from the environment.

```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST'),
    'port' => env('DB_PORT'),
    'database' => env('DB_DATABASE'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],
```

Notice that no credentials are hardcoded.

---

# Multiple Database Connections

Laravel can work with multiple databases in the same application.

Example:

```
connections
├── mysql
├── reporting
├── testing
└── mongodb
```

Common uses include:

- Reporting databases
- Legacy systems
- Separate analytics databases
- Migration projects

---

# Selecting a Connection

Use the Database facade when writing direct SQL.

```php
use Illuminate\Support\Facades\DB;

$users = DB::connection('reporting')
    ->select('SELECT * FROM users');
```

Laravel executes the query using the selected connection.

---

# Using Eloquent Models

Most applications use Eloquent instead of writing SQL.

A model can specify its own connection.

```php
class Event extends Model
{
    protected $connection = 'reporting';
}
```

Now every query using this model automatically uses the reporting database.

---

# Querying with Eloquent

Once a model has a connection configured, querying is simple.

```php
$count = Event::count();
```

Laravel automatically:

- Opens the correct database
- Builds the SQL
- Returns the result

This keeps application code clean and readable.

---

# Temporary Connection Changes

Sometimes only a single query should use another database.

```php
Event::on('reporting')
    ->where('status', 'active')
    ->get();
```

The model remains unchanged after the query finishes.

---

# MongoDB Connections

For this course we will also use MongoDB.

The configuration is very similar.

```dotenv
DB_CONNECTION=mongodb
DB_URI=mongodb://127.0.0.1:27017
DB_DATABASE=college_api
```

Laravel uses the MongoDB driver to communicate with the database.

---

# SQL vs MongoDB

| SQL Database | MongoDB |
|--------------|----------|
| Tables | Collections |
| Rows | Documents |
| Columns | Fields |
| JOINs | Embedded documents or references |
| Fixed schema | Flexible schema |

The Laravel application still uses models to work with both.

---

# Choosing the Right Approach

**DB Facade**
- Raw SQL
- Complex queries
- Stored procedures
- Database administration

**Eloquent**
- CRUD operations
- Business logic
- Relationships
- Most day-to-day development

Eloquent should be your default choice.

---

# Best Practices

- Keep credentials in `.env`
- Never hardcode passwords
- Use descriptive connection names
- Prefer Eloquent for application code
- Use raw SQL only when necessary
- Test every connection before deployment

---

# Knowledge Check

Which statement is correct?

A. Every Laravel application can only connect to one database.

B. Database passwords should be stored in the model.

C. Eloquent models can specify their own database connection.

D. SQL queries always require raw SQL.

**Answer:** C

---

# Section Summary

You should now understand:

- How Laravel configures database connections.
- How the default connection is selected.
- How to work with multiple databases.
- The difference between the DB facade and Eloquent.
- How MongoDB fits into the Laravel ecosystem.

Next we will explore **Resourceful Controllers and API Controllers**.

---

# Laravel Architecture

## Where Does the Database Fit?

```
                 Client
                    │
          HTTP Request / JSON
                    │
         Laravel Application
                    │
        Controllers & Validation
                    │
            Eloquent Models
             /            \
     MySQL Database     MongoDB
    (Relational)       (Document)
             \            /
              Business Data
                    │
              JSON Response
```

---

Although MySQL and MongoDB store data differently, the Laravel application is built in almost the same way.

Controllers, validation, routes and business logic remain largely unchanged.

Only the persistence layer changes.

---

# Why This Matters

In Stage 1 you worked with **MySQL**.

In Stage 2 you will introduce **MongoDB**.

Most of your Laravel skills transfer directly.

Only a few concepts change:

| Same | Changes |
|------|---------|
| Controllers | Database structure |
| Routes | Relationships |
| Validation | Queries |
| Requests | Document modelling |
| Responses | Collections instead of tables |

The goal is to learn **a new persistence model**, not a completely new framework.

---

# Resourceful Controllers

## Keeping Controllers Consistent

A controller groups together actions that belong to a resource.

For example:

```
Course
```

becomes

```
CourseController
```

Each method performs one operation on that resource.

---

# CRUD Operations

Most applications perform the same five operations.

| Operation | HTTP Method |
|-----------|-------------|
| Create | POST |
| Read | GET |
| Update | PUT / PATCH |
| Delete | DELETE |
| List | GET |

Laravel provides a standard structure for these operations.

---

# Generating a Resource Controller

Use Artisan to generate a web controller.

```bash
php artisan make:controller CourseController --resource
```

Laravel creates a controller containing all standard CRUD methods.

---

# Resource Controller Methods

```text
index()

create()

store()

show()

edit()

update()

destroy()
```

These methods support **server-rendered web applications**.

---

# Resource Routes

Laravel automatically maps routes to controller methods.

```php
Route::resource(
    'courses',
    CourseController::class
);
```

One line creates all CRUD routes.

---

# Generated Routes

| Method | URI | Action |
|---------|-----|--------|
| GET | /courses | index |
| GET | /courses/create | create |
| POST | /courses | store |
| GET | /courses/{id} | show |
| GET | /courses/{id}/edit | edit |
| PUT/PATCH | /courses/{id} | update |
| DELETE | /courses/{id} | destroy |

Laravel follows these conventions automatically.

---

# Resource Controllers in MVC

```
Browser
   │
Request
   │
Controller
   │
Model
   │
Database
   │
View
   │
Browser
```

Resource controllers are designed for traditional web applications that return HTML pages.

---

# API Controllers

REST APIs do not display HTML pages.

Instead they return JSON.

For APIs we use **API Resource Controllers**.

---

# Creating an API Controller

```bash
php artisan make:controller \
Api/V1/CourseController --api
```

Notice the `--api` option.

Laravel creates a controller designed specifically for REST APIs.

---

# API Controller Methods

```text
index()
store()
show()
update()
destroy()
```

The following methods are removed:

```
create()
edit()
```

Why? REST APIs do not display HTML forms.

---

# Web vs API Controllers

| Web Controller | API Controller |
|---------------|----------------|
| Returns HTML | Returns JSON |
| Uses Blade views | Used by mobile/web clients |
| Includes create() | No create() |
| Includes edit() | No edit() |
| Route::resource() | Route::apiResource() |

Choose the controller that matches your application.

---

# Creating API Routes

```php
Route::apiResource(
    'courses',
    CourseController::class
);
```

Laravel automatically creates REST endpoints.

No HTML routes are generated.

---

# Knowledge Check

Which controller should you use for a REST API?

A. Resource Controller
B. API Resource Controller
C. Database Controller
D. Migration Controller

**Answer:** B

---

# Section Summary

You should now understand:

- How Laravel architecture remains consistent across MySQL and MongoDB.
- What a Resource Controller is.
- What an API Resource Controller is.
- The difference between web and API controllers.
- How Laravel generates REST routes automatically.

Next we will look at **Route Naming and API Versioning**.

---
# Route Naming and API Versioning

---

# Why Do Routes Matter?

Routes determine **how clients communicate** with your application.

A good route should be:

- Easy to understand
- Predictable
- Consistent
- Resource-oriented

Good routes reduce documentation and make APIs easier to use.

---

# REST Uses Resources

Think about **things**, not actions.

Instead of:

```
/getCourses
/addCourse
/deleteCourse
```

Think in terms of resources.

```
/courses
```

The HTTP method tells Laravel what action to perform.

---

# HTTP Methods Drive Behaviour

| HTTP Method | Resource | Action |
|-------------|----------|--------|
| GET | `/courses` | List courses |
| GET | `/courses/15` | View one course |
| POST | `/courses` | Create |
| PUT | `/courses/15` | Replace |
| PATCH | `/courses/15` | Update |
| DELETE | `/courses/15` | Delete |

The URL remains consistent.

Only the HTTP method changes.

---

# Resource-Oriented Design

Avoid verbs in your routes. Instead of

```
POST /createCourse
```
prefer
```
POST /courses
```
Instead of
```
DELETE /deleteCourse/15
```
prefer
```
DELETE /courses/15
```
This follows REST conventions.

---

# Creating API Routes

Laravel provides a shortcut.

```php
Route::apiResource(
    'courses',
    CourseController::class
);
```

Laravel automatically creates all REST endpoints.

No manual route definitions are required.

---

# Generated API Routes

```text
GET      /courses
GET      /courses/{course}
POST     /courses
PUT      /courses/{course}
PATCH    /courses/{course}
DELETE   /courses/{course}
```
One statement creates an entire REST API.

---

# Named Routes

Laravel automatically assigns route names.

```text
courses.index
courses.show
courses.store
courses.update
courses.destroy
```

Named routes make applications easier to maintain.

---

# Why Use Named Routes?

Instead of hardcoding URLs...

```php
'/courses'
```

Use route names.

```php
route('courses.index');
```

Benefits include:

- Easier refactoring
- Centralised routing
- Fewer broken links
- Cleaner code

---

# Organising API Routes

As applications grow, routes should be grouped.

```php
Route::middleware('api')
    ->group(function () {

        // API routes

    });
```

Groups keep related routes together.

---

# Route Prefixes

Prefixes organise routes.

```php
Route::prefix('api')
```
creates
```
/api/courses
```
Another prefix
```php
Route::prefix('admin')
```
creates
```
/admin/users
```
Large applications often have many route groups.

---

# Why Version an API?

Applications evolve.

New versions may:

- Add features
- Remove fields
- Improve performance
- Introduce breaking changes

Existing clients should continue working.

Versioning solves this problem.

---

# URL Versioning

A common approach is placing the version in the URL.

```
/api/v1/courses

/api/v2/courses
```

Clients choose which version they use.

---

# Organising Controllers

Keep controller versions separate.

```
app/
└── Http/
    └── Controllers/
        └── Api/
            ├── V1/
            │     CourseController.php
            └── V2/
                  CourseController.php
```

Each version can evolve independently.

---

# Versioned Routes

```php
use App\Http\Controllers\Api\V1\CourseController;

Route::prefix('api/v1')
    ->middleware('api')
    ->group(function () {
        Route::apiResource(
            'courses',
            CourseController::class
        );
    });
```

Every endpoint now belongs to Version 1.

---

# Supporting Multiple Versions

As the application grows:

```
api/v1
api/v2
api/v3
```

Older versions remain available while new functionality is introduced.

This allows clients to migrate at their own pace.

---

# Deprecating an API

Eventually an API version reaches end of life.

Typical process:

1. Announce deprecation.
2. Provide migration documentation.
3. Support both versions for a period.
4. Remove the old version.

Enterprise software rarely removes an API immediately.

---

# Example Project Structure

```
routes/
    api.php
app/
    Http/
        Controllers/
            Api/
                V1/
                V2/
app/
    Models/
```
This structure scales well as projects grow.

---

# Best Practices

- Use nouns rather than verbs.
- Keep URLs consistent.
- Version only when required.
- Never break existing clients without notice.
- Group related routes.
- Use `Route::apiResource()` whenever appropriate.

Consistency is more important than creativity.

---

# Knowledge Check

Which route best follows REST conventions?

A.
```
POST /createCourse
```
B.
```
GET /deleteCourse
```
C.
```
POST /courses
```
D.
```
GET /addNewCourse
```
**Answer:** C

---

# Section Summary

You should now understand:

- How REST routes are designed.
- Why Laravel uses `apiResource()`.
- How named routes improve maintainability.
- How route groups and prefixes organise applications.
- Why enterprise APIs use versioning.
- How multiple API versions coexist.

Next we will finish the session with **Essential Artisan Commands**, followed by a practical exercise that combines migrations, seeders, environments, database connections and API controllers.

---
# Putting It All Together

## Building Your First REST API

---

# The Development Workflow

When building a Laravel REST API, follow a consistent workflow.

```
Design
   │
Migration
   │
Model
   │
Seeder
   │
Controller
   │
Routes
   │
Testing
   │
Deploy
```
Each step builds on the previous one.

---

# Step 1 — Create the Model
Generate a model.

```bash
php artisan make:model Course
```
This creates:

```
app/Models/Course.php
```
The model represents one document or record within your application.

---

# Step 2 — Create the Migration
Generate the database structure.

```bash
php artisan make:migration create_courses_table --create=courses
```

Then apply it.

```bash
php artisan migrate
```

Your database now contains a **courses** table.

---

# Step 3 — Generate Sample Data
Create a seeder.

```bash
php artisan make:seeder CourseSeeder
```

Populate the table.

```bash
php artisan db:seed
```

Now you have data to develop and test against.

---

# Step 4 — Create the API Controller
Generate an API controller.

```bash
php artisan make:controller \
Api/V1/CourseController --api
```

Laravel creates the five REST methods automatically.

```
index
store
show
update
destroy
```
---

# Step 5 — Register the Routes

Inside `routes/api.php`

```php
Route::prefix('v1')
    ->group(function () {
        Route::apiResource(
            'courses',
            CourseController::class
        );
    });
```

Your REST endpoints now exist.

---

# Step 6 — Test the API

Open Postman. Send a request.

```
GET
/api/v1/courses
```
Expected response:
```json
[
    {
        "id": 1,
        "code": "CPT101",
        "title": "Computing Fundamentals"
    }
]
```

Testing should begin as soon as the endpoint exists.

---

# The Complete Request Flow

```
Client
   │
HTTP Request
   │
API Route
   │
Controller
   │
Model
   │
Database
   │
JSON Response
   │
Client
```

This is the same architecture used by enterprise Laravel applications.

---

# Where Does MongoDB Fit?

In Stage 1 the flow is:

```
Controller
      │
Eloquent
      │
MySQL
```

---

In Stage 2 it becomes:

```
Controller
      │
Eloquent

      │
MongoDB
```

Everything above the persistence layer remains almost identical.

---

# Common Artisan Commands

| Task | Command |
|------|---------|
| Create model | `php artisan make:model Course` |
| Create migration | `php artisan make:migration ...` |
| Run migrations | `php artisan migrate` |
| Rollback | `php artisan migrate:rollback` |
| Create seeder | `php artisan make:seeder` |
| Run seeders | `php artisan db:seed` |
| Create API controller | `php artisan make:controller --api` |
| List routes | `php artisan route:list` |

These commands form the foundation of Laravel development.

---

# Typical Development Cycle

As you build features you will often repeat this cycle.

```
Change Model
      │
Update Migration
      │
Run Migration
      │
Update Seeder
      │
Test API
      │
Commit to Git
```

Professional developers repeat this process many times each day.

---

# Practical Investigation

Working individually or in pairs:

Create a simple **Course API**.

Requirements:

- Create the model.
- Create the migration.
- Add three fields.
- Run the migration.
- Create a seeder.
- Insert five records.
- Create an API controller.
- Register an `apiResource`.
- Test all endpoints using Postman.

Document any errors you encounter and explain how you resolved them.

---

# End of Session Review

Today you learned how to:

- Separate database structure from data.
- Configure application environments.
- Manage database connections.
- Build Resourceful and API controllers.
- Design REST routes.
- Version enterprise APIs.
- Build a complete Laravel REST API workflow.

These concepts provide the foundation for the remainder of the course.

---

# Preparing for Session 03

Before next class:

- Review today's code examples.
- Read the Laravel documentation on Eloquent Models.
- Ensure your development environment is working.
- Bring your completed Course API project ready for extension.

In Session 03 we will begin implementing full CRUD operations and validation using Eloquent models.
