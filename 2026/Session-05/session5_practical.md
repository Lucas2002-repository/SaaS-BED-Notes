---
marp: true
theme: nmtafe_v2
paginate: true
footer: "MyJamJar API • Session 5 • MongoDB Migration Practical"
---

# Session 5 Practical — Migrate the Task Vertical Slice to MongoDB

## Purpose

Session 5 begins from the **completed Session 4 Task architecture**.

The Task flow is already connected as:

```text
Route
→ Request
→ NewTask Payload
→ Controller
→ Task Job
→ Task Model
→ TaskResource
→ Generic Response
```

---

## What we will do

Do **not** rebuild or reconnect this architecture during Session 5.

The goal now is to migrate runtime persistence from MySQL to MongoDB for:

```text
users
personal_access_tokens
tasks
```

while keeping the Task API and Session 4 architecture recognisable.

---

# Scope

## We will change

- check the MongoDB PHP extension
- install Laravel MongoDB support
- configure MongoDB
- migrate `User`
- migrate Sanctum `PersonalAccessToken`
- remove the SQL transaction around login token creation
- migrate `Task`

---

## We will also change

- add `tag_ids`
- update the existing Task Payload
- update Store and Update validation
- remove SQL transaction assumptions from the connected Task Jobs
- seed MongoDB
- authenticate and exercise the existing Task routes
- inspect the resulting MongoDB documents

---

## We Will Not Change

- Task routes
- controller responsibilities
- the Request → Payload → Job architecture
- `TaskResource`
- generic response responsibilities
- create a `Tag` model
- implement a `tags` collection
- migrate cache, session or queue persistence to MongoDB
- investigate embedding vs referencing in depth
- add indexes, optimisation or TTL

---

# Tags in This Practical

We will add a simple array of future Tag IDs to the Task document:

```json
{
  "tag_ids": ["01TAG...", "01TAG..."]
}
```

We are **not** creating a `Tag` model or `tags` collection.

```text
tasks collection
└── tag_ids
       ↓
future tags collection
└── _id
```

For Session 5, `tag_ids` only demonstrates where related identifiers could be stored in a MongoDB document.

---

# Confirm the Session 4 Starting Point

Before changing persistence, confirm that the completed Session 4 architecture is working.

```text
routes/api/tasks.php
app/Http/Api/Requests/Tasks/StoreTaskRequest.php
app/Http/Api/Requests/Tasks/UpdateTaskRequest.php
app/Http/Payloads/Tasks/NewTask.php
app/Jobs/Tasks/CreateNewTask.php
app/Jobs/Tasks/UpdateTask.php
app/Jobs/Tasks/DeleteTask.php
app/Http/Api/Controllers/Tasks/TaskController.php
app/Models/Task.php
app/Http/Api/Resources/TaskResource.php
app/Models/User.php
app/Models/PersonalAccessToken.php
```

---

## Confirm

The live Task flow should already be:

```text
POST /api/v1/tasks
    ↓
StoreTaskRequest
    ↓
NewTask
    ↓
TaskController
    ↓
CreateNewTask
    ↓
Task
    ↓
TaskResource
    ↓
Response
```

--
Update and delete should likewise use:

---

# Establish the SQL Baseline

Run:

```bash
php artisan route:list
```

Then run the existing automated tests:

```bash
php artisan test
```

---

## Important

The existing tests are a **pre-migration SQL/SQLite baseline only**.

The repository's current test setup is not the MongoDB verification step for this practical.

```text
POST   /api/v1/auth/login
GET    /api/v1/tasks
POST   /api/v1/tasks
GET    /api/v1/tasks/{task}
PATCH  /api/v1/tasks/{task}
DELETE /api/v1/tasks/{task}
```

Save one successful Task response for comparison later.

---

# Check the MongoDB PHP Extension

Laravel MongoDB requires the PHP MongoDB extension.

Run:

```bash
php --ri mongodb
```

## Expected Result

PHP should display information about the MongoDB extension.

If the extension is missing, enable/install the MongoDB extension in the PHP version being used by Laragon before continuing.

---

# Install Laravel MongoDB

Run:

```bash
composer require mongodb/laravel-mongodb:^5.8
```

This adds MongoDB support to Laravel.

---

# Register the Providers

Register the existing application provider, MongoDB provider and the application's Sanctum provider.

```php
// bootstrap/providers.php
<?php

use App\Providers\AppServiceProvider;
use App\Providers\SanctumServiceProvider;
use MongoDB\Laravel\MongoDBServiceProvider;

return [
    AppServiceProvider::class,
    MongoDBServiceProvider::class,
    SanctumServiceProvider::class,
];
```

---

## Note

`SanctumServiceProvider` already exists in the application.

Registering it ensures Sanctum uses the custom `PersonalAccessToken` model that we migrate later.

---

# Add the MongoDB Connection

Inside `connections`, add:

```php
// config/database.php
'mongodb' => [
    'driver' => 'mongodb',
    'dsn' => env(
        'DB_URI',
        'mongodb://127.0.0.1:27017/'
    ),
    'database' => env(
        'DB_DATABASE',
        'myjamjar'
    ),
],
```

Keep the existing SQL connection configuration for reference.

---

# Update the Environment

Set MongoDB as the runtime application database:

```text
<!-- .env -->
DB_CONNECTION=mongodb
DB_URI=mongodb://127.0.0.1:27017/
DB_DATABASE=myjamjar
```

For this practical, keep unrelated persistence mechanisms simple:

```text
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
```

---

## Why?

Session 5 is migrating:

```text
users
personal_access_tokens
tasks
```

We are not also introducing MongoDB-backed cache, sessions or queue storage.

---

# Clear Configuration

Run:

```bash
php artisan config:clear

php artisan about
```

Do **not** run:

```bash
php artisan migrate
```

The existing migrations describe the original relational design.
For this introductory exercise, MongoDB collections will be created when documents are written.

---

# Migrate the User Model

Replace:

```php
// app/Models/User.php
use Illuminate\Foundation\Auth\User as Authenticatable;
```

```php
use MongoDB\Laravel\Auth\User as Authenticatable;
```

Add:

```php
protected $connection = 'mongodb';
protected $table = 'users';
```

---

# Preserve Existing User Configuration

Do not replace the whole class. Preserve the existing model attributes such as:

```php
// app/Models/User.php
#[Fillable([
    'name',
    'email',
    'password',
])]
```

and:

```php
#[Hidden([
    'password',
    'remember_token',
])]
```

---

Preserve the existing casts:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

---

## Note

MongoDB stores the primary key internally as `_id`.

Laravel MongoDB continues exposing it through the model's normal `id` property.

The API therefore does not need to expose MongoDB ObjectIds or change its public identifier terminology.

---

# Migrate Sanctum Tokens

Replace the SQL-pinned token model with:

```php
// app/Models/PersonalAccessToken.php
<?php

declare(strict_types=1);

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;
use MongoDB\Laravel\Eloquent\DocumentModel;

final class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use DocumentModel;

    protected $connection = 'mongodb';

    protected $table = 'personal_access_tokens';

    protected $keyType = 'string';
}
```

---

## Note

Sanctum's model comes from another Laravel package.

`DocumentModel` provides the MongoDB behaviour needed by the existing Sanctum model.

---

# Remove the Login SQL Transaction

Remove the SQL transaction dependency. Remove imports such as:

```php
// app/Http/Api/Controllers/Auth/LoginController.php
use Illuminate\Database\DatabaseManager;
use Throwable;
```

If the controller currently injects `DatabaseManager`, remove that constructor dependency.

---

**Replace the transaction-wrapped token creation with direct token creation.**

```php
$token = $request->user()?->createToken(
    name: $request->header(
        'X-Integration-Name',
        'default-integration'
    ),
    abilities: [],
);
```

Keep the application's existing token response code unchanged.

---

## Why?

For this small API, issuing a token is a single document operation.

We do not need the relational transaction wrapper used by the original MySQL version.

---

# Migrate the Task Model

Replace:

```php
// app/Models/Task.php
use Illuminate\Database\Eloquent\Model;
```

with:

```php
use MongoDB\Laravel\Eloquent\Model;
```

Add:

```php
protected $connection = 'mongodb';
protected $table = 'tasks';
```

---

Add `tag_ids` to the existing fillable fields.

```php
//app/Models/Task.php
protected $fillable = [
    'assigned_to',
    'name',
    'description',
    'status',
    'due_date',
    'tag_ids',
];
```

Keep the existing date cast:

```php
protected function casts(): array
{
    return [
        'due_date' => 'date',
    ];
}
```

---

Do **not** add an `array` cast for `tag_ids`.

MongoDB stores PHP arrays directly as BSON arrays.

Example Task document:

```json
{
  "_id": "01J...",
  "name": "Prepare MongoDB migration",
  "status": "in_progress",
  "tag_ids": ["01TAG-1", "01TAG-2"]
}
```

---

# Keep the User Relationship

Keep the existing relationship to `User`.

```text
<!-- app/Models/Task.php -->
Task.assigned_to
        ↓
User.id
```

The application continues using Laravel's `id` terminology.

MongoDB stores the underlying User key as `_id`.

We are not redesigning this relationship in Session 5.

---

# Extend the `NewTask` Payload

Add a Tag ID array to the constructor:

```php
// app/Http/Payloads/Tasks/NewTask.php
public function __construct(
    public string $name,
    public ?string $description,
    public string $status,
    public ?string $dueDate,
    public ?string $assignedTo,
    public array $tagIds,
) {}
```

---

Update `toArray()`:

```php
public function toArray(): array
{
    return [
        'name' => $this->name,
        'description' => $this->description,
        'status' => $this->status,
        'due_date' => $this->dueDate,
        'assigned_to' => $this->assignedTo,
        'tag_ids' => $this->tagIds,
    ];
}
```

---

## Notet

The Payload still has the same purpose:

> carry trusted application data from the Request to the Job.

It does not decide how Tags are stored or retrieved.

---

# Update Store Validation

Keep the existing Task validation. Add:

```php
// app/Http/Api/Requests/Tasks/StoreTaskRequest.php
'tag_ids' => [
    'nullable',
    'array',
],
'tag_ids.*' => [
    'string',
],
```

Keep the existing User validation:

```php
'assigned_to' => [
    'nullable',
    'string',
    'exists:users,id',
],
```

Although MongoDB stores the key as `_id`, the Laravel application continues to use `id`.

---

# Update the Store Payload

Update the existing `payload()` method:

```php
// app/Http/Api/Requests/Tasks/StoreTaskRequest.php
public function payload(): NewTask
{
    $data = $this->validated();

    return new NewTask(
        name: $data['name'],
        description: $data['description'] ?? null,
        status: $data['status'],
        dueDate: $data['due_date'] ?? null,
        assignedTo: $data['assigned_to'] ?? null,
        tagIds: $data['tag_ids'] ?? [],
    );
}
```

The flow remains:

```text
HTTP input
→ validation
→ NewTask
```

---

# Update UpdateTaskRequest

Add:

```php
// app/Http/Api/Requests/Tasks/UpdateTaskRequest.php
'tag_ids' => [
    'sometimes',
    'nullable',
    'array',
],
'tag_ids.*' => [
    'string',
],
```

Keep the existing partial-update validation behaviour.

## Date Format Note

The current repository has fixed the different date rules in the Store and Update requests.

---

# Preserve Tags During Partial Update

Update the existing `payload(Task $task)` method to catch tag updates...

```php
// app/Http/Api/Requests/Tasks/UpdateTaskRequest.php
public function payload(Task $task): NewTask
{
    return new NewTask(
        name: $data['name'] ?? $task->name,
        description: $data['description']
            ?? $task->description,
        status: $data['status']
            ?? $task->status,
        dueDate: $data['due_date']
            ?? $task->due_date?->format('Y-m-d'),
        assignedTo: $data['assigned_to']
            ?? $task->assigned_to,
        tagIds: $data['tag_ids']
            ?? $task->tag_ids
            ?? [],
    );
}
```

---

> Why do we fall back to the existing Task `tag_ids` during a partial update?

---

# Remove SQL Transactions from CreateNewTask

Confirm the Payload import is:

```php
// app/Jobs/Tasks/CreateNewTask.php
use App\Http\Payloads\Tasks\NewTask;
```

Remove SQL-specific imports such as:

```php
use Illuminate\Database\DatabaseManager;
use Throwable;
```

---

Replace the transaction-based `handle()` with:

```php
public function handle(): Task
{
    return Task::query()->create(
        $this->payload->toArray()
    );
}
```

---

# Remove SQL Transactions from UpdateTask

```php
// app/Jobs/Tasks/UpdateTask.php
use App\Http\Payloads\Tasks\NewTask;
```

Again, remove:

```php
use Illuminate\Database\DatabaseManager;
use Throwable;
```

---

Use:

```php
public function handle(): Task
{
    $this->task->update(
        $this->payload->toArray()
    );

    return $this->task->refresh();
}
```

---

# Remove SQL Transactions from DeleteTask

Remove the `DatabaseManager` and transaction code.

Use:

```php
// app/Jobs/Tasks/DeleteTask.php
public function handle(): void
{
    $this->task->delete();
}
```

---

## Note

The Jobs remain application-operation boundaries.

We are changing their persistence-specific implementation, not removing them.

---

# Update the Controller Job Calls

The controller is already connected to the Task Jobs. Because the Job `handle()` methods no longer accept a `DatabaseManager`, remove:

```php
// app/Http/Api/Controllers/Tasks/TaskController.php
->handle(app('db'))
```

from the existing Job calls. The Store/Update/Delete calls should finish with:

```php
])->handle();
```

---

Keep the controller's existing:

- Job construction
- `TaskResource`
- generic response classes
- route model binding
- `assignedTo` loading

---

## Final Flow

```text
Route
→ Request
→ NewTask
→ Controller
→ Job
→ MongoDB Task
→ TaskResource
→ Generic Response
```

---

# Expose `tag_ids`

Add:

```php
// app/Http/Api/Resources/TaskResource.php
'tag_ids' => $this->tag_ids ?? [],
```

Keep the rest of the existing resource fields unchanged.

## PNote

`TaskResource` continues to control the public Task representation. The MongoDB migration does not replace the Resource layer.

---

# Update the Task Factory

Add:

```php
// database/factories/TaskFactory.php
'tag_ids' => [],
```

to the Task factory definition.

The factory will now create Task documents using the MongoDB-backed `Task` model.

---

# Seed MongoDB

The original MySQL data is not automatically copied into MongoDB.

Run:

```bash
php artisan db:seed
```

The existing seeder should recreate the sample User and Task data through their now MongoDB-backed models.

Inspect MongoDB and confirm that collections appear for:

```text
users
tasks
```

---

# Authenticate

Use the existing login endpoint and the seeded credentials:

```text
POST /api/v1/auth/login
```

```text
client@example.com
password
```

Confirm that:

1. the User is read from MongoDB
2. the password is accepted
3. Sanctum creates a token
4. `personal_access_tokens` appears in MongoDB
5. the bearer token can access `/api/v1/tasks`

---

# Create a Task

Use the existing Task creation route in Postman

```http
POST /api/v1/tasks
```

```json
{
  "name": "Prepare MongoDB migration",
  "description": "Move the Task vertical slice to MongoDB",
  "status": "in_progress",
  "due_date": "24-08-2026",
  "tag_ids": ["01TAG-MONGODB", "01TAG-API"]
}
```

If you include:

```json
"assigned_to": "..."
```

use the seeded User's Laravel `id` value.

---

# Inspect the Task Document

Open the MongoDB `tasks` collection.

Locate the Task created through the API.

Identify:

```text
_id
name
description
status
due_date
assigned_to
tag_ids
created_at
updated_at
```

Compare this document with the relational Task row from the beginning of the practical.

---

# Consider the Future Tags Collection

You should see:

```json
"tag_ids": [
  "01TAG-MONGODB",
  "01TAG-API"
]
```

There is deliberately no Tag model or collection yet.

Later, these IDs could refer to documents such as:

```json
{
  "_id": "01TAG-MONGODB",
  "name": "MongoDB"
}
```

Do not implement this now. Session 8 will investigate document relationships and embedding vs referencing.

---

# Test the Task Vertical Slice

Using the bearer token, manually exercise:

```text
GET    /api/v1/tasks
POST   /api/v1/tasks
GET    /api/v1/tasks/{task}
PATCH  /api/v1/tasks/{task}
DELETE /api/v1/tasks/{task}
```

For each request check:

- HTTP status
- response JSON
- `tag_ids`
- `assigned_to` where used
- corresponding MongoDB document

---

# Automated Testing Boundary

Do not use the full automated suite as the final MongoDB verification step.

The current repository testing configuration is still built around the original SQL test environment:

```text
phpunit.xml
→ SQLite in memory
```

and the current authentication feature test uses:

```text
RefreshDatabase
```

---

For this Session 5 practical:

```text
Before migration
→ existing automated tests establish the SQL baseline

After migration
→ manual API testing + MongoDB inspection verify the migration
```

Updating the automated test architecture for MongoDB is outside today's scope.

---

# Before and After

## Before

```text
Request
→ Payload
→ Job
→ SQL Eloquent Task
→ MySQL
→ TaskResource
→ Response
```

---

## After

```text
Request
→ Payload
→ Job
→ MongoDB Task
→ tasks collection
→ TaskResource
→ Response
```

Authentication also changes from:

```text
User + PersonalAccessToken
→ MySQL
```

to:

```text
User + PersonalAccessToken
→ MongoDB
```

---

# What Changed?

Persistence-aware code changed:

```text
MongoDB PHP extension
Composer dependency
service providers
database connection
User model
PersonalAccessToken model
LoginController transaction
Task model
NewTask tag_ids
Task request validation
Task Jobs
Task factory
```

---

# What Stayed Stable?

The application architecture remains recognisable:

```text
routes
Requests
Payload
Controller
Jobs
TaskResource
generic Responses
Sanctum authentication flow
public Task endpoints
```

This is one of the reasons we separated responsibilities in Session 4.
