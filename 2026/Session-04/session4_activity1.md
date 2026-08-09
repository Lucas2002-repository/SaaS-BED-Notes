# Refactoring Session 3

The goal is to consolidate the existing request-to-response flow before the next lecture introduces a different way to organise larger API features.

> https://github.com/NM-TAFE/saas-bed-task-api.git

---

**Terminal Command**

```bash
git log --oneline f96a399ec491dca1912f009c836ceb65074c4d81..18abe4ff7ef8af74fe214fec78217962e63d88c3
git diff --stat f96a399ec491dca1912f009c836ceb65074c4d81..18abe4ff7ef8af74fe214fec78217962e63d88c3
git diff --name-only f96a399ec491dca1912f009c836ceb65074c4d81..18abe4ff7ef8af74fe214fec78217962e63d88c3
```

**Review**

- The range is small enough to inspect live.
- The sequence shows Task API cleanup and namespacing work alongside authentication work needed to protect the API.
- The main Task files touched in the range are the routes, controller, request classes, resource, and model.

---

**Consider...**

- Which changed files look like HTTP entry points, and which look like data or response layers?

**Terminal Command**

```bash
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:routes/api.php
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:routes/api/tasks.php
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:routes/api/auth.php
```

---

**Open**

- `routes/api.php`
- `routes/api/tasks.php`
- `routes/api/auth.php`

**Review**

- API routes are versioned under `/api/v1`.
- Task routes are grouped separately from auth routes.
- The Task routes sit inside `auth:sanctum`, so the request must be authenticated before reaching the controller.
- `Route::apiResource('/', TaskController::class)` defines the standard REST endpoints for tasks.

---

**Consider...**

- Where does a task request enter the application, and what must happen before it reaches the task controller?

## Trace the Task Controller

**Terminal Command**

```bash
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:app/Http/Api/Controllers/Tasks/TaskController.php
```

---

**Open**

- `app/Http/Api/Controllers/Tasks/TaskController.php`

**Review**

- `index()` fetches tasks, loads `assignedTo`, orders latest first, and paginates.
- `store()` and `update()` both rely on validated request data.
- `show()` and `destroy()` use route model binding through the `Task $task` parameter.
- The controller coordinates the operation, but the JSON shape is delegated to `TaskResource`.

---

**Consider...**

- Which responsibilities are handled here, and which are delegated somewhere else?

## Where is Task Input Is Validated

**Terminal Command**

```bash
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:app/Http/Api/Requests/Tasks/StoreTaskRequest.php
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:app/Http/Api/Requests/Tasks/UpdateTaskRequest.php
```

---

**Open**

- `app/Http/Api/Requests/Tasks/StoreTaskRequest.php`
- `app/Http/Api/Requests/Tasks/UpdateTaskRequest.php`

**Review**

- Validation is separated from the controller.
- `store` and `update` have different rule sets.
- `status` is constrained to known values.
- The accepted `due_date` format is defined here, not in the controller.

---

**Consider...**

- Where is invalid API input rejected in this feature?

## Show the Data and Response Layers

**Terminal Command**

```bash
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:app/Models/Task.php
git show 18abe4ff7ef8af74fe214fec78217962e63d88c3:app/Http/Api/Resources/TaskResource.php
```

---

**Open**

- `app/Models/Task.php`
- `app/Http/Api/Resources/TaskResource.php`

**Review**

- The model defines fillable attributes, the `due_date` cast, and the `assignedTo()` relationship.
- The resource controls the public JSON fields returned to the API consumer.
- The resource also formats `due_date` and includes nested assigned user data only when loaded.

---

**Consider...**

- Which class controls the JSON fields returned to the API consumer?

## Trace the Task Operation

**Purpose**

Trace `POST /api/v1/tasks` from request entry to JSON response.

**Open**

- `routes/api/tasks.php`
- `app/Http/Api/Requests/Tasks/StoreTaskRequest.php`
- `app/Http/Api/Controllers/Tasks/TaskController.php`
- `app/Models/Task.php`
- `app/Http/Api/Resources/TaskResource.php`

---

**Review**

```text
POST /api/v1/tasks
-> auth:sanctum middleware
-> StoreTaskRequest
-> TaskController::store()
-> Task::create(...)
-> TaskResource
-> JSON response
```

---

**Expected Observation**

- The route decides which controller method runs.
- The request class decides whether the payload is acceptable.
- The controller coordinates creation.
- The model stores the data.
- The resource shapes the JSON response.

**Review**

- This commit range includes an authentication feature test, not a Task feature test.
- For the Task API recap, use one short automated test run plus one short API demonstration.

---

**Terminal Command**

```bash
php artisan test tests/Feature/Auth/LoginTest.php
```

**Expected Observation**

- The login endpoint returns a token.
- Repeated failed logins become rate-limited.

---

**Open**

- `tests/Feature/Auth/LoginTest.php`
- `database/seeders/DatabaseSeeder.php`

**Review**

- The seeded client account is `client@example.com` / `password`.
- Authentication is part of the Task API flow because task routes are protected.

---

## Test the api using curl

If the app is already running and seeded, demonstrate the flow with a login request followed by one task request.

**Terminal Command**

```bash
curl -X POST http://127.0.0.1:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "X-Integration-Name: local-client" \
  -d '{
    "email": "client@example.com",
    "password": "password"
  }'
```

---

Show the returned token, then use it in one task request.

```bash
curl http://127.0.0.1:8000/api/v1/tasks \
  -H "Accept: application/json" \
  -H "X-Integration-Name: local-client" \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

**Expected Observation**

- View the request, HTTP status, and JSON response shape.
- The API only exposes the fields defined by `TaskResource`.

---

**Open**

- `app/Http/Api/Controllers/Tasks/TaskController.php`

**Consider...**

- What information enters this controller method?
- What responsibilities does the controller currently coordinate?
- Which parts of the operation depend on the HTTP request?
- If this operation became larger, what might become harder to maintain?
- If this operation needed to be reused elsewhere, what challenges might appear?

---

## Transition

We have reviewed the Task API exactly as it existed at the end of Session 3. We can now identify where the request enters the application, where input is validated, where the controller coordinates the operation, where data is stored, and how the result becomes a JSON API response. The next part of the lecture will look at other ways to organise these responsibilities in a larger API.
