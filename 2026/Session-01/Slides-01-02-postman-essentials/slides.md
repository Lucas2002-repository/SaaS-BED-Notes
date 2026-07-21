---
theme: nmt
layout: cover
title: SaaS-2-BED – Session 01 (Supplement)
info: Postman Essentials for REST API Testing
transition: slide-left
mdc: true
---

# 🎞️ SaaS‑2‑BED – Session 01 (Supplement)

## Postman Essentials for REST API Testing

---

layout: section
---

# **What is Postman?**

- Send requests, inspect responses
- Organise with Collections
- Use variables & environments
- Automate checks with Tests

---

# **Installing & Opening Postman**

Download: https://www.postman.com/downloads/

---

# **Importing a Collection**

1. Click **Import**
2. Upload the JSON file

---

# **Setting Up Variables**

Use collection variables:
`base_url = http://127.0.0.1:8000`

---

# **Using Environments**

Create an environment and set `base_url`.

---

# Template Collections

### Before we build our own API, we'll use two ready-made Postman collections

| Collection               | Purpose                                                                                                                                      |
| ------------------------ | -------------------------------------------------------------------------------------------------------------------------------------------- |
| **REST API Basics**      | Learn how individual HTTP requests work using GET, POST, PUT, PATCH and DELETE. Observe status codes, headers and JSON responses.            |
| **API Scenario Testing** | Test complete business workflows by creating, retrieving, updating and deleting resources while automatically passing data between requests. |

---

### Why use template collections?

- Learn API testing without writing code first.
- Observe REST principles in action.
- Build confidence using Postman.
- Reuse and extend these collections throughout the course.

> **Next:** We'll import both collections and explore how professional developers test REST APIs.

<!--
Presenter Notes

This course includes two reusable Postman collections.

The first focuses on understanding individual REST requests and responses.

The second demonstrates complete business workflows using variables and automated sequencing.

Students should view these as reusable learning tools that will be extended throughout the semester as new API features are introduced.

Today's objective is simply to become familiar with the collections before beginning the Laravel practical.
-->

---

# **Running Requests**

Test `GET /courses`.

---

# **POST Requests**

Example JSON body:

```json
{ "code": "BED101", "title": "Backend Basics" }
```

---

# **Tests**

```javascript
pm.test("Status is 201", () => pm.response.to.have.status(201));
pm.test("Has Location header", () => pm.response.headers.has("Location"));
```

---

# **Saving Examples**

Use **Save Response → Save as Example**.

---

# **Exporting Collections**

Use: **⋮ → Export → Collection v2.1**

---

# **Further Study**

- https://learning.postman.com/
- https://laravel.com/docs/
- https://pestphp.com/
