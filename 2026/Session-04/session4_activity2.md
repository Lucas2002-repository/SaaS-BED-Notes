# Session 4 Activity 2: Refactoring the Task API

This activity starts from the existing Session 3 Task feature and progressively refactors it toward the Session 4 Payloads, Jobs and generic Responses structure.

Keep the scope tight:

- migrate only the Task feature
- preserve the existing routes and API behaviour wherever practical
- do not introduce Services, Repositories or third-party DTO packages

Use the supplied MyJamJar examples as the pattern reference, but adapt only the fields that actually exist in this codebase. In this repo, Tasks do not currently use `project_id` or `workspace_id`, so those should not be added during this activity.

## Test

Reconfirm that the current Task feature already works before refactoring it.

**Open**

- `routes/api/tasks.php`
- `app/Http/Api/Requests/Tasks/StoreTaskRequest.php`
- `app/Http/Api/Requests/Tasks/UpdateTaskRequest.php`
- `app/Http/Api/Controllers/Tasks/TaskController.php`
- `app/Models/Task.php`
- `app/Http/Api/Resources/TaskResource.php`

**Point Out**

Current flow:

```text
index/show/store/update/destroy
    ↓
Request validation
    ↓
TaskController
    ↓
Task model operations
    ↓
TaskResource or Response
    ↓
JSON
```

The feature is not broken. The purpose of this migration is to separate responsibilities more clearly.

**Consider...**

Which responsibilities are currently sitting inside `TaskController` that could be moved into clearer boundaries?

## 2: Create the Task payload boundary

**Purpose**

Introduce typed application data so the rest of the application no longer depends directly on raw request arrays.

**Create**

- `app/Http/Payloads/Tasks/NewTask.php`

**Code**

```php
<?php

declare(strict_types=1);

namespace App\Http\Payloads\Tasks;

final readonly class NewTask
{
    public function __construct(
        public string $name,
        public ?string $description,
        public string $status,
        public ?string $dueDate,
        public ?string $assignedTo,
    ) {}

    /** @return array{name:string,description:?string,status:string,due_date:?string,assigned_to:?string} */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->dueDate,
            'assigned_to' => $this->assignedTo,
        ];
    }
}
```

**Point Out**

This follows the supplied `NewTask` example closely:

- `readonly`
- promoted constructor properties
- `toArray()` for model persistence

It is adapted only to match the current Task contract in this repo.

**Consider...**

What does the application now know once a `NewTask` object exists that it did not know from a raw request array?

## 3: Convert the Requests into payloads

**Purpose**

Keep validation in the Request layer, then expose trusted typed data through `payload()` methods.

**Open**

- `app/Http/Api/Requests/Tasks/StoreTaskRequest.php`
- `app/Http/Api/Requests/Tasks/UpdateTaskRequest.php`

**Code**

In `StoreTaskRequest`, add:

```php
use App\Http\Payloads\Tasks\NewTask;
```

```php
public function payload(): NewTask
{
    return new NewTask(
        name: (string) $this->validated('name'),
        description: $this->validated('description'),
        status: (string) $this->validated('status'),
        dueDate: $this->validated('due_date'),
        assignedTo: $this->validated('assigned_to'),
    );
}
```

In `UpdateTaskRequest`, add the same import and create a payload method that fills missing values from the current model so partial updates still work:

```php
use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;
```

```php
public function payload(Task $task): NewTask
{
    return new NewTask(
        name: (string) ($this->validated('name') ?? $task->name),
        description: $this->validated('description') ?? $task->description,
        status: (string) ($this->validated('status') ?? $task->status),
        dueDate: $this->validated('due_date') ?? $task->due_date?->format('Y-m-d'),
        assignedTo: $this->validated('assigned_to') ?? $task->assigned_to,
    );
}
```

**Point Out**

The Request layer still owns validation.

The Payload layer does not validate HTTP input. It carries trusted, typed application data.

Boundary after this step:

```text
HTTP Request
    ↓
StoreTaskRequest / UpdateTaskRequest
    ↓
NewTask
```

**Consider...**

At what point does raw HTTP input become trusted application data?

## 4: Introduce the Task Jobs

**Purpose**

Move application operations out of the controller and into dedicated classes under `app/Jobs/Tasks`.

**Create**

- `app/Jobs/Tasks/CreateNewTask.php`
- `app/Jobs/Tasks/UpdateTask.php`
- `app/Jobs/Tasks/DeleteTask.php`

**Code**

`CreateNewTask.php`

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class CreateNewTask
{
    public function __construct(public NewTask $payload) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): Task
    {
        return $database->transaction(
            callback: fn(): Task => Task::query()->create($this->payload->toArray()),
            attempts: 3,
        );
    }
}
```

`UpdateTask.php`

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Http\Payloads\Tasks\NewTask;
use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class UpdateTask
{
    public function __construct(
        public Task $task,
        public NewTask $payload,
    ) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): Task
    {
        return $database->transaction(
            callback: function (): Task {
                $this->task->update($this->payload->toArray());

                return $this->task->refresh();
            },
            attempts: 3,
        );
    }
}
```

`DeleteTask.php`

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Tasks;

use App\Models\Task;
use Illuminate\Database\DatabaseManager;
use Throwable;

final readonly class DeleteTask
{
    public function __construct(public Task $task) {}

    /** @throws Throwable */
    public function handle(DatabaseManager $database): void
    {
        $database->transaction(
            callback: fn() => $this->task->delete(),
            attempts: 3,
        );
    }
}
```

**Point Out**

These Jobs preserve the core MyJamJar pattern:

- controller coordinates
- payload carries trusted data
- job performs the application operation
- model persists the data

They are adapted to the current Task implementation by removing `workspace_id`.

## 5: Introduce generic Responses

**Purpose**

Standardise common response shapes without removing `TaskResource`.

**Create**

- `app/Http/Responses/MessageResponse.php`
- `app/Http/Responses/ModelResponse.php`
- `app/Http/Responses/PaginatedCollectionResponse.php`

**Code**

Use the supplied classes exactly.

`MessageResponse.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class MessageResponse implements Responsable
{
    public function __construct(private string $message, private int $status = Response::HTTP_OK) {}

    public function toResponse($request): Response
    {
        return new JsonResponse(data: ['message' => $this->message], status: $this->status, headers: []);
    }
}
```

`ModelResponse.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

final readonly class ModelResponse implements Responsable
{
    public function __construct(private JsonResource $data, private int $status = Response::HTTP_OK) {}

    public function toResponse($request): Response
    {
        return new JsonResponse(data: $this->data, status: $this->status, headers: []);
    }
}
```

`PaginatedCollectionResponse.php`

```php
<?php

declare(strict_types=1);

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final readonly class PaginatedCollectionResponse implements Responsable
{
    public function __construct(private AnonymousResourceCollection $data, private int $status = Response::HTTP_OK) {}

    public function toResponse($request): Response
    {
        return new JsonResponse(data: $this->data, status: $this->status, headers: []);
    }
}
```

**Point Out**

`TaskResource` still controls what Task data is public.

The Response classes control how that result is wrapped as an HTTP response.

## 6: Refactor the controller to the new structure

**Purpose**

Update all Task controller methods so the controller becomes a coordinator instead of the place where persistence work happens.

**Open**

- `app/Http/Api/Controllers/Tasks/TaskController.php`

**Code**

Update the imports:

```php
use App\Http\Api\Requests\Tasks\StoreTaskRequest;
use App\Http\Api\Requests\Tasks\UpdateTaskRequest;
use App\Http\Api\Resources\TaskResource;
use App\Http\Responses\MessageResponse;
use App\Http\Responses\ModelResponse;
use App\Http\Responses\PaginatedCollectionResponse;
use App\Jobs\Tasks\CreateNewTask;
use App\Jobs\Tasks\DeleteTask;
use App\Jobs\Tasks\UpdateTask;
use App\Models\Task;
use Illuminate\Http\Response;
```

Then refactor the controller methods:

```php
public function index(): PaginatedCollectionResponse
{
    $tasks = Task::query()
        ->with('assignedTo')
        ->latest()
        ->paginate(25);

    return new PaginatedCollectionResponse(
        TaskResource::collection($tasks),
    );
}
```

```php
public function store(StoreTaskRequest $request): ModelResponse
{
    $task = app(CreateNewTask::class, [
        'payload' => $request->payload(),
    ])->handle(app('db'));

    return new ModelResponse(
        data: new TaskResource($task->load('assignedTo')),
        status: Response::HTTP_CREATED,
    );
}
```

```php
public function show(Task $task): ModelResponse
{
    return new ModelResponse(
        new TaskResource($task->load('assignedTo')),
    );
}
```

```php
public function update(UpdateTaskRequest $request, Task $task): ModelResponse
{
    $task = app(UpdateTask::class, [
        'task' => $task,
        'payload' => $request->payload($task),
    ])->handle(app('db'));

    return new ModelResponse(
        new TaskResource($task->load('assignedTo')),
    );
}
```

```php
public function destroy(Task $task): MessageResponse
{
    app(DeleteTask::class, [
        'task' => $task,
    ])->handle(app('db'));

    return new MessageResponse(
        message: 'Task deleted successfully.',
    );
}
```

**Point Out**

The finished controller now coordinates:

```text
Request
    ↓
Payload
    ↓
Job
    ↓
Model
    ↓
Resource
    ↓
Response
```

The main remaining direct model work in the controller is the `index()` query and route model binding for `show`, `update` and `destroy`.

## 7: Preserve TaskResource

**Purpose**

Make the Resource/Response split explicit.

**Open**

- `app/Http/Api/Resources/TaskResource.php`

**Point Out**

Do not remove `TaskResource`.

The flow is now:

```text
Task model
    ↓
TaskResource
    ↓
ModelResponse or PaginatedCollectionResponse
    ↓
JSON
```

The Resource chooses fields like:

- `id`
- `name`
- `description`
- `status`
- formatted `due_date`
- nested `assigned_to`

The Response classes do not replace that work.

**Consider...**

What is the difference between `TaskResource` and `ModelResponse`?

## 8: Compare the folder structure

**Purpose**

Show the students the architectural shape they have created.

**Point Out**

Relevant structure after the migration:

```text
app/
├── Http/
│   ├── Api/
│   │   ├── Controllers/
│   │   ├── Requests/
│   │   └── Resources/
│   ├── Payloads/
│   │   └── Tasks/
│   │       └── NewTask.php
│   └── Responses/
│       ├── MessageResponse.php
│       ├── ModelResponse.php
│       └── PaginatedCollectionResponse.php
└── Jobs/
    └── Tasks/
        ├── CreateNewTask.php
        ├── UpdateTask.php
        └── DeleteTask.php
```

This repo already uses `app/Http/Api/...` for its current HTTP layer, so preserve that structure. The new `Payloads`, `Responses` and `Jobs` folders extend the current app instead of replacing it.

## 9: Test after the refactor

**Purpose**

Confirm the endpoint behaviour still makes sense from an API consumer perspective even though the internal code structure has changed.

**Run**

```bash
php artisan test
```

Then repeat the key API operations used before the migration:

- `GET /api/v1/tasks`
- `GET /api/v1/tasks/{task}`
- `POST /api/v1/tasks`
- `PUT/PATCH /api/v1/tasks/{task}`
- `DELETE /api/v1/tasks/{task}`

**Expected Result**

Check:

- validation still occurs in the Request layer
- creation and update now flow through `NewTask`
- create, update and delete operations now flow through Jobs
- public JSON still comes through `TaskResource`
- controller responses now use generic Response classes

Note: this repo does not currently include focused Task tests, so use the existing test suite plus manual API checks during the live coding session.

## 10: Before and after comparison

**Purpose**

Finish with a concise architectural summary.

**Point Out**

Before:

```text
Request
    ↓
TaskController
    ↓
Task model operations
    ↓
TaskResource
    ↓
JSON
```

After:

```text
StoreTaskRequest / UpdateTaskRequest
    ↓
NewTask
    ↓
TaskController
    ↓
CreateNewTask / UpdateTask / DeleteTask
    ↓
Task
    ↓
TaskResource
    ↓
ModelResponse / PaginatedCollectionResponse / MessageResponse
    ↓
JSON
```

Lecture principle:

```text
Validate it
    → Request
Type it
    → Payload
Process it
    → Job
Store it
    → Model
Transform it
    → Resource
Respond consistently
    → Response
```

## Consider...

1. Where is HTTP validation performed now?
2. When does raw request data become trusted application data?
3. What does `NewTask` communicate that an untyped array does not?
4. What responsibility moved from the controller into the Jobs?
5. What remains the responsibility of `TaskResource`?
6. What is the difference between `TaskResource` and `ModelResponse`?
7. Why can the endpoint stay while the internal architecture changes?
8. If another Developer or Code Agent had to change Task creation or update later, which files would it now inspect first?

## Summary

After the refactor, a coding agent can focus on a smaller, more predictable set of files:

- `StoreTaskRequest`
- `UpdateTaskRequest`
- `NewTask`
- `CreateNewTask`
- `UpdateTask`
- `DeleteTask`
- `TaskResource`
- relevant tests

That does not automatically reduce token usage, but it does focus our work to the smallest possible change
