# Laravel Task API v1 — Artisan Commands

This scaffold creates a versioned API endpoint at:

```text
/api/v1/tasks
```

## 1. Create or enter the Laravel API project

For a new Laravel project:

```bash
laravel new task-api
composer create-project laravel/laravel task-api
cd task-api
php artisan install:api
```

For an existing Laravel project, run only:

```bash
php artisan install:api
```

## 2. Generate the model and associated files

The following command creates the model, migration, factory, seeder, resource controller, and form requests:

```bash
php artisan make:model Task --migration --factory --seed --controller --resource --requests
php artisan make:model Task --all --api --requests --factory --seed
```

Laravel may generate the controller at `app/Http/Controllers/TaskController.php`. Move it into the versioned namespace:

```bash
mkdir -p app/Http/Controllers/Api/V1
mv app/Http/Controllers/TaskController.php app/Http/Controllers/Api/V1/TaskController.php
```

Update its namespace to:

```php
namespace App\Http\Controllers\Api\V1;
```

## 3. Generate the API response resource

```bash
php artisan make:resource Api/V1/TaskResource
```

The generated file will be:

```text
app/Http/Resources/Api/V1/TaskResource.php
```

## 4. Generate explicitly named request classes

The `--requests` option normally creates store and update requests. These commands can be used when generating each file separately:

```bash
php artisan make:request Api/V1/Tasks/StoreTaskRequest
php artisan make:request Api/V1/Tasks/UpdateTaskRequest
```

## 5. Generate individual files instead of the combined command

```bash
php artisan make:model Task
php artisan make:migration create_tasks_table
php artisan make:factory TaskFactory --model=Task
php artisan make:seeder TaskSeeder
php artisan make:controller Api/V1/TaskController --api --model=Task
php artisan make:request Api/V1/Tasks/StoreTaskRequest
php artisan make:request Api/V1/Tasks/UpdateTaskRequest
php artisan make:resource Api/V1/TaskResource
```

## 6. Add the versioned API route

Add this to `routes/api.php`:

```php
use App\Http\Controllers\Api\V1\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::apiResource('tasks', TaskController::class);
});
```

List the generated routes:

```bash
php artisan route:list --path=api/v1/tasks
```

## 7. Run the migration and seeder

```bash
php artisan migrate
php artisan db:seed --class=TaskSeeder
```

Or rebuild the development database and seed it:

```bash
php artisan migrate:fresh --seed
```

## 8. Start the API

```bash
php artisan serve
```

The default URL is:

```text
http://127.0.0.1:8000/api/v1/tasks
```

## 9. Test the endpoints with cURL

### List tasks

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks' \
  --header 'Accept: application/json'
```

### Create a task

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks' \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "project_id": "01ky99dtfr907x9ycp98kh12j1",
    "assigned_to": null,
    "name": "Complete API resource",
    "description": "Create and test the Laravel task resource.",
    "status": "todo",
    "due_date": "2026-08-02"
  }'
```

### Show one task

```bash
curl --location 'http://127.0.0.1:8000/api/v1/tasks/TASK_ULID' \
  --header 'Accept: application/json'
```

### Update one task

```bash
curl --request PATCH \
  --location 'http://127.0.0.1:8000/api/v1/tasks/TASK_ULID' \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "status": "done"
  }'
```

### Delete one task

```bash
curl --request DELETE \
  --location 'http://127.0.0.1:8000/api/v1/tasks/TASK_ULID' \
  --header 'Accept: application/json'
```

## Expected response shape

```json
{
  "data": {
    "id": "01ky99dtfvskq1a4767xzwbtss",
    "name": "Eligendi omnis ea.",
    "description": "Voluptatem temporibus aliquam dolores dolores unde error.",
    "status": "done",
    "due_date": "2026-08-02",
    "created": {
      "human": "4 days ago",
      "string": "2026-07-24T05:26:31+00:00"
    },
    "project": {
      "id": "01ky99dtfr907x9ycp98kh12j1"
    },
    "assigned_to": null
  }
}
```
