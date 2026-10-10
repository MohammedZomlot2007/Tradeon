# Full Laravel + React + Inertia + MySQL Project Audit

Act as a **Senior Full-Stack Software Engineer, Laravel Architect, React Engineer, Inertia.js Specialist, MySQL Database Engineer, Security Engineer, and QA Engineer**.

I have an existing project built with:

- Laravel
- PHP
- React
- Inertia.js
- Vite
- MySQL

Your responsibility is to **fully inspect, verify, test, identify problems, and safely fix the project**.

The goal is to make sure the entire application works correctly from:

```text
Database
↓
Laravel Backend
↓
Routes
↓
Controllers
↓
Inertia
↓
React
↓
UI
↓
User Actions
↓
Laravel Backend
↓
MySQL
```

Do not only check whether the project compiles.

The application must work correctly from both the **technical** and **user** perspective.

---

# IMPORTANT RULES

1. Do NOT rewrite the project from scratch.
2. Do NOT replace the existing architecture without a strong reason.
3. Do NOT delete existing functionality.
4. Do NOT randomly update dependencies.
5. Do NOT make unnecessary refactors.
6. Do NOT change business logic unless it is clearly incorrect.
7. First inspect and understand the project.
8. Identify problems before fixing them.
9. Make the smallest safe change required.
10. After every important change, verify that nothing else broke.
11. Never assume something works just because the code looks correct.
12. Actually run commands and tests whenever possible.
13. Do not hide errors just to make the build pass.
14. Do not expose secrets from `.env`.
15. Do not modify production data.
16. Do not run destructive database commands against the real database.
17. Preserve existing UI and business behavior unless a problem requires changing them.

---

# PHASE 1 — PROJECT DISCOVERY

First inspect the entire project structure.

Identify:

- Laravel version
- PHP version
- React version
- Inertia version
- Vite version
- Node version
- npm version
- MySQL version if available
- authentication system
- authorization system
- frontend architecture
- database architecture
- testing setup

Inspect:

```text
composer.json
package.json
.env.example
routes/
app/
config/
database/
resources/
resources/js/
tests/
vite.config.*
phpunit.xml
```

Also inspect any:

```text
README
documentation
architecture files
AI instructions
coding conventions
```

if they exist.

Before changing anything, create a short understanding of the current architecture.

---

# PHASE 2 — ENVIRONMENT VERIFICATION

Check the environment:

```bash
php -v
composer -V
node -v
npm -v
```

Check Laravel:

```bash
php artisan --version
php artisan about
```

Check dependencies:

```bash
composer validate
npm list --depth=0
```

Check whether the required packages are compatible.

Pay special attention to:

- Laravel
- PHP
- React
- Inertia
- Vite
- Node
- npm
- TypeScript if used
- Tailwind if used
- UI libraries

Do NOT upgrade packages automatically.

If there is a version conflict:

1. Identify it.
2. Explain it.
3. Determine the safest solution.
4. Only then apply a fix if necessary.

---

# PHASE 3 — MYSQL DATABASE AUDIT

The application uses:

**MySQL**

Treat MySQL as the primary database.

Verify:

```env
DB_CONNECTION=mysql
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=
```

Never expose the real password.

Use `.env.example` for documentation.

---

## MySQL Connection

Verify that Laravel can connect to MySQL.

Use safe commands such as:

```bash
php artisan migrate:status
```

Do not run destructive commands on the real database.

---

## MySQL Schema

Inspect:

```text
database/migrations
database/seeders
database/factories
```

Check:

- tables
- primary keys
- foreign keys
- indexes
- unique constraints
- nullable columns
- default values
- data types
- timestamps
- soft deletes
- cascade behavior

Look for:

- missing indexes
- incorrect foreign keys
- inconsistent data types
- duplicate indexes
- unnecessary indexes
- incorrect nullable fields
- missing unique constraints
- dangerous cascade deletes

---

## MySQL Relationships

Compare database relationships with Laravel relationships.

Verify:

```text
hasOne
hasMany
belongsTo
belongsToMany
morphOne
morphMany
```

Check that:

```text
Database relationship
=
Migration
=
Model relationship
=
Controller usage
=
React data structure
```

Identify inconsistencies.

---

## MySQL Charset & Collation

Check the project's charset and collation.

Pay attention to Arabic and multilingual data.

Verify that the database supports:

- Arabic
- English
- Unicode
- emojis if required

Avoid introducing encoding problems.

---

# PHASE 4 — DATABASE PERFORMANCE

Check for:

- N+1 queries
- missing indexes
- unnecessary queries
- loading large datasets
- inefficient filtering
- inefficient sorting
- inefficient searching
- unnecessary joins
- repeated database queries

Pay special attention to Laravel relationships.

Example:

```php
User::with('orders')
```

may be required instead of repeatedly querying relationships.

Do not optimize blindly.

Only make changes where there is a clear problem.

---

# PHASE 5 — LARAVEL ROUTES AUDIT

Inspect all routes.

Run:

```bash
php artisan route:list
```

Check:

- duplicate routes
- incorrect HTTP methods
- missing route names
- incorrect middleware
- incorrect route parameters
- broken route model binding
- routes pointing to nonexistent controller methods
- unauthorized routes
- unnecessary routes

Verify every important route has the correct:

```text
GET
POST
PUT
PATCH
DELETE
```

behavior.

---

# PHASE 6 — CONTROLLERS AUDIT

Inspect every important controller.

Check:

- validation
- authorization
- business logic
- database queries
- relationships
- redirects
- Inertia responses
- error handling
- transactions where necessary

Look for:

- duplicated logic
- huge controllers
- business logic that should be in services
- missing validation
- missing authorization
- incorrect redirects
- incorrect response structures
- N+1 queries

Do not refactor everything just for style.

---

# PHASE 7 — MODELS AUDIT

Inspect models.

Check:

- `$fillable`
- `$guarded`
- `$casts`
- relationships
- scopes
- accessors
- mutators
- enums
- soft deletes
- model events

Verify relationships against the MySQL schema.

Check for:

- incorrect foreign keys
- wrong relationship types
- missing relationships
- incorrect casts
- mass assignment vulnerabilities

---

# PHASE 8 — FORM REQUESTS & VALIDATION

Inspect all Form Requests and validation logic.

Check:

- required fields
- nullable fields
- unique rules
- update rules
- authorization
- numeric validation
- dates
- enums
- IDs
- file uploads
- strings
- arrays

Test:

```text
Valid data
Empty data
Missing required fields
Invalid data types
Duplicate data
Very long strings
Special characters
Arabic text
English text
Invalid IDs
Nonexistent records
Unauthorized requests
```

Make sure validation happens on the backend.

Never trust frontend validation alone.

---

# PHASE 9 — AUTHENTICATION

Test the complete authentication flow.

Check:

```text
Registration
Login
Logout
Session
Password reset
Email verification if implemented
Remember me if implemented
Protected routes
Guest routes
```

Test:

```text
Unauthenticated user
Authenticated user
Invalid credentials
Expired session
Invalid session
Unauthorized access
```

Make sure protected operations are protected on the server.

---

# PHASE 10 — AUTHORIZATION

Audit:

- middleware
- policies
- gates
- permissions
- roles

Test cases:

```text
User A accessing User B data
User A editing User B data
User A deleting User B data
Unauthorized POST
Unauthorized PUT
Unauthorized PATCH
Unauthorized DELETE
```

Authorization must exist on the **Laravel backend**.

Do not rely only on React to hide buttons.

---

# PHASE 11 — INERTIA.JS AUDIT

This is a critical part of the audit.

Inspect the complete Laravel ↔ Inertia ↔ React flow.

Verify:

```text
Laravel Controller
↓
Inertia Response
↓
React Page
↓
Props
↓
Components
```

Check:

- Inertia middleware
- Inertia responses
- shared props
- authentication props
- permissions
- flash messages
- validation errors
- redirects
- partial reloads
- lazy props if used
- pagination

Look for:

- missing props
- incorrect prop names
- incorrect prop types
- nullable values not handled
- incorrect redirects
- broken forms
- backend/frontend data mismatch

---

# PHASE 12 — FRONTEND / BACKEND DATA CONTRACT

For every important feature verify:

```text
MySQL
↓
Model
↓
Controller
↓
Inertia
↓
React Props
↓
React Component
↓
User Action
↓
Inertia Form
↓
Controller
↓
Validation
↓
MySQL
```

Check consistency for:

- IDs
- names
- dates
- booleans
- statuses
- enums
- arrays
- nested objects
- pagination
- nullable values

Pay special attention to:

```text
snake_case
camelCase
```

Do not fix one layer while breaking another.

---

# PHASE 13 — REACT AUDIT

Inspect:

```text
resources/js/
```

including:

- Pages
- Components
- Layouts
- Hooks
- Utils
- Forms
- Tables
- Modals
- Navigation
- Shared components

Check for:

- broken imports
- undefined variables
- incorrect props
- incorrect hooks
- incorrect `useEffect`
- unnecessary state
- duplicated state
- unnecessary re-renders
- memory leaks
- stale data
- inconsistent components

Check React console/runtime errors.

---

# PHASE 14 — INERTIA FORMS

Audit every form.

Check:

- `useForm`
- submission
- validation errors
- loading state
- disabled state
- duplicate submission
- reset
- cancel
- success
- server errors
- redirects

Every form should correctly handle:

```text
Idle
Loading
Success
Validation Error
Server Error
Network Error
```

Test double-clicking the submit button.

---

# PHASE 15 — CRUD VERIFICATION

For every CRUD feature verify:

```text
Create
Read
Update
Delete
```

Test:

### Create

- valid data
- invalid data
- duplicate data
- missing fields
- unauthorized user

### Read

- existing record
- nonexistent record
- empty state
- pagination
- filtering
- sorting

### Update

- valid update
- invalid update
- duplicate values
- unauthorized update
- updating deleted record

### Delete

- authorized delete
- unauthorized delete
- nonexistent record
- related records
- confirmation
- soft delete if used

---

# PHASE 16 — ERROR HANDLING

Verify handling for:

```text
401 Unauthorized
403 Forbidden
404 Not Found
419 Page Expired
422 Validation Error
429 Too Many Requests
500 Server Error
Network Error
Database Error
```

The user should receive a proper UI response.

Do not expose:

- SQL queries
- stack traces
- secrets
- database credentials
- internal implementation details

in production.

---

# PHASE 17 — SECURITY AUDIT

Perform a security review.

Check for:

- SQL injection
- XSS
- CSRF
- mass assignment
- authorization bypass
- IDOR
- insecure file uploads
- sensitive information exposure
- unsafe redirects
- missing validation
- insecure API endpoints
- trusting frontend permissions

Check:

```env
APP_ENV
APP_DEBUG
APP_KEY
```

Never expose `.env`.

Make sure `.gitignore` properly protects:

```text
.env
/vendor
/node_modules
```

Do not commit secrets.

---

# PHASE 18 — FILE UPLOADS

If the application supports uploads, verify:

- file validation
- MIME validation
- file size
- filename handling
- storage configuration
- public/private storage
- unauthorized access
- dangerous file types

Test:

```text
Valid file
Invalid file
Large file
Empty file
Wrong MIME type
Duplicate file
Unauthorized upload
Unauthorized download
```

---

# PHASE 19 — UI / UX AUDIT

Check all pages.

Verify consistency of:

- buttons
- inputs
- forms
- tables
- cards
- modals
- alerts
- dropdowns
- navigation
- typography
- spacing
- loading indicators

Check:

```text
Loading state
Empty state
Error state
Success state
Disabled state
```

Do not redesign the application.

Focus on correctness and consistency.

---

# PHASE 20 — RESPONSIVE DESIGN

Verify the frontend on:

```text
Mobile
Tablet
Desktop
Large Desktop
```

Check:

- horizontal overflow
- tables
- forms
- navigation
- sidebar
- modals
- cards
- buttons
- long text
- images
- dropdowns

If Arabic/RTL is supported, also verify:

```text
RTL
Arabic text
Mixed Arabic + English
Numbers
Dates
Tables
Icons
Alignment
```

---

# PHASE 21 — NAVIGATION

Check every:

- link
- button
- menu item
- breadcrumb
- pagination link
- redirect
- back action

Verify that all links point to valid routes.

Look for:

- broken links
- outdated routes
- unauthorized pages
- incorrect route parameters

---

# PHASE 22 — SEARCH / FILTER / PAGINATION

For every search/filter/list feature verify:

```text
No results
One result
Many results
Invalid search
Arabic search
English search
Special characters
Pagination
Large dataset
Multiple filters
Reset filters
```

Make sure filtering and pagination are handled efficiently on the backend when appropriate.

---

# PHASE 23 — VITE / FRONTEND BUILD

Inspect:

```text
package.json
vite.config.*
```

Run:

```bash
npm install
npm run build
```

Only use `npm install` if appropriate for the current project state.

Check for:

- dependency conflicts
- build errors
- missing environment variables
- invalid imports
- unresolved modules
- Vite configuration problems

Do not randomly modify package versions.

---

# PHASE 24 — TESTING

Inspect existing tests.

Run:

```bash
php artisan test
```

if configured.

Also run frontend lint/type checks if configured.

If tests are missing for important functionality, identify the missing coverage.

Prioritize tests for:

```text
Authentication
Authorization
CRUD
Validation
Critical business logic
Database relationships
Important Inertia pages
```

---

# PHASE 25 — LARAVEL CACHE / CONFIG

After appropriate changes, verify Laravel caches are not hiding problems.

Use safely:

```bash
php artisan optimize:clear
```

Then verify:

```bash
php artisan route:list
php artisan config:show database
```

Do not cache production configuration during development unless required.

---

# PHASE 26 — ENVIRONMENT CONFIGURATION

Inspect:

```text
.env.example
```

Document required variables such as:

```text
APP_NAME
APP_ENV
APP_KEY
APP_URL

DB_CONNECTION
DB_HOST
DB_PORT
DB_DATABASE
DB_USERNAME
DB_PASSWORD
```

plus any project-specific:

```text
Mail
Storage
Queue
Broadcasting
Payment
Third-party APIs
Frontend variables
```

Never expose actual secrets.

---

# PHASE 27 — EDGE CASE TESTING

For every important feature consider:

```text
No data
One record
Many records
Large dataset
Invalid ID
Nonexistent record
Deleted record
Duplicate record
Unauthorized user
Unauthenticated user
Expired session
Invalid form
Empty form
Long strings
Special characters
Arabic
English
Arabic + English
Slow network
Network failure
Server error
Database error
Double submission
Refresh
Browser back button
Mobile screen
```

---

# PHASE 28 — DEPENDENCY CHECK

Inspect:

```text
composer.json
package.json
```

Identify:

- unused dependencies
- missing dependencies
- conflicting dependencies
- deprecated dependencies
- incompatible versions

Do not update dependencies unless necessary.

If an update is required, explain:

```text
Current version
Required version
Reason
Potential breaking changes
```

before applying it.

---

# PHASE 29 — AUDIT REPORT BEFORE FIXING

Before making major changes, generate an audit report.

Use this structure:

```text
# PROJECT AUDIT REPORT

## 1. Project Overview

## 2. Technology Stack

## 3. Architecture

## 4. Environment

## 5. MySQL Database

## 6. Laravel Backend

## 7. React Frontend

## 8. Inertia Integration

## 9. Authentication

## 10. Authorization

## 11. Security

## 12. Performance

## 13. UI/Responsive

## 14. Testing

## 15. Build

## 16. Problems Found

## 17. Recommended Fixes
```

For every issue use:

```text
Issue:
Severity:
File:
Location:
Why it is a problem:
Recommended solution:
Risk:
```

Severity:

```text
CRITICAL
HIGH
MEDIUM
LOW
OPTIONAL
```

---

# PHASE 30 — FIXING STRATEGY

After the audit, fix issues in this order:

```text
CRITICAL
↓
HIGH
↓
MEDIUM
↓
LOW
```

For every fix:

1. Explain the issue.
2. Identify the affected files.
3. Make the smallest safe change.
4. Verify the feature.
5. Run relevant tests.
6. Check for regressions.
7. Continue.

Do not make unrelated refactors.

---

# PHASE 31 — FINAL FULL VERIFICATION

After all fixes, run the verification again.

Verify:

```text
Laravel starts
PHP works
Composer works
Node works
npm works
Vite works
React works
Inertia works
MySQL works
Routes work
Authentication works
Authorization works
CRUD works
Forms work
Validation works
Database relationships work
Search works
Filters work
Pagination works
Error handling works
Responsive UI works
Security checks pass
Tests pass
Production build works
```

---

# FINAL REPORT

At the end provide:

```text
# FINAL PROJECT STATUS

Overall Status:
READY / NOT READY / READY WITH CONDITIONS

Laravel:
PASS / WARN / FAIL

React:
PASS / WARN / FAIL

Inertia:
PASS / WARN / FAIL

MySQL:
PASS / WARN / FAIL

Database:
PASS / WARN / FAIL

Authentication:
PASS / WARN / FAIL

Authorization:
PASS / WARN / FAIL

Security:
PASS / WARN / FAIL

Performance:
PASS / WARN / FAIL

UI:
PASS / WARN / FAIL

Responsive:
PASS / WARN / FAIL

Testing:
PASS / WARN / FAIL

Build:
PASS / WARN / FAIL
```

Then provide:

```text
## Fixed Issues

## Remaining Issues

## Known Risks

## Tests Executed

## Commands Executed

## Recommended Next Steps
```

For every remaining issue explain its severity.

---

# MOST IMPORTANT REQUIREMENT

Do not tell me:

> "The project looks good."

unless you actually inspected and verified it.

I want a real engineering audit.

The final application must have consistency across:

```text
MySQL
↓
Laravel Models
↓
Laravel Controllers
↓
Validation
↓
Authorization
↓
Routes
↓
Inertia
↓
React Props
↓
React Components
↓
Forms
↓
User Interaction
↓
Laravel
↓
MySQL
```

Every important feature must work across the complete flow.

The goal is not merely:

```text
npm run build → PASS
```

The goal is:

```text
Application works correctly
+
Backend is correct
+
MySQL is correct
+
React is correct
+
Inertia is correctly integrated
+
Authentication is secure
+
Authorization is enforced
+
Forms work
+
Validation works
+
Errors are handled
+
Edge cases are considered
+
UI is responsive
+
No obvious security issues
+
No obvious runtime errors
+
Production build works
```

**Start by inspecting the project. Do not modify anything until you understand the existing architecture and have identified the important issues.**