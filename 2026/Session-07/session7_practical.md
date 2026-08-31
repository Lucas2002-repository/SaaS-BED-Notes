---
marp: true
theme: nmtafe_v2
paginate: true
footer: "MyJamJar API • Session 7 • AT2 Architecture Cheatsheet"
---

# Session 7 Practical

## Add Enterprise Infrastructure to the Starter API

In this guided practical you will extend the current example application with:

```text
1. Attachment storage service
2. Queued Task creation
3. Task-created webhook
4. Practical tests
```

Keep the changes small and traceable.

---

# Practical Goal

Start from the current application:

```text
Controller
→ Request
→ Payload
→ Job
→ Model
→ MongoDB
```

Extend it toward:

```text
Controller
→ Job
   ├→ Model → MongoDB
   ├→ Service → Storage
   └→ Queued Webhook → External API
```

The purpose is to understand the architecture, not to add unnecessary complexity.

---

# Before You Start

Confirm the application starts and the existing API works.

Useful checks:

```bash
php artisan route:list
php artisan migrate:status
php artisan test
```

Make sure MongoDB is available.

Create a Git checkpoint before changing the application.

```bash
git add .
git commit -m "Session 7 starting point"
```

---

# Part 1 — Extract Storage Logic Into a Service

## Why?

Deleting an Attachment may require two separate operations:

```text
delete stored file
+
delete MongoDB document
```

External storage is an infrastructure concern.

We will move that responsibility into a dedicated Service.

---

# Create the Service

```text
app/Services/AttachmentStorageService.php
```

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attachment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class AttachmentStorageService
{
    public function delete(Attachment $attachment): bool
    {
        if ('' === $attachment->path) {
            return true;
        }
```

---

# Complete the Service

```php
        try {
            return Storage::disk($attachment->disk)
                ->delete($attachment->path);
        } catch (Throwable $exception) {
            Log::warning(
                'Unable to delete attachment object.',
                [
                    'attachment_id' => $attachment->id,
                    'disk' => $attachment->disk,
                    'path' => $attachment->path,
                    'error' => $exception->getMessage(),
                ],
            );

            return false;
        }
    }
}
```

---

# Use the Service From the Delete Job

```text
app/Jobs/Attachments/DeleteAttachment.php
```

Keep the Job responsible for the **delete operation**. Inject the service into `handle()`:

```php
use App\Services\AttachmentStorageService;

public function handle(
    AttachmentStorageService $storage,
): void {
    $storage->delete($this->attachment);

    $this->attachment->delete();
}
```

Laravel resolves the Service from the container.

---

# Observe the Responsibility Change

Before:

```text
Model
→ storage operation
```

After:

```text
DeleteAttachment Job
       ↓
AttachmentStorageService
       ↓
Laravel Storage
```

The Model continues to represent Attachment data.

The Service owns the external storage concern.

---

# Test the Storage Service

```text
tests/Unit/Services/AttachmentStorageServiceTest.php
```

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Attachment;
use App\Services\AttachmentStorageService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AttachmentStorageServiceTest extends TestCase
{
    public function test_it_deletes_an_attachment_object(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put(
            'attachments/report.pdf',
            'test file',
        );
```

---

# Complete the Storage Test

```php
        $attachment = new Attachment([
            'disk' => 'local',
            'path' => 'attachments/report.pdf',
        ]);

        $service = new AttachmentStorageService();

        self::assertTrue(
            $service->delete($attachment)
        );

        Storage::disk('local')->assertMissing(
            'attachments/report.pdf'
        );
    }
}
```

This verifies the service without touching real storage.

---

# Run the Service Test

```bash
php artisan test \
  tests/Unit/Services/AttachmentStorageServiceTest.php
```

Expected:

```text
PASS
```

If it fails, check:

```text
disk
path
Storage::fake()
service namespace
```

Commit the working change.

---

# Part 2 — Queue Task Creation

The current `CreateNewTask` is a Job class but it is not yet a **queued Job**.

We will make the distinction explicit.

---

# Update CreateNewTask

```text
app/Jobs/Tasks/CreateNewTask.php
```

```php
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
```

```php
final class CreateNewTask implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly NewTask $payload,
    ) {}

    public function handle(): Task
    {
        return Task::query()->create(
            $this->payload->toArray()
        );
    }
}
```

The Job can now be placed on a queue.

---

# Queue Configuration

The application already contains a MongoDB queue migration.

Confirm `.env` contains:

```text
QUEUE_CONNECTION=database
DB_QUEUE_CONNECTION=mongodb
```

Then run:

```bash
php artisan migrate
```

Relevant MongoDB collections include:

```text
jobs
job_batches
failed_jobs
```

---

# Test the Queue Behaviour

Stop any queue worker first.

Then create a Task through Postman.

Expected API result:

```text
202 Accepted
```

Now inspect:

```text
jobs collection
tasks collection
```

Question:

> Has the Task actually been created yet?

---

# Start the Worker

Run:

```bash
php artisan queue:work
```

Observe:

```text
queued CreateNewTask
        ↓
worker receives Job
        ↓
Task::create()
        ↓
MongoDB tasks collection
```

Check the Task now exists.

Stop the worker with:

```text
Ctrl+C
```

---

# Queue Management

Try:

```bash
php artisan queue:work --tries=3
php artisan queue:failed
```

If a Job fails:

```bash
php artisan queue:retry all
```

To remove failed Job records:

```bash
php artisan queue:flush
```

Do not use `queue:flush` unless you intend to remove all failed Job records.

---

# Test the Queue Contract

The important observation is:

```text
202 Accepted
≠
work completed
```

The HTTP response means:

> The application accepted the operation for processing.

This is a useful pattern for slow, external or retryable work.

---

# Part 3 — Add a Task-Created Webhook

A webhook will notify another system when Task creation succeeds.

Architecture:

```text
CreateNewTask
      ↓
Task created
      ↓
SendTaskCreatedWebhook
      ↓
Queue
      ↓
External HTTP endpoint
```

Webhook delivery should not block the original API request.

---

# Add Webhook Configuration

```text
config/services.php
```

```php
'task_webhook' => [
    'url' => env('TASK_CREATED_WEBHOOK_URL'),
    'secret' => env(
        'TASK_CREATED_WEBHOOK_SECRET'
    ),
],
```

Then add to `.env`:

```text
TASK_CREATED_WEBHOOK_URL=
TASK_CREATED_WEBHOOK_SECRET=change-me
```

---

# Create the Webhook Job

```text
app/Jobs/Webhooks/SendTaskCreatedWebhook.php
```

```php
<?php

declare(strict_types=1);

namespace App\Jobs\Webhooks;

use App\Models\Task;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
```

---

```php
final class SendTaskCreatedWebhook implements ShouldQueue
{
use Queueable;
    public int $tries = 3;
    public array $backoff = [10, 30, 60];
    public function __construct(
        public readonly Task $task,
    ) {}
}
```

---

# Build and Sign the Payload

```php
public function handle(): void
{
    $url = config('services.task_webhook.url');
    $secret = config('services.task_webhook.secret');
    if (! is_string($url) || '' === $url) {
        return;
    }
    $payload = [
        'event_id' => (string) Str::ulid(),
        'event' => 'task.created',
        'data' => [
            'id' => $this->task->id,
            'name' => $this->task->name,
        ],
    ];
    $json = json_encode(
        $payload,
        JSON_THROW_ON_ERROR
    );
```

---

# Send the Signed Webhook

```php
    $signature = hash_hmac(
        'sha256',
        $json,
        (string) $secret,
    );

    Http::withHeaders([
        'X-Webhook-Signature' => $signature,
    ])
        ->withBody($json, 'application/json')
        ->post($url)
        ->throw();
}
```

```text
remote 500 / 400 response
        ↓
Job fails
        ↓
queue retry policy can apply
```

---

# Dispatch the Webhook

```php
// app/Jobs/Tasks/CreateNewTask.php
use App\Jobs\Webhooks\SendTaskCreatedWebhook;
```

```php
public function handle(): Task
{
    $task = Task::query()->create(
        $this->payload->toArray()
    );

    SendTaskCreatedWebhook::dispatch($task);

    return $task;
}
```

Now one successful operation can trigger another asynchronous integration.

---

# Integration Flow

```text
POST Task
   ↓
202 Accepted
   ↓
CreateNewTask queued
   ↓
Task stored in MongoDB
   ↓
SendTaskCreatedWebhook queued
   ↓
external endpoint called
```

This is an example of eventual consistency.

The external system may receive the update slightly later.

---

# Test the Webhook Without an External System

Do not make automated tests depend on the internet.

Create:

```text
tests/Unit/Jobs/SendTaskCreatedWebhookTest.php
```

Use:

```php
Http::fake();
```

This lets Laravel capture the outbound request.

---

# Webhook Test — Setup

```text
tests/Unit/Jobs/SendTaskCreatedWebhookTest.php
```

```php
<?php

declare(strict_types=1);
namespace Tests\Unit\Jobs;
use App\Jobs\Webhooks\SendTaskCreatedWebhook;
use App\Models\Task;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class SendTaskCreatedWebhookTest extends TestCase
{
    public function test_it_sends_a_signed_webhook(): void
    {
        config([
            'services.task_webhook.url'
                => 'https://example.test/webhook',
            'services.task_webhook.secret'
                => 'test-secret',
        ]);

        Http::fake();
```

---

# Webhook Test — Create the Task

```php
        $task = new Task([
            'name' => 'Webhook Task',
        ]);

        $task->setAttribute(
            '_id',
            '01J00000000000000000000000'
        );

        (new SendTaskCreatedWebhook($task))
            ->handle();
```

The outbound request is captured by `Http::fake()`.

---

# Complete the Webhook Assertion

```php
        Http::assertSent(
            function (Request $request): bool {
                $body = $request->data();

                return
                    $request->url()
                        === 'https://example.test/webhook'
                    && $body['event']
                        === 'task.created'
                    && $body['data']['name']
                        === 'Webhook Task'
                    && $request->hasHeader(
                        'X-Webhook-Signature'
                    );
            }
        );
    }
}
```

This tests the integration boundary without contacting another service.

---

# Run the Webhook Test

```bash
php artisan test \
  tests/Unit/Jobs/SendTaskCreatedWebhookTest.php
```

Expected:

```text
PASS
```

Then run both new tests:

```bash
php artisan test tests/Unit
```

---

# Optional Live Webhook Check — Laragon + ngrok

Use ngrok to expose a local Laravel webhook receiver through a temporary public HTTPS URL.

Architecture:

```text
Starter API
   ↓ POST webhook
ngrok public HTTPS URL
   ↓ tunnel
Laragon
   ↓
local Laravel webhook receiver
```

This lets you test both sides of the integration without deploying another application.

---

# Create a Local Webhook Receiver

In the receiving Laravel application, add a development-only route.

```php
// routes/api.php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post(
    '/webhooks/task-created',
    function (Request $request) {
        Log::info(
            'Task webhook received',
            $request->all(),
        );

        return response()->json([
            'received' => true,
        ]);
    }
);
```

Use this only for local development/testing.

---

# Confirm the Laragon URL

Start the receiving Laravel application in Laragon.

Confirm the route works locally first.

Typical Laragon examples:

```text
http://example-app.test
http://localhost
```

Check the route:

```bash
php artisan route:list
```

Make sure the webhook route is reachable before introducing ngrok.

---

# Start ngrok

Install/sign in to ngrok first if required.

From a terminal, expose the same local HTTP endpoint Laragon is serving.

If using Laragon on port 80:

```bash
ngrok http 80
```

If your app is running on another port, expose that port instead.

ngrok will provide a temporary address similar to:

```text
https://example.ngrok-free.app
```

---

# Configure the Sending Application

Set the webhook URL in the Starter API `.env`.

Example:

```text
TASK_CREATED_WEBHOOK_URL=https://example.ngrok-free.app/api/webhooks/task-created
TASK_CREATED_WEBHOOK_SECRET=change-me
```

Then clear cached configuration:

```bash
php artisan config:clear
```

Start the queue worker:

```bash
php artisan queue:work
```

---

# Run the Live Test

Create a Task through Postman.

Observe:

```text
POST /api/v1/tasks
      ↓
202 Accepted
      ↓
CreateNewTask
      ↓
Task stored in MongoDB
      ↓
SendTaskCreatedWebhook
      ↓
ngrok
      ↓
local Laravel receiver
```

Then inspect:

```text
ngrok request inspector
storage/logs/laravel.log
```

You should see the `task.created` webhook payload arrive.

---

# Live Failure / Retry Test

Now stop either:

```text
ngrok
or
the receiving Laravel application
```

Create another Task. Observe the webhook Job fail/retry.

```bash
php artisan queue:failed
```

Restore the receiver and retry:

```bash
php artisan queue:retry all
```

This demonstrates a real synchronisation failure between two systems.

---

# ngrok Testing Notes

Use ngrok only for the live integration check.

Keep automated tests based on:

```php
Http::fake();
```

because automated tests should not depend on:

```text
internet access
ngrok availability
temporary public URLs
external services
```

Do not send real secrets, personal information or production data through a temporary public tunnel.

---

# Failure Experiment

Change the webhook URL temporarily to an invalid/unavailable endpoint.

Run:

```bash
php artisan queue:work --tries=3
```

```text
SendTaskCreatedWebhook
        ↓
HTTP failure
        ↓
retry
        ↓
failed_jobs if retries exhausted
```

```bash
php artisan queue:failed
```

Restore the valid configuration afterwards.

---

# What Have We Built?

```text
Attachment delete
    ↓
AttachmentStorageService
    ↓
Storage

Task create
    ↓
Queued CreateNewTask
    ↓
MongoDB
    ↓
Queued SendTaskCreatedWebhook
    ↓
External API
```

Three different responsibilities are now separated.

---

# Framework Context

The Laravel implementation is specific.

The architecture is not.

```text
Laravel Service
→ application/infrastructure service

ShouldQueue Job
→ background worker task

Laravel HTTP Client
→ outbound API client

Webhook
→ event notification between systems
```

FastAPI, Spring and ASP.NET solve the same architectural problems using different libraries and terminology.

---

# Review

## Build

```text
AttachmentStorageService
queued CreateNewTask
SendTaskCreatedWebhook
```

## Observe

```text
jobs collection
Task creation timing
outbound webhook request
```

---

## Test

```text
Storage::fake()
Http::fake()
queue worker
failed jobs
```

---

# Explain

Be able to answer:

```text
Why extract storage into a Service?
Why queue the operation?
Why return 202?
Why queue webhook delivery?
Why sign a webhook?
Why can a webhook be delivered more than once?
```

--

## Improve

Identify one place where:

```text
cache
queue
service
webhook
```

Could improve the wider MyJamJar architecture. Do not implement it yet.

---

# Git Checkpoint

Run:

```bash
git status
php artisan test
```

If working:

```bash
git add .
git commit -m \
"Add Session 7 service queue and webhook examples"
```

Keep this implementation separate from your Assessment 2 work.

---

# Transition to Assessment Workshop

Stop coding.

In groups, return to your:

```text
Project ERD
Class/domain UML
Sequence UML
Component UML
```

Use today's implementation to improve the diagrams.

Consider where these belong:

```text
Services
Queue workers
External APIs
Webhooks
Storage
Cache
```

Only include components you can justify.

---

# Lecturer Catch-Up

Bring your current diagrams and scaffold notes.

Be ready to explain:

```text
Project database design
confirmed relationships
assumptions
where Project fits in the scaffold
architecture decisions
next AT2 action
```

The discussion should lead to an improved design, not simply approval.
