---
marp: true
theme: nmtafe_v2
paginate: true
footer: "MyJamJar API • Session 6 • Guided Attachment Activity"
---

# Guided Activity — Attachment Vertical Slice

## Adapt the MyJamJar Attachment Pattern to the Starter API

The MyJamJar reference supports Attachments for several models.

The classroom scaffold currently has only:

```text
User
Task
```

So today's vertical slice is deliberately limited to:

```text
Task
  ↓
Attachment
```

We preserve the MyJamJar architecture without introducing missing domain models.

---

# Guided Activity Goal

Build toward the MyJamJar pattern:

```text
MongoDB migration
        ↓
Attachment model
        ↓
controlled polymorphic map
        ↓
StoreRequest
        ↓
NewAttachment Payload
        ↓
StoreController
        ↓
CreateNewAttachment Job
        ↓
AttachmentResource
```

But only `Task` is allowed as an `attachmentable` resource.

---

# Important Scope Boundary

The full MyJamJar reference supports:

```text
Project
Task
Milestone
Comment
```

The Starter API does **not** contain all of these.

Do not create:

```text
Project
Milestone
Comment
```

just to make Attachments work.

Instead:

```text
attachmentable_type = task
```

is the only valid relationship type in this activity.

---

# Step 1 — Create the MongoDB Migration

**File:**

```text
database/migrations/
xxxx_xx_xx_xxxxxx_create_attachments_table.php
```

Use the MongoDB Blueprint:

```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class () extends Migration {
    protected $connection = 'mongodb';

    public function up(): void
    {
        Schema::create(
            'attachments',
            function (Blueprint $collection): void {
                $collection->index([
                    'attachmentable_type',
                    'attachmentable_id',
                    'created_at' => -1,
                ]);

                $collection->index([
                    'uploaded_by',
                    'created_at' => -1,
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
```

---

# Why These Indexes?

The first index supports a common query:

```text
Find Attachments
for one Task
ordered by creation time
```

The second supports:

```text
Find Attachments
uploaded by one User
ordered by creation time
```

Key message:

> MongoDB migrations should focus on real query patterns.

---

# Step 2 — Use the MongoDB ULID Concern

The Attachment model should use the same identifier convention introduced in the Session 5 recap:

```text
app/Models/Concerns/HasMongoUlidKey.php
```

The concern standardises:

```text
Laravel ULID
      ↓
MongoDB _id
      ↓
route/model ID
```

Do not repeat identifier configuration inside every model.

---

# Step 3 — Create the Attachment Model

**File:**

```text
app/Models/Attachment.php
```

Start from the MyJamJar model:

```php
namespace App\Models;

use App\Models\Concerns\HasMongoUlidKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use MongoDB\Laravel\Eloquent\Model;

final class Attachment extends Model
{
    use HasFactory;
    use HasMongoUlidKey;
    use SoftDeletes;

    protected $connection = 'mongodb';

    protected $table = 'attachments';
}
```

---

# Step 3 — Attachment Fields

Add:

```php
protected $fillable = [
    'attachmentable_id',
    'attachmentable_type',
    'uploaded_by',
    'disk',
    'path',
    'original_name',
    'mime_type',
    'size',
];
```

Keep the stored document focused on metadata.

The actual file can later live in S3.

---

# Step 3 — API Compatibility

Use:

```php
protected $hidden = ['_id'];

protected $appends = [
    'id',
    'file_path',
];
```

and:

```php
public function getFilePathAttribute(): string
{
    return (string) (
        $this->attributes['path'] ?? ''
    );
}
```

This allows:

```text
MongoDB field → path
API field     → file_path
```

Storage can change without forcing the API contract to change.

---

# Step 3 — Relationships

Keep the polymorphic relationship:

```php
public function attachmentable(): MorphTo
{
    return $this->morphTo();
}
```

Keep the uploader relationship:

```php
public function user(): BelongsTo
{
    return $this->belongsTo(
        User::class,
        'uploaded_by',
        '_id'
    );
}
```

For this class app, `attachmentable()` will resolve only to `Task`.

---

# Step 4 — Create a Task-Only Polymorphic Map

**File:**

```text
app/Support/PolymorphicRelations.php
```

Adapt the MyJamJar helper.

Only expose the Task alias:

```php
<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;

final class PolymorphicRelations
{
    public const ATTACHMENTABLE_TASK = 'task';

    public static function classForAttachmentableAlias(
        string $alias
    ): ?string {
        return match ($alias) {
            self::ATTACHMENTABLE_TASK => Task::class,
            default => null,
        };
    }

    public static function normaliseAttachmentableType(
        mixed $type
    ): ?string {
        if (! is_string($type)) {
            return null;
        }

        return match ($type) {
            self::ATTACHMENTABLE_TASK,
            Task::class => self::ATTACHMENTABLE_TASK,
            default => null,
        };
    }

    public static function findAttachmentable(
        string $alias,
        string $id
    ): ?Model {
        $class = self::classForAttachmentableAlias($alias);

        return null === $class
            ? null
            : $class::query()->find($id);
    }
}
```

---

# Why Keep a Polymorphic Map With One Type?

Today there is only:

```text
task
```

But the structure still teaches an enterprise pattern:

```text
client alias
→ controlled map
→ known model class
```

Later MyJamJar can expand this map without allowing clients to supply arbitrary PHP class names.

---

# Step 5 — Create the Store Request

**File:**

```text
app/Http/Requests/Attachments/StoreRequest.php
```

Use the MyJamJar request shape:

```php
return [
    'file_path' => ['required', 'string'],
    'attachmentable_id' => ['required', 'ulid'],
    'attachmentable_type' => ['required', 'string'],
    'uploaded_by' => ['required', 'ulid'],
    'disk' => ['nullable', 'string', 'max:255'],
    'original_name' => ['nullable', 'string', 'max:255'],
    'mime_type' => ['nullable', 'string', 'max:255'],
    'size' => ['nullable', 'integer', 'min:0'],
];
```

---

# Step 5 — Normalise the Type

Before validation:

```php
protected function prepareForValidation(): void
{
    if ($this->has('attachmentable_type')) {
        $resolved =
            PolymorphicRelations::
                normaliseAttachmentableType(
                    $this->input('attachmentable_type')
                );

        if (null !== $resolved) {
            $this->merge([
                'attachmentable_type' => $resolved
            ]);
        }
    }
}
```

For this application, valid input resolves to:

```text
task
```

---

# Step 5 — Validate the Task Reference

After the basic validation rules, confirm that the Task actually exists.

```php
public function withValidator($validator): void
{
    $validator->after(function ($validator): void {
        $type = $this
            ->string('attachmentable_type')
            ->toString();

        $id = $this
            ->string('attachmentable_id')
            ->toString();

        if (
            null ===
            PolymorphicRelations::
                classForAttachmentableAlias($type)
        ) {
            $validator->errors()->add(
                'attachmentable_type',
                'The selected attachmentable type is invalid.'
            );

            return;
        }

        if (
            null ===
            PolymorphicRelations::
                findAttachmentable($type, $id)
        ) {
            $validator->errors()->add(
                'attachmentable_id',
                'The selected Task is invalid.'
            );
        }
    });
}
```

---

# Step 5 — Validate the Uploader

Still in `withValidator()`:

```php
$uploader = User::query()->find(
    $this->string('uploaded_by')->toString()
);

if (null === $uploader) {
    $validator->errors()->add(
        'uploaded_by',
        'The selected uploaded_by is invalid.'
    );
}
```

This demonstrates application-level referential validation in MongoDB.

---

# Step 6 — Create the Payload

**File:**

```text
app/Http/Payloads/Attachments/NewAttachment.php
```

Use the MyJamJar typed payload:

```php
final readonly class NewAttachment
{
    public function __construct(
        public string $filePath,
        public string $attachmentableId,
        public string $attachmentableType,
        public string $uploadedBy,
        public string $disk = '',
        public string $originalName = '',
        public ?string $mimeType = null,
        public ?int $size = null,
    ) {}
}
```

---

# Step 6 — Map the Payload to MongoDB

```php
public function toArray(): array
{
    return [
        'attachmentable_id'
            => $this->attachmentableId,

        'attachmentable_type'
            => $this->attachmentableType,

        'uploaded_by'
            => $this->uploadedBy,

        'disk'
            => '' !== $this->disk
                ? $this->disk
                : config(
                    'filesystems.default',
                    's3'
                ),

        'path'
            => $this->filePath,

        'original_name'
            => '' !== $this->originalName
                ? $this->originalName
                : basename($this->filePath),

        'mime_type'
            => $this->mimeType,

        'size'
            => $this->size,
    ];
}
```

---

# Step 6 — Build the Payload From the Request

Back in:

```text
StoreRequest.php
```

add:

```php
public function payload(): NewAttachment
{
    return new NewAttachment(
        filePath:
            $this->string('file_path')->toString(),

        attachmentableId:
            $this->string(
                'attachmentable_id'
            )->toString(),

        attachmentableType:
            $this->string(
                'attachmentable_type'
            )->toString(),

        uploadedBy:
            $this->string('uploaded_by')->toString(),

        disk:
            $this->string('disk')->toString(),

        originalName:
            $this->string(
                'original_name'
            )->toString(),

        mimeType:
            $this->string(
                'mime_type'
            )->toString(),

        size:
            $this->filled('size')
                ? $this->integer('size')
                : null,
    );
}
```

---

# Step 7 — Create the Job

**File:**

```text
app/Jobs/Attachments/CreateNewAttachment.php
```

Use the MyJamJar Job:

```php
final class CreateNewAttachment
    implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly NewAttachment $payload
    ) {}

    public function handle(): void
    {
        Attachment::query()->create(
            $this->payload->toArray()
        );
    }
}
```

The Job knows nothing about the HTTP Request.

---

# Step 8 — Create the Store Controller

**File:**

```text
app/Http/Controllers/Attachments/StoreController.php
```

Use the MyJamJar controller pattern:

```php
final readonly class StoreController
{
    public function __construct(
        private Dispatcher $bus
    ) {}

    public function __invoke(
        StoreRequest $request
    ): MessageResponse {
        defer(
            callback: fn() =>
                $this->bus->dispatch(
                    new CreateNewAttachment(
                        payload: $request->payload()
                    )
                ),
            name: 'create-new-attachment',
        );

        return new MessageResponse(
            message: 'We have accepted your request.',
            status: Response::HTTP_ACCEPTED
        );
    }
}
```

---

# Why Return `202 Accepted`?

The request does not promise that the queued work has already completed.

```text
HTTP request
     ↓
validation
     ↓
job accepted
     ↓
202 Accepted
     ↓
queued processing
     ↓
MongoDB write
```

This is a different contract from synchronous:

```text
201 Created
```

Discuss why the status code matters.

---

# Step 9 — Create the Resource

**File:**

```text
app/Http/Resources/AttachmentResource.php
```

Use the deliberately small MyJamJar representation:

```php
return [
    'id' => $this->resource->id,

    'file_path'
        => $this->resource->file_path,

    'uploaded_by'
        => new UserResource(
            resource: $this->whenLoaded('user')
        ),
];
```

The Resource does not expose every MongoDB field.

---

# Step 10 — Add the Task Attachment Route

The scaffold only supports Task Attachments.

Use a route that makes the parent relationship explicit.

Conceptually:

```http
POST /api/v1/tasks/{task}/attachments
```

Keep it inside the existing:

```text
/api/v1
auth:sanctum
```

route structure.

Run:

```bash
php artisan route:list
```

before and after adding the route.

---

# Route and Request Design

The MyJamJar reference Request accepts:

```text
attachmentable_type
attachmentable_id
```

For the classroom Task-only route, there are two useful teaching choices:

### Preserve the MyJamJar payload

Keep both fields in the request so students see the published contract.

### Derive from the URI

Later improve the design by deriving:

```text
attachmentable_type = task
attachmentable_id   = {task}
```

from the nested Task route.

For the guided activity, **start by preserving the MyJamJar request contract**.

---

# Step 11 — Test a Valid Task Attachment

Use real IDs from the class app.

Example request body:

```json
{
  "file_path": "tasks/example/report.pdf",
  "attachmentable_id": "<TASK_ULID>",
  "attachmentable_type": "task",
  "uploaded_by": "<USER_ULID>",
  "original_name": "report.pdf",
  "mime_type": "application/pdf",
  "size": 2048
}
```

Expected initial response:

```text
202 Accepted
```

---

# Step 11 — Confirm the Job Result

After the queued Job runs, inspect:

```text
attachments collection
```

Expected document shape includes:

```text
_id
attachmentable_id
attachmentable_type = task
uploaded_by
disk
path
original_name
mime_type
size
created_at
updated_at
```

Verify the Task ID matches an existing Task document.

---

# Step 12 — Test Invalid Relationships

Test one failure at a time.

### Invalid type

```json
{
  "attachmentable_type": "project"
}
```

Expected:

```text
validation failure
```

because the scaffold only supports `task`.

---

# Step 12 — Invalid Task

Use a valid-looking ULID that does not exist.

Expected:

```text
attachmentable_id validation error
```

This demonstrates:

```text
valid ULID syntax
≠
valid document reference
```

---

# Step 12 — Invalid Uploader

Use a valid-looking User ULID that does not exist.

Expected:

```text
uploaded_by validation error
```

Again:

```text
MongoDB reference integrity
→ application responsibility
```

---

# Optional — Task Relationship on the Model

If useful for the demonstration, add a Task-side relationship that follows the same polymorphic convention.

Conceptually:

```php
public function attachments()
{
    return $this->morphMany(
        Attachment::class,
        'attachmentable'
    );
}
```

Only add this if it matches the current Starter API model conventions.

It is not required merely to create an Attachment document.

---

# Factory — Simplify the MyJamJar Reference

The MyJamJar `AttachmentFactory` supports:

```text
Project
Task
Milestone
Comment
```

For the scaffold, remove unsupported states.

Keep only the Task default:

```php
return [
    'attachmentable_id' => Task::factory(),
    'attachmentable_type'
        => PolymorphicRelations::ATTACHMENTABLE_TASK,
    'uploaded_by' => User::factory(),
    'disk' => 's3',
    'path'
        => 'attachments/'
            . $this->faker->uuid()
            . '.pdf',
    'original_name'
        => $this->faker->word() . '.pdf',
    'mime_type' => 'application/pdf',
    'size'
        => $this->faker->numberBetween(
            1024,
            5242880
        ),
];
```

---

# Seeder — Keep It Task-Only

Do **not** copy the MyJamJar seeder loops for:

```text
Projects
Comments
Milestones
```

The classroom seeder should only seed Attachments against available Tasks.

Conceptually:

```php
Task::query()->each(
    fn(Task $task) =>
        AttachmentFactory::new()
            ->forTask($task)
            ->uploadedBy($users->random())
            ->create()
);
```

This keeps the scaffold aligned with its actual domain.

---

# Storage Boundary

The model supports:

```php
Storage::disk($this->disk)
    ->delete($this->path);
```

Use this to explain the future production architecture:

```text
file
→ S3 / storage disk

metadata
→ MongoDB Attachment document
```

The guided activity does not need to implement the complete upload pipeline.

---

# Guided Activity Architecture

The final Task-only vertical slice is:

```text
POST Task Attachment
        ↓
StoreRequest
        ↓
Task-only PolymorphicRelations
        ↓
NewAttachment
        ↓
StoreController
        ↓
CreateNewAttachment
        ↓
Attachment Model
        ↓
MongoDB
```

and for public output:

```text
Attachment
→ AttachmentResource
→ API response
```

---

# What We Preserved From MyJamJar

We kept the industry structure:

```text
MongoDB migration indexes
HasMongoUlidKey
polymorphic field names
controlled alias map
application reference validation
typed Payload
queued Job
202 Accepted
Resource allow-list
storage metadata
```

---

# What We Deliberately Removed

Because the scaffold does not contain these models:

```text
Project
Milestone
Comment
```

we do not support them in:

```text
PolymorphicRelations
AttachmentFactory
AttachmentSeeder
validation
```

This is deliberate scope control, not a missing feature.

---

# Teaching Point

> **Enterprise patterns should be adapted to the domain that actually exists.**

Do not copy infrastructure for resources the application does not have.

The class app learns the MyJamJar pattern through:

```text
Task → Attachments
```

Students later apply the same reasoning independently to Projects.

---

# Guided Activity Review

Ask students:

1. Why is `task` the only valid attachment type?
2. Why does the migration use compound indexes?
3. Why is ULID syntax validation not enough?
4. Why is reference validation performed in the Request?
5. Why does the controller return `202`?
6. Why does the Resource expose fewer fields than MongoDB stores?
7. Where would S3 fit later?

---

# Transition to the Project Practical

The lecturer has demonstrated:

```text
existing domain
        ↓
relationship design
        ↓
MongoDB structure
        ↓
REST contract
        ↓
enterprise vertical slice
```

Students now apply the process independently:

```text
Published MyJamJar Projects
        ↓
Project ERD
        ↓
Project API contract
        ↓
Project vertical slice
        ↓
Postman comparison
```

Do not provide the Project implementation.
