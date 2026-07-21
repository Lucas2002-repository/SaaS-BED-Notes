Activity 4 (Week 4)

Roles, Permissions and External Integrations

Duration: 45–60 minutes

API Documentation: https://api-docs.jobmanapp.com

Coding Required: No

---

- explain the purpose of role-based access control (RBAC);
- distinguish between authentication and authorisation;
- identify different permission levels within a business application;
- explain how APIs integrate with external systems;
- recognise common integration patterns;
- evaluate the security implications of third-party integrations.

---

Scenario

Your team has successfully connected your application to the Jobman API.

The application now authenticates users and can access business data.

The next challenge is determining:

- what each user is allowed to do;
- how the application exchanges information with external services.

---

Part 1 – Investigating Roles

Review the documentation relating to:

- Staff
- Staff Roles
- Users
- Permissions
- Settings
- Organisation

As a group discuss:

- What different types of users might exist?
- Would every staff member require the same permissions?
- Which users should manage staff?
- Which users should manage invoices?
- Which users should manage system settings?

Create a list of possible user roles.

Example:

- Administrator
- Manager
- Office Staff
- Technician
- Sales Representative
- Read-only User

---

Part 2 – Designing Permissions

Choose five resources from the API.

For each resource decide which roles should be able to:

- View
- Create
- Update
- Delete
- Approve
- Export

Complete the following table.

Resource| Admin| Manager| Staff| Read Only
Jobs| | | |
Quotes| | | |
Contacts| | | |
Invoices| | | |
Staff| | | |

Be prepared to justify every permission.

---

Part 3 – Authentication vs Authorisation

Using your Week 3 work, explain the difference between:

Authentication

«Who are you?»

Authorisation

«What are you allowed to do?»

Provide one example where:

- authentication succeeds;
- authorisation fails.

Explain why this is a normal situation.

---

Part 4 – External Integrations

Explore the documentation for features that communicate with external systems.

Examples include:

- Accounting systems
- Email
- File storage
- Webhooks
- Imports
- Exports
- Notifications

Discuss:

- Why do businesses integrate systems?
- What information might be exchanged?
- Which integrations send data?
- Which receive data?
- Which require authentication?

---

Part 5 – Integration Mapping

Select one business process.

Examples:

- Quote to Accounting
- Invoice to Accounting
- Contact Import
- Contact Export
- Email Notifications
- File Uploads

Draw a simple integration diagram.

Example:

Customer

↓

Jobman

↓

REST API

↓

Accounting System

Label:

- what information is transferred;
- who initiates the transfer;
- possible errors;
- security considerations.

---

Part 6 – Critical Thinking

Discuss the following scenarios.

Scenario 1

Every authenticated user has administrator access.

What problems could occur?

---

Scenario 2

An external accounting system is unavailable.

Should users still be able to create invoices?

What should happen when the connection is restored?

---

Scenario 3

A staff member accidentally deletes customer information.

Should every role be allowed to delete records?

Could there be a better solution?

---

Scenario 4

A webhook is received from an unknown source.

Should your application trust it automatically?

What checks should occur before processing it?

---

Part 7 – Architecture Review

Update your architecture from Week 3.

Add:

- Roles
- Permissions
- Integration Layer
- External Systems

Example:

User

↓

Authentication

↓

Authorisation

↓

API Client

↓

Feature Services

↓

REST API

↓

Business Logic

↓

Integration Layer

↓

Accounting
Email
Webhooks
File Storage

Identify which layers are responsible for:

- checking permissions;
- calling external APIs;
- handling failed integrations;
- logging integration activity.

---

Part 8 – Reflection

Prepare a five-minute presentation.

Include:

- your proposed user roles;
- permission matrix;
- one external integration;
- updated architecture diagram;
- one security recommendation;
- one business benefit of using integrations.

---

Deliverables

Each group submits:

- Role hierarchy.
- Permission matrix.
- Integration diagram.
- Updated architecture.
- One-page evaluation of the security and business implications of external integrations.

---

Lecturer Notes

This activity introduces students to concepts rather than implementation.

Students should recognise that:

- authentication identifies the user;
- authorisation determines what the user may do;
- permissions are based on business requirements;
- integrations connect business systems together;
- external systems can fail and software must handle those failures gracefully.

Implementation of permissions, middleware, JWT claims, OAuth scopes and API integration code will be covered in later practical sessions.
