---
marp: true
theme: nmtafe
paginate: true
header: "ICT50220 Diploma of Information Technology | Stage 2 – Enterprise API Engineering"
footer: "Session 3 Practical – Authentication and API Security"
---

# Session 3 Practical

# Authentication and API Security

## Practical Implementation of Sanctum

Continue developing the **Starter API** from Sessions 1 and 2.

---

# Practical Goal

By the end of this practical, the class API will have:

- Sanctum token authentication
- Identity routes
- Login endpoint
- Current user endpoint
- Logout endpoint
- Protected Task routes
- Token abilities
- Postman authentication tests

---

# Starting Point

Use the existing class repository.

Do **not** create a new Laravel project.

The repository already contains:

- Laravel application
- API route file
- User model
- Task API structure
- Laravel Sanctum package
- Sanctum token migration

---

# Step 1

## Review Existing Authentication Files

Open the following files with the lecturer:

```text
composer.json
config/sanctum.php
routes/api.php
app/Models/User.php
database/migrations/
```

Confirm that Sanctum is installed but not fully implemented.

---

# Step 2

## Check the User Model

Open:

```text
app/Models/User.php
```

Review the traits currently used by the model.

The User model must be able to issue API tokens.

---

# Step 3

## Add HasApiTokens

Update the User model to include Sanctum token support.

```php
use Laravel\Sanctum\HasApiTokens;
```

Add the trait:

```php
use HasApiTokens, HasFactory, Notifiable;
```

Run the application and confirm there are no errors.

---

# Step 4

## Confirm the Test User

Review the database seeder or factory.

The practical will use a known test user.

Example:

```text
Email: test@example.com
Password: password
```

Run migrations and seed the database if required.

---

# Step 5

## Create Identity Controllers

Create a dedicated identity area for authentication.

```text
app/Http/Controllers/Api/V1/Identity/
├── LoginController.php
├── LogoutController.php
└── CurrentUserController.php
```

Each controller should have one clear responsibility.

---

# Step 6

## Register Identity Routes

Open:

```text
routes/api.php
```

Create versioned authentication routes.

```text
POST /api/v1/auth/login
GET  /api/v1/auth/user
POST /api/v1/auth/logout
```

Keep authentication routes separate from Task routes.

---

# Step 7

## Build the Login Endpoint

Implement the login workflow.

```text
Receive credentials
↓
Find user
↓
Check password
↓
Create token
↓
Return JSON response
```

The response should return a Bearer token.

---

# Step 8

## Example Login Response

A successful login should return a response similar to:

```json
{
  "token_type": "Bearer",
  "access_token": "1|...",
  "abilities": ["tasks:read"]
}
```

Copy the access token for Postman testing.

---

# Step 9

## Build the Current User Endpoint

Implement:

```text
GET /api/v1/auth/user
```

This endpoint should return the authenticated user.

Protect this route using:

```php
auth:sanctum
```

---

# Step 10

## Build the Logout Endpoint

Implement:

```text
POST /api/v1/auth/logout
```

The logout endpoint should revoke the current token.

After logout, the same token should no longer authenticate.

---

# Step 11

## Protect the Task Routes

Apply Sanctum middleware to the Task routes.

```php
Route::middleware('auth:sanctum')->group(function () {
    // protected task routes
});
```

Repeat the request in Postman before and after authentication.

---

# Step 12

## Add Token Abilities

Issue tokens with simple abilities.

```text
tasks:read
tasks:write
```

Use these abilities to limit what the token can do.

Do not give every token unrestricted access.

---

# Step 13

## Protect by Ability

Protect read and write actions separately.

Example:

```php
abilities:tasks:read
```

Example:

```php
abilities:tasks:write
```

Test each route using Postman.

---

# Step 14

## Configure Postman Login

Create a Postman request:

```text
POST /api/v1/auth/login
```

Use JSON body data:

```json
{
  "email": "test@example.com",
  "password": "password"
}
```

Send the request and copy the returned token.

---

# Step 15

## Configure Postman Bearer Token

For protected requests, add:

```text
Authorization: Bearer <token>
```

In Postman, use:

```text
Authorization
Type: Bearer Token
Token: <access_token>
```

Send the protected request again.

---

# Step 16

## Test Successful Authentication

Test the following flow:

```text
Login
↓
Copy token
↓
GET /api/v1/auth/user
↓
GET /api/v1/tasks
```

Expected result:

```text
200 OK
```

The API should recognise the authenticated user.

---

# Step 17

## Test Missing Token

Remove the Authorization header.

Send the protected request again.

Expected result:

```text
401 Unauthenticated
```

The request failed because the API cannot identify the caller.

---

# Step 18

## Test Invalid Token

Change part of the Bearer token.

Send the protected request again.

Expected result:

```text
401 Unauthenticated
```

The request failed because the supplied credential is not valid.

---

# Step 19

## Test Revoked Token

Login successfully.

Use the token.

Logout.

Try to use the same token again.

Expected result:

```text
401 Unauthenticated
```

The token has been revoked.

---

# Step 20

## Test Missing Ability

Use a token with:

```text
tasks:read
```

Attempt a route that requires:

```text
tasks:write
```

Expected result:

```text
403 Forbidden
```

The user is authenticated, but the token is not authorised for that action.

---

# Step 21

## Review 401 vs 403

```text
401 Unauthenticated
```

The API does not know who the caller is.

```text
403 Forbidden
```

The API knows who the caller is, but the caller is not allowed to perform the action.

---

# Step 22

## Final Request Flow

Review the completed authentication pipeline.

```text
Client
↓
Bearer Token
↓
Sanctum Middleware
↓
Authenticated User
↓
Token Ability Check
↓
Controller
↓
JSON Response
```

This is now the security foundation for the Starter API.

---

# Practical Complete

The class API now has:

- Sanctum enabled on the User model
- Identity controllers
- Login endpoint
- Current user endpoint
- Logout endpoint
- Protected Task routes
- Token abilities
- Postman authentication testing

Next session we will improve request validation and API resources.
