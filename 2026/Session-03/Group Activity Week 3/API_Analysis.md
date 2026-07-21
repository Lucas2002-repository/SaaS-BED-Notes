Activity 3 (Week 3)

Connecting to an API – Authentication, Settings and Secure Access

Duration: 45–60 minutes

API Documentation: https://api-docs.jobmanapp.com

Coding Required: No

---

Learning Outcomes

By the end of this activity students should be able to:

- explain why APIs require authentication;
- distinguish between authentication and authorisation;
- identify how OAuth 2.0 is used to protect an API;
- recognise the purpose of API settings and configuration endpoints;
- identify where authentication should occur within a software architecture;
- explain why API keys and access tokens should never be exposed.

---

Scenario

Your team has completed its review of the Jobman API and has proposed a software architecture.

Management has approved your design.

The next task is to connect your application to the API.

Before writing any code, your team must understand:

- how users authenticate;
- how applications authenticate;
- how the API protects business data;
- how configuration information is managed.

---

Part 1 – Authentication Investigation

Review the documentation for:

- Authentication
- OAuth 2.0
- Authorisation
- Access Tokens
- Rate Limits

As a group discuss:

- Why doesn't every endpoint allow anonymous access?
- Why might access tokens expire?
- Why are refresh tokens useful?
- Why shouldn't usernames and passwords be sent with every request?
- Why does the API use HTTPS?

---

Part 2 – Authentication Flow

Create a flow diagram showing what you believe happens when a user signs in.

Example:

User

↓

Login Screen

↓

OAuth Provider

↓

Access Token

↓

API Client

↓

Protected Endpoint

↓

Response

Discuss:

- Where is the access token stored?
- Who validates the token?
- What happens if the token expires?
- What happens if an invalid token is sent?

---

Part 3 – Settings Investigation

Explore the Settings area of the API.

Examples may include:

- Organisation settings
- Staff settings
- User preferences
- System configuration
- Templates
- Payment settings
- Tax settings
- Notification settings

Discuss:

- Which settings belong to an organisation?
- Which belong to individual users?
- Which settings are likely to change frequently?
- Which settings should rarely change?

---

Part 4 – Protecting Sensitive Data

Your application stores:

- Customer details
- Quotes
- Jobs
- Invoices
- Staff information

Discuss:

- Which users should be allowed to view this information?
- Which users should be allowed to edit it?
- Should every authenticated user have the same permissions?
- What could happen if permissions are configured incorrectly?

Explain why authentication alone does not determine what a user is allowed to do.

---

Part 5 – Where Does Authentication Belong?

Using your architecture from Week 2, identify where authentication should occur.

For each layer decide whether it should:

- know about authentication;
- validate tokens;
- attach tokens to requests;
- check user permissions;
- display login screens.

Complete the following table.

Layer| Responsibility
User Interface|
API Client|
Feature Services|
REST API|
Business Logic|
Database|

---

Part 6 – Critical Thinking

Discuss the following questions.

Scenario 1

A developer hard-codes an access token into the application.

What problems could this create?

---

Scenario 2

An access token never expires.

Would this improve usability?

Would it reduce security?

---

Scenario 3

Every user has administrator access.

What advantages would this provide?

What risks would this introduce?

---

Scenario 4

Your application stores user passwords in plain text.

What problems could occur?

How should passwords normally be handled?

---

Part 7 – Reflection

Prepare a four-minute presentation covering:

- How authentication works at a high level.
- The difference between authentication and authorisation.
- Where authentication belongs in your architecture.
- Three settings your application would need to read.
- One security practice every API developer should follow.
- One question you would ask the Jobman development team before implementing authentication.

---

Deliverables

Each group submits:

- Authentication flow diagram.
- Updated software architecture.
- Completed responsibility table.
- Summary of authentication versus authorisation.
- One-page security recommendations.

---

Lecturer Notes

This activity intentionally avoids implementing OAuth.

The goal is for students to understand:

- why authentication exists;
- where authentication fits into an application's architecture;
- why configuration endpoints are separated from business endpoints;
- why protecting API credentials is essential.

Implementation of OAuth, access tokens and authenticated API requests will be covered in later practical sessions once students have a solid understanding of the underlying concepts.
