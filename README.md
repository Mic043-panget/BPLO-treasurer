# Treasurer Workspace

A Laravel 13 / Blade interface prototype for the Treasurer's BPLO payment-review workflow. The visual patterns follow the linked BPLO, Admin, and Inspector portals: a municipal staff sidebar, restrained green accents, summary cards, data tables, status badges, and responsive navigation.

## Run locally

Requirements: PHP 8.3+, Composer, and Node.js/npm.

```powershell
composer install
npm install
npm run build
php artisan serve
```

Open `http://localhost:8000`.

## Screens

- `/treasurer/dashboard` — review summary, recent submissions, and activity.
- `/treasurer/payments` — searchable and filterable payment list with payment-date and status filters.
- `/treasurer/history` — payment submissions and Treasurer action history.
- `/treasurer/integration` — verified integration blockers and safe next steps.

Select **Review** on a payment to inspect its applicant, application, amount, fee breakdown, payment reference, and sample proof. The preview is read-only: it does not offer payment decisions or claim to persist a Treasurer audit history.

## Integration boundary

This workspace was scaffolded because the starting folder was empty. [`app/Support/TreasurerDemoData.php`](app/Support/TreasurerDemoData.php) is the isolated source for illustrative records and initial activity. No payment API calls, server mutations, or environment variables for a production integration are configured.

### Persistence audit

This app defaults to its own SQLite database (`DB_CONNECTION=sqlite` and Laravel's `database/database.sqlite` path). Its migrations create only framework users/cache/jobs tables; no Treasurer payment, decision, or audit tables exist, and its users migration has no role column. The screens currently load in-memory PHP fixture arrays from [`TreasurerDemoData.php`](app/Support/TreasurerDemoData.php), serialize them into the page, then filter/render them in JavaScript. There is no browser local/session storage or payment mutation endpoint. History is also fixture data, not a persisted audit log.

Proof metadata in fixtures is only a display cue for a generated sample receipt. No proof file is uploaded or stored by this app. The configured local private disk and optional S3 disk are not connected to the Applicant/BPLO proof store. No Treasurer role authentication or reviewer identity persistence is implemented.

Therefore, no real Treasurer, Applicant, payment, permit, or QR data is currently persisted here. Do not interpret the separate local SQLite database or demo fixtures as authoritative. See the [integration findings page](/treasurer/integration) for each upstream authority, known database arrangement, and blockers to safe persistence.

The inspected [BPLO repository](https://github.com/Aberte-m4rlon/BPLO) has no Treasurer payment API. Its payment review routes are BPLO-authorized, its manual gateway uses a BPLO confirmation source, and its payment model/service and core migration disagree on fields. Its rejection path maps to `FAILED` plus a reason rather than a persisted `REJECTED` payment status. The setup guide describes a shared Applicant database/private storage in some deployments, but the Applicant app, deployed schema, and Treasurer authentication arrangement are not verified.

The inspected [Admin dashboard](https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD) declares the Treasurer role and Admin fee/payment-method configuration code, but does not expose an API or confirmed shared data source. Its `.env.example` defaults to SQLite; its read-only settings page shows local `config('permit.*')`; and the checked-in migrations omit the `role`, fee, and application tables referenced by its models. The deployed database relationship and role persistence are therefore unknown. No Admin config is copied or editable in this preview. Amounts and methods in the fixture records remain illustrative and must not be used as official rates or allowed payment methods.

No Admin changes currently propagate to Treasurer. To avoid a false authority or divergent configuration, the preview does not implement a local fee/settings copy or role-based sign-in. Connect only after the actual shared user/database arrangement or a documented Admin/BPLO API is verified. For payment history, show the stored amount and fee breakdown snapshot from the authoritative BPLO payment record rather than recalculating it from current fee rates. Missing/stale/invalid configuration must block real review decisions rather than fall back to a sample or zero total. See the [integration findings page](/treasurer/integration) for field authority, evidence, and follow-up requirements.

The [Inspector system](https://github.com/maryvalerie/BPLO-Inspector-) documents shared permit records and QR verification history with Applicant and BPLO through their common database/private storage, but exposes no Treasurer API and does not establish that this Treasurer app shares that database. BPLO `PermitService::release` issues the permit only after `PAYMENT_VERIFIED`, generates the permit number/token/QR, and moves the application to `PERMIT_RELEASED`. The Inspector resolves the existing verification token and reports `VALID`, `EXPIRED`, `REVOKED`, or `NOT_FOUND`; these are permit-verification results, not payment statuses. This preview has no trusted permit source, so it displays no permit number, QR, or validation result and labels permit status unavailable. It cannot issue or change permits or update payment/application records. See the [integration findings page](/treasurer/integration) for the verified permit and QR fields and required linkage.

Do not connect to real records until the Applicant/deployed schema is confirmed and the BPLO system of record provides an agreed Treasurer authorization and decision contract. That contract must define identifiers, validation, atomic conflict and retry handling, stale-record behavior, audit attribution, rejection semantics, and private proof access. Keep payment verification separate from permit approval/release. The preview does not define an API endpoint, request fields, status values, or production environment variables.

## Validation

```powershell
php artisan test
npm run build
```
