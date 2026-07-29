Activity 1 (Week 1)

API Investigation – Understanding an Existing API

We are looking to familiairse and identify:

- resources within a REST API;
- common route patterns;
- distinguish between CRUD operations and business actions;
- relationships between resources;
- thinking about why APIs are structured the way they are.

---

Scenario

Your development team has been asked to build an application that connects to the Jobman API.
The only information available is the API documentation.
Before writing any code, your team must investigate the API and understand how it appears to have been designed.
You are not expected to understand every endpoint.
Instead, look for patterns.

---

Part 1 – Explore the API

Open the documentation:
https://api-docs.jobmanapp.com

Spend ten minutes exploring the documentation.

As a group answer:

- What type of business is this API designed for?
- What are the major sections of the documentation?
- Which resources appear to be the most important?
- Which sections appear to support the others?

---

Part 2 – Assigned Investigation
Each group is allocated one business area.

Examples include:

- Contacts
- Leads
- Quotes
- Jobs
- Projects
- Catalogue
- Finance
- Staff
- Platform Features

Investigate only your assigned area.

---

Part 3 – Evidence Table
Find at least six endpoints.

Record:

- HTTP Method
- Route
- Resource
- What the endpoint appears to do
- Whether it is:
  - Read
  - Create
  - Update
  - Delete
  - Business Action

---

Part 4 – Relationships
Identify relationships between resources.

Examples:

- Job → Tasks
- Quote → Quote Sections
- Contact → Contact Persons
- Invoice → Invoice Items

Discuss:

- Which resource appears to be the parent?
- Which appears to be the child?
- Why?

---

Part 5 – Patterns

As a group discuss:

- Why are nouns used instead of verbs?
- Why are similar routes grouped together?
- What naming conventions can you identify?
- Which endpoints look consistent?
- Which endpoints surprise you?

---

Part 6 – Reflection

Prepare a three-minute presentation covering:

- Your assigned business area
- Three interesting endpoints
- One relationship you discovered
- One design decision you think was good
- One question you would ask the Jobman developers

---

Complete

- Completed evidence table
- Resource relationship diagram
- Group presentation
