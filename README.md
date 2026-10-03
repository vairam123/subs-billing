# Subscription Billing & Usage-Metering System

A multi-tenant Laravel application for recording customer usage, aggregating
usage, calculating subscription billing, handling mid-cycle plan changes, and
providing merchant usage dashboards.

---

## Requirements

- PHP 8.2+
- Composer
- MySQL
- Node.js and npm
- Laravel

---

## Local Setup

### 1. Get the Project

Clone the repository:

```bash
git clone <repository-url>
cd subs-billing
```

Or extract the submitted ZIP file and open the project directory.

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Configure the Environment

Copy `.env.example` to `.env`.

On Windows:

```cmd
copy .env.example .env
```

Configure the database connection in `.env`:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=subs_billing
DB_USERNAME=root
DB_PASSWORD=
```

Use the appropriate MySQL username, password, and database name for the local
environment.

Do not commit real database credentials to the repository.

### 4. Generate the Application Key

```bash
php artisan key:generate
```

### 5. Run Database Migrations

```bash
php artisan migrate
```

This creates the application tables including merchants, customers, plans,
subscriptions, subscription periods, usage events, daily usage, invoices, and
invoice items.

### 6. Install Frontend Dependencies

```bash
npm install
npm run build
```

### 7. Start the Laravel Application

```bash
php artisan serve
```

Open:

```text
http://127.0.0.1:8000
```

### 8. Start the Queue Worker

Usage aggregation is processed asynchronously through Laravel's queue.

Open a second terminal in the project directory and run:

```bash
php artisan queue:work
```

Keep the queue worker running while testing usage ingestion and dashboard
aggregation.

---

# Architecture Summary

The application is organized around the following components:

- **Merchants** are tenants and own plans and customers.
- **Customers** belong to a merchant and can have subscriptions.
- **Plans** define base price, billing cycle, included usage units, currency,
  and overage rate.
- **Subscriptions** contain one or more subscription periods.
- **Subscription periods** preserve the pricing configuration that was active
  during each part of a subscription. This allows usage before and after a
  mid-cycle plan change to be billed using the correct plan and rate.
- **Usage events** are the high-volume source-of-truth records for individual
  usage submissions.
- **Daily usage** stores aggregated customer usage by subscription period and
  date to avoid repeatedly scanning raw usage events for billing and dashboard
  queries.
- **Invoices and invoice items** store the calculated billing result and
  provide a breakdown of base and overage charges.

---

# Usage Ingestion

## Endpoint

```text
POST /api/usage
```

The usage endpoint validates the request, verifies that the customer belongs to
the requested merchant, resolves the subscription period for the usage date,
and records the usage event.

Usage events use an idempotency key with a database uniqueness constraint so
retried requests do not double-count usage.

A race condition between concurrent requests is handled by relying on the
database uniqueness constraint and retrieving the existing event when a
duplicate insert occurs.

Daily aggregation is dispatched to a queue instead of calculating aggregate
totals synchronously during the API request.

---

# Aggregation and Billing

`AggregateDailyUsageJob` calculates daily usage for a customer and
subscription period using a database aggregation and stores the result in the
daily usage table.

Invoice generation calculates each subscription-period segment independently.
This allows a mid-cycle upgrade or downgrade to produce separate charges using
the pricing that was active during each segment.

Base charges are prorated according to the overlapping time period, and
overage is calculated from usage above the applicable included allowance.

Invoice creation is idempotent for the same subscription and billing period.

---

# Mid-Cycle Plan Changes

Subscription periods preserve their own pricing values.

When a customer changes plans during a billing cycle:

1. The existing subscription period ends at the plan-change time.
2. A new subscription period starts with the new plan pricing.
3. Usage before the change remains associated with the old period.
4. Usage after the change is associated with the new period.
5. Each period is billed using its own pricing.
6. Base charges are prorated based on the duration of each period.
7. Included usage and overage are calculated using the pricing applicable to
   that period.

This prevents historical usage from being re-priced when the plan changes.

---

# Merchant Dashboard

## Endpoint

```text
GET /api/merchants/{id}/dashboard
```

The dashboard provides:

- Top 5 customers by current-month usage
- Projected overage revenue for the current cycle
- Customers whose usage has dropped by more than 50% compared with the
  previous month

Daily aggregated usage is used for these reporting queries to avoid repeatedly
scanning the high-volume `usage_events` table.

### Usage Drop Calculation

The month-over-month usage drop compares actual usage in the previous calendar
month with actual usage in the current calendar month.

Example:

```text
Previous month usage = 1,000 units
Current month usage  =   200 units

Decrease = 1,000 - 200
         = 800 units

Drop percentage = 800 / 1,000 × 100
                = 80%
```

A customer is included when the current usage is less than 50% of the previous
month's usage.

---

# Caching

Plan and pricing lookups are cached using Laravel's cache layer.

The cache key includes both merchant ID and plan ID:

```text
merchant:{merchant_id}:plan:{plan_id}:pricing
```

Plan updates and deletes invalidate the corresponding cache entry through the
`Plan` model lifecycle events.

This prevents stale plan pricing from remaining in the cache after a plan
change.

---

# Database Design

The schema is normalized around:

- `merchants`
- `customers`
- `plans`
- `subscriptions`
- `subscription_periods`
- `usage_events`
- `daily_usage`
- `invoices`
- `invoice_items`

Subscription periods retain the pricing values required for historical
billing.

Usage events remain the source of truth, while `daily_usage` acts as an
aggregation/read model for billing and reporting.

---

# Database Indexing

The high-volume `usage_events` table has indexes for common access patterns,
including:

- merchant + usage date
- customer + usage date
- subscription period + usage date
- merchant + idempotency key

The merchant/idempotency-key combination has a unique database constraint to
prevent duplicate usage events.

The `daily_usage` table also has indexes for common customer, merchant,
subscription-period, and date-based lookups.

These indexes improve lookup and aggregation performance but do not by
themselves solve unlimited data growth.

---

# Scalability for 50L+ Usage Rows

For 50L+ usage-event rows, raw usage events should remain the source of truth
while daily aggregation provides a much smaller dataset for billing and
reporting.

The application avoids repeatedly scanning the raw usage-event table for
dashboard and reporting queries by using the `daily_usage` aggregation table.

If usage volume continues to grow significantly, I would consider:

- time-based partitioning of `usage_events`, such as monthly partitions,
  depending on the production database and workload
- retention and archival of older detailed usage events
- additional read models for reporting workloads
- selective denormalization where it provides a measurable performance benefit

A retention/archive strategy would depend on billing history, audit
requirements, customer contracts, and data-retention requirements.

---

# Rate Limiting

The usage endpoint uses Laravel's `throttle:usage` middleware.

The current configured limit is:

```text
60 requests per minute per rate-limit key
```

This protects the usage-ingestion endpoint from excessive traffic while still
allowing normal usage submission.

---

# Code Review — Initial Usage Endpoint

The original endpoint supplied in the assignment only looked up a customer and
directly inserted a usage event.

The main issues identified were:

- Missing request validation
- No handling when the customer does not exist
- No merchant/tenant isolation
- No idempotency mechanism
- No database uniqueness protection for concurrent duplicate requests
- No subscription-period resolution
- No asynchronous aggregation
- No rate limiting
- No useful identifier in the response for the created usage event

The implemented endpoint addresses these concerns through:

- request validation
- tenant-aware customer lookup
- idempotency keys
- database uniqueness protection
- subscription-period resolution
- queued aggregation
- rate limiting

---

# Assumptions and Design Decisions

- Usage is associated with the subscription period active on the usage date.
- Subscription periods preserve their own pricing values so historical billing
  is not affected when a plan changes later.
- Billing periods use a half-open time range: start is included and end is
  excluded.
- Included usage is prorated for a partial subscription-period segment.
- Overage is charged only for usage above the applicable included allowance.
- Daily usage is treated as an aggregation/read model derived from usage
  events.
- Invoice generation for the same subscription and billing period is
  idempotent.
- The dashboard uses the current calendar month and previous calendar month
  for month-over-month usage comparison.

---

# Testing

Run the complete test suite with:

```bash
php artisan test
```

The test suite covers:

### Usage

- Request validation
- Positive usage-unit validation
- Merchant/customer tenant isolation

### Dashboard

- Required dashboard usage metrics
- Current-month usage
- Projected overage revenue
- Month-over-month usage drop

### Caching

- Plan pricing is cached
- Cache is invalidated when a plan is updated

### Billing

- Full cycle within included usage
- Overage billing
- Mid-cycle plan changes
- Proration
- Invoice idempotency
- Usage exactly at the included limit
- Usage one unit above the included limit

Current test result:

```text
13 passed
76 assertions
```

---

# Rollout & Monitoring

I would roll out the system gradually using a feature flag or a small
percentage of merchant traffic first.

During rollout, I would monitor:

- API error rates
- API latency
- Queue depth and failures
- Usage-event insertion rates
- Unusual changes in usage-ingestion volume

Database migrations would be deployed before enabling the new usage-recording
path, with rollback available during the rollout.

The first signal I would want an on-call engineer to see is the
usage-ingestion health signal: successful usage-event rate compared with the
recent baseline, together with API 5xx errors and queue failures.

A sudden drop in successful usage events while traffic remains normal would
indicate that usage recording may be silently failing.

---

# Handing This Off

The next engineer should pay particular attention to:

1. **Usage-event scale:** Monitor growth of the raw usage table and evaluate
   partitioning or additional aggregation strategies as volume increases.

2. **Billing precision:** Monetary calculations should use a consistent
   decimal/money arithmetic strategy before production financial use.

3. **Operational monitoring:** Queue failures, delayed daily aggregation, and
   unusual usage-ingestion rates should have appropriate alerts and dashboards.

Because this was completed under a three-day time-box, the implementation
intentionally keeps the architecture relatively simple.

Production-scale follow-up work could include:

- advanced queue orchestration
- extensive observability
- automated partition management
- stronger deployment automation
- additional reporting read models

---

# Project Structure

Important application areas include:

```text
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Jobs/
├── Models/
└── Services/

database/
└── migrations/

resources/
└── views/

routes/
├── api.php
└── web.php

tests/
├── Feature/
└── Unit/
```

The main business logic is separated into controllers, jobs, models, and
services so that usage ingestion, aggregation, pricing/cache handling, and
billing can be tested independently.

---

# Evaluation Flow

The main application flow is:

```text
Merchant
   ↓
Customer
   ↓
Subscription
   ↓
Subscription Period
   ↓
POST /api/usage
   ↓
Usage Event
   ↓
Queue
   ↓
Daily Usage Aggregation
   ↓
Dashboard / Billing
```

For billing scenarios involving a mid-cycle plan change:

```text
Old Subscription Period
        ↓
Old pricing + old usage
        ↓
Plan Change
        ↓
New Subscription Period
        ↓
New pricing + new usage
        ↓
Prorated Invoice
```

The implementation is designed so that usage is recorded first, aggregation is
processed asynchronously, and billing/reporting operates from the appropriate
subscription-period pricing and daily usage data.
