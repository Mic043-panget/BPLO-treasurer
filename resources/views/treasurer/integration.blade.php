<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Integration findings · Treasurer workspace</title>
    @vite(['resources/css/app.css'])
</head>
<body>
    <main class="content" style="max-width: 1000px; padding-top: 40px">
        <p class="eyebrow">BPLO INTEGRATION</p>
        <h1>Integration findings</h1>
        <p class="page-description">Verified against the public <a href="https://github.com/Aberte-m4rlon/BPLO">BPLO repository</a>. These findings are a boundary assessment, not an API specification.</p>

        <section class="panel" style="margin-top: 24px; padding: 24px">
            <h2>No Treasurer API or authorized sign-in flow</h2>
            <p>The inspected BPLO routes provide web workflows, not a Treasurer payment API. Staff routes are protected by authenticated BPLO role middleware. Although the user model defines a Treasurer role and dashboard destination, the inspected login flow accepts active BPLO users, and payment routes remain BPLO-only. Separate application sessions are not established by the shared-database setup notes.</p>
            <p>The manual payment gateway accepts a BPLO confirmation source. This does not authorize a separate Treasurer application to make decisions.</p>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Payment status and schema mismatch</h2>
            <p>The Payment model defines <code>PENDING</code>, <code>PROCESSING</code>, <code>PAID</code>, <code>FAILED</code>, and <code>CANCELLED</code>; verification is separately <code>UNVERIFIED</code> or <code>VERIFIED</code>. There is no persisted <code>REJECTED</code> status. The current rejection path records a reason, marks payment failed, and may return the application to <code>APPROVED_FOR_PAYMENT</code>. A rejection time is not stored by that path.</p>
            <p><strong>Schema mismatch:</strong> the inspected core payments migration omits payer, proof, and rejection-reason columns referenced by the model/service, as well as some other payment fields. The deployed database and any additional migrations have not been verified. A UI label derived from <code>FAILED</code> plus a rejection reason is only a display mapping, not a new BPLO status.</p>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Persistence and audit are not Treasurer integration</h2>
            <p>BPLO setup documentation describes sharing the Applicant database and private storage in some deployments. This does not establish that the Applicant app, deployed schema, or Treasurer credentials are available here. Payment transaction and audit-log models exist, but the inspected workflow does not provide the required Treasurer authorization, atomic decision/idempotency guarantees, or a confirmed Treasurer audit contract.</p>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Current Treasurer persistence audit</h2>
            <ul>
                <li><strong>Database configured here:</strong> Laravel's local <code>DB_CONNECTION=sqlite</code> default uses this app's <code>database/database.sqlite</code>. Migration status contains only framework users, cache, and jobs tables; there are no Treasurer payments, decision, or audit tables. The local users migration has no role field.</li>
                <li><strong>Payment queue and records:</strong> not database-backed. <code>app/Support/TreasurerDemoData.php</code> returns hard-coded sample arrays, passed by <code>DashboardController</code> into Blade and serialized into <code>window.treasurerDemoPayments</code>. Filtering and rendering happen in the browser.</li>
                <li><strong>Decisions and audit history:</strong> there is no decision write route or persistent decision store. The browser decision simulation has been removed. The action-history screen reads the hard-coded sample history from <code>TreasurerDemoData::history()</code>; it is not an audit log.</li>
                <li><strong>Proof files:</strong> no upload endpoint, file write, or stored file exists in this app. Fixture <code>proofName</code> fields only drive an illustrative receipt drawing in JavaScript. Laravel's default local filesystem points at this app's private storage; the S3 disk is configurable, but neither is connected to Applicant/BPLO proof storage.</li>
                <li><strong>Authentication and reviewer identity:</strong> no login or role middleware protects these GET screens, and no reviewer identity is persisted. This app's database users table does not establish a shared identity source.</li>
            </ul>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Inspector permit and QR contract</h2>
            <p>The Inspector <a href="https://github.com/maryvalerie/BPLO-Inspector-">repository</a> documents that Inspector, Applicant, and BPLO share permit records and QR history through a common database and private storage. Its setup example points local Inspector to the Applicant SQLite database; separate deployments require all three apps to use the same hosted MySQL database and private storage. The documented sharing does not include this Treasurer app, and no Inspector API is exposed in its routes.</p>
            <p>BPLO's <code>PermitService::release</code> guards on application status <code>PAYMENT_VERIFIED</code>, creates the permit and a unique verification token, generates the QR for the public verification URL, stores the PDF, then transitions the application to <code>PERMIT_RELEASED</code>. This is the issuance authority. Treasurer must neither create permit numbers/tokens/QRs nor modify permit status.</p>
            <p>The permit schema ties one permit to its exact <code>application_id</code> and stores <code>permit_number</code>, <code>status</code> (<code>ACTIVE</code>/<code>REVOKED</code>), <code>issued_at</code>, <code>valid_until</code>, and a private verification token. QR verification resolves the token against the permit and records <code>permit_id</code>, <code>token</code>, <code>result</code>, timestamp, and scan metadata. Its public result is <code>VALID</code>, <code>EXPIRED</code>, <code>REVOKED</code>, or <code>NOT_FOUND</code>, determined by permit existence, status, and expiry. These results are not payment statuses. The Treasurer preview does not store or expose a token, QR image, or verification result.</p>
            <p>For a future trusted read-only permit display, require the exact BPLO <code>application_id</code> / permit relation and the upstream <code>permit_number</code>, <code>status</code>, <code>issued_at</code>, and <code>valid_until</code>. Obtain QR/validity result only from the existing verification authority using the original token flow; do not reconstruct a token or infer a permit from a name, payment reference, or payment status. Never expose the token in Treasurer-facing HTML, logs, or audit metadata. Any payment decision, once supported upstream, must address the exact BPLO payment <code>id</code> and <code>application_id</code> relationship; the current preview cannot write either record.</p>
            <p>Because this app has no verified connection to those permit records, the payment table shows permit status as unavailable rather than “not issued”; no fixture permit number, permit status, QR, or scan result is presented as real.</p>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Admin dashboard findings</h2>
            <p>The inspected <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD">Admin repository</a> contains no API routes or Treasurer-facing API contract; its <code>routes/web.php</code> defines browser routes. Its <code>.env.example</code> defaults to SQLite and provides no shared Treasurer API/database connection. Its settings screen displays local <code>config('permit.*')</code> values read-only; payment mode, enabled methods, and account settings are local app configuration, not remotely exposed settings.</p>
            <p>The Admin code declares <code>ROLE_TREASURER</code> and includes Treasurer in the user-management role choices. However, the checked-in users migration does not include a <code>role</code> column (nor several user fields used by its model/controller), so actual role persistence in the deployed system is unverified. Admin routes are grouped by authentication; the dashboard redirect explicitly checks Admin, but the inspected route group does not apply an Admin-role middleware to every admin route.</p>
            <p>Admin fee computation reads active <code>fee_rates</code> joined to active <code>fee_types</code>. Those tables and the associated application tables have models/controllers but no migrations in the inspected repository; the included migrations create only framework tables. The deployed database and whether it is shared with the BPLO payment system are unknown. The Admin code itself logs a warning and returns a zero fee total when there are no active rates; that fallback must not be interpreted by Treasurer as an authoritative zero charge.</p>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Field authority and current handling</h2>
            <ul>
                <li><strong>Treasurer role:</strong> Admin <code>User</code> model/user-management code declares the role, but production persistence and shared identity are not confirmed. This preview authenticates nobody and grants no decision permissions.</li>
                <li><strong>Fee schedule:</strong> Admin <code>FeeRate</code>/<code>FeeType</code> plus <code>FeeComputationService</code> is the source used by that repository's computation code. Shared production authority is unverified; Treasurer does not duplicate, read, or edit rates.</li>
                <li><strong>Amount for an existing payment:</strong> BPLO's payment record <code>amount</code> and <code>breakdown_json</code> are the relevant stored values to show when a verified payment source is connected. Do not recalculate a submitted payment from current rates. This preview displays fixtures only.</li>
                <li><strong>Payment methods/settings:</strong> Admin's <code>PaymentMethods</code> reads local <code>config('permit.payment.*')</code>; its settings page is read-only. The Treasurer preview has no verified access to those values and its fixture methods are not an allowed-method list.</li>
                <li><strong>Payment status:</strong> BPLO and Admin payment models declare the same payment and verification statuses. The existing BPLO workflow remains authoritative for transitions; this preview maps sample statuses for display only and cannot transition them.</li>
                <li><strong>Requirements and operational rules:</strong> Admin has management screens, but no verified feed/API or shared production source to Treasurer. No review policy or status-transition rule is configurable here.</li>
            </ul>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Configuration propagation and failure handling</h2>
            <p><strong>Current propagation: none.</strong> Admin changes remain in the Admin app's local configuration or database; no verified API, shared production database, cache-invalidation mechanism, or deployment contract delivers them to this Treasurer app. Admin-managed fees and settings are not editable here. The displayed fixtures are clearly labeled samples, not copied Admin configuration.</p>
            <p>Until the real connection is verified, missing or invalid Admin configuration must be treated as unavailable—not guessed from fixtures, defaulted to zero, or replaced with a local setting. A future adapter must fail closed for fee/payment-method policy and block review decisions when required config or payment data is stale/unavailable. No webhook secret or other Admin secret is copied or exposed here.</p>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Admin repository evidence</h2>
            <ul>
                <li><a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/routes/web.php">routes/web.php</a> — web routes, authenticated Admin area, no API routes.</li>
                <li><a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Models/User.php">User.php</a>, <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Http/Controllers/Admin/UserController.php">UserController.php</a>, and <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/database/migrations/0001_01_01_000000_create_users_table.php">users migration</a> — role constants/choices and the checked-in user schema.</li>
                <li><a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Services/FeeComputationService.php">FeeComputationService.php</a>, <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Models/FeeRate.php">FeeRate.php</a>, and <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Models/FeeType.php">FeeType.php</a> — fee computation source.</li>
                <li><a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Support/PaymentMethods.php">PaymentMethods.php</a>, <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/config/permit.php">config/permit.php</a>, and <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/app/Http/Controllers/Admin/SettingController.php">SettingController.php</a> — local payment configuration and read-only settings.</li>
                <li><a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/blob/4114cf63dd22c7527202e0eb1d9ace410c414504/.env.example">.env.example</a> and <a href="https://github.com/azaiburieldomingo-dev/BPLO-ADMIN-DASHBOARD/tree/4114cf63dd22c7527202e0eb1d9ace410c414504/database/migrations">database/migrations</a> — default database configuration and included migration inventory.</li>
            </ul>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Repository evidence</h2>
            <ul>
                <li><a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/routes/web.php">routes/web.php</a> and <a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/app/Http/Controllers/Auth/LoginController.php">LoginController.php</a> — exposed web routes and login flow.</li>
                <li><a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/app/Http/Middleware/EnsureUserHasRole.php">EnsureUserHasRole.php</a> and <a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/app/Models/User.php">User.php</a> — role authorization and defined roles.</li>
                <li><a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/app/Models/Payment.php">Payment.php</a>, <a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/app/Services/PaymentService.php">PaymentService.php</a>, and <a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/app/Services/ManualGateway.php">ManualGateway.php</a> — current payment states and decision behavior.</li>
                <li><a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/database/migrations/2025_01_01_000010_create_payments_table.php">payments migration</a>, <a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/database/migrations/2025_01_01_000015_create_audit_logs_table.php">audit migration</a>, and <a href="https://github.com/Aberte-m4rlon/BPLO/blob/main/SETUP.md">SETUP.md</a> — core schema, audit storage, and documented deployment arrangement.</li>
                <li><a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/SETUP.md">Inspector SETUP.md</a>, <a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/routes/web.php">routes/web.php</a>, <a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/app/Models/Permit.php">Permit.php</a>, and <a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/app/Models/QrVerification.php">QrVerification.php</a> — documented shared-storage setup, Inspector/public routes, and permit/scan fields.</li>
                <li><a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/app/Services/PermitService.php">PermitService.php</a>, <a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/app/Http/Controllers/PublicPages/VerificationController.php">VerificationController.php</a>, and <a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/database/migrations/2025_01_01_000012_create_permits_table.php">permit migration</a> / <a href="https://github.com/maryvalerie/BPLO-Inspector-/blob/f420ad7e1f2c033839e5b20371d4eb00f1d01013/database/migrations/2025_01_01_000013_create_qr_verifications_table.php">QR migration</a> — issuance, QR verification outcomes, and persisted fields.</li>
            </ul>
        </section>

        <section class="panel" style="margin-top: 16px; padding: 24px">
            <h2>Safe next steps</h2>
            <ol>
                <li>Confirm the actual Applicant application, deployed schema/migrations, and private proof-file access rules.</li>
                <li>Confirm whether Admin and BPLO use the same production user/fee/settings database, and reconcile the checked-in migration gaps against deployed schema.</li>
                <li>In the BPLO system of record, agree how Treasurer identities authenticate and are authorized; do not rely on shared database access or applicant names.</li>
                <li>Resolve schema discrepancies and explicitly agree on the persisted rejection representation before changing workflow behavior. Keep Admin fee/settings write authorization in Admin.</li>
                <li>Define and document the BPLO-owned decision contract, including identifier, validation, concurrency/idempotency, audit, and retry/stale-record behavior; then implement and test the mutation in BPLO.</li>
                <li>Only after those sources and contracts are verified, replace preview values with authenticated read-only Admin/BPLO adapters. Keep payment verification separate from permit approval and release.</li>
            </ol>
            <p>No endpoint, request field, environment variable, fee setting, role integration, or production payment decision has been invented or added by this preview.</p>
        </section>

        <p style="margin-top: 24px"><a class="button button-secondary" href="{{ route('treasurer.dashboard') }}">Back to Treasurer workspace</a></p>
    </main>
</body>
</html>
