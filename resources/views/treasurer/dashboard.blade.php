<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ucfirst($activePage) }} · Treasurer workspace</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <div class="sidebar-scrim" id="sidebar-scrim" hidden></div>
        <aside class="sidebar" id="sidebar" aria-label="Main navigation">
            <a class="brand" href="{{ route('treasurer.dashboard') }}">
                <span class="brand-mark" aria-hidden="true">B</span>
                <span class="brand-copy"><strong>BPLO</strong><small>Municipal services</small></span>
            </a>
            <button class="icon-button sidebar-close-button" id="sidebar-close-button" type="button" aria-label="Close navigation">×</button>

            <div class="workspace-label">TREASURER WORKSPACE</div>
            <nav class="primary-nav">
                <a class="nav-link {{ $activePage === 'dashboard' ? 'is-active' : '' }}" href="{{ route('treasurer.dashboard') }}" @if ($activePage === 'dashboard') aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="7" height="8" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="11" width="7" height="10" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/></svg>
                    <span>Overview</span>
                </a>
                <a class="nav-link {{ $activePage === 'payments' ? 'is-active' : '' }}" href="{{ route('treasurer.payments') }}" @if ($activePage === 'payments') aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h4"/></svg>
                    <span>Payments</span>
                    <span class="nav-count" id="nav-pending-count">—</span>
                </a>
                <a class="nav-link {{ $activePage === 'history' ? 'is-active' : '' }}" href="{{ route('treasurer.history') }}" @if ($activePage === 'history') aria-current="page" @endif>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 2.64-6.36L3 8"/><path d="M3 3v5h5m4-1v5l3 2"/></svg>
                    <span>Action history</span>
                </a>
            </nav>

            <div class="sidebar-bottom">
                <div class="help-card">
                    <span class="help-icon" aria-hidden="true">?</span>
                    <strong>Need a hand?</strong>
                    <p>Payment decisions should match the municipal transaction record.</p>
                </div>
                <div class="sidebar-user">
                    <span class="avatar">TD</span>
                    <span class="sidebar-user-copy"><strong>Treasurer Demo</strong><small>Demo workspace</small></span>
                    <span class="online-dot" aria-label="Demo mode"></span>
                </div>
            </div>
        </aside>

        <main class="main-area">
            <header class="topbar">
                <button class="icon-button mobile-menu-button" id="mobile-menu-button" type="button" aria-label="Open navigation" aria-expanded="false" aria-controls="sidebar">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="breadcrumbs"><span>Municipal BPLO</span><span class="crumb-separator">/</span><strong>Treasurer</strong></div>
                <div class="topbar-actions">
                    <span class="demo-pill"><span></span> Demo data</span>
                    <span class="topbar-date">{{ now()->format('l, F j, Y') }}</span>
                    <span class="avatar avatar-small" aria-label="Treasurer Demo">TD</span>
                </div>
            </header>

            <div class="content">
                <div class="demo-notice" role="note">
                    <span class="notice-symbol" aria-hidden="true">i</span>
                    <p><strong>Read-only preview.</strong> All records are illustrative sample data. No shared Admin/BPLO Treasurer sign-in, payment API, or configuration connection is verified, so payment decisions are disabled.</p>
                    <button class="notice-dismiss" id="dismiss-notice" type="button" aria-label="Dismiss demo notice">×</button>
                </div>

                @if ($activePage === 'dashboard')
                    <section class="page-heading">
                        <div>
                            <p class="eyebrow">PAYMENT OPERATIONS</p>
                            <h1>Treasurer workspace</h1>
                            <p class="page-description">Review illustrative payment records while integration is pending.</p>
                        </div>
                        <a class="button button-primary" href="{{ route('treasurer.payments') }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
                            Review payments
                        </a>
                    </section>

                    <section class="metric-grid" aria-label="Payment summary">
                        <article class="metric-card metric-primary">
                            <div class="metric-top"><span class="metric-label">Awaiting review</span><span class="metric-icon icon-amber"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span></div>
                            <strong class="metric-value" id="metric-awaiting">—</strong>
                            <span class="metric-note">Submitted for verification</span>
                        </article>
                        <article class="metric-card">
                            <div class="metric-top"><span class="metric-label">Sample awaiting amount</span><span class="metric-icon icon-blue"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2v20m5-15.5C17 4.6 14.8 3.5 12 3.5s-5 1.2-5 3.2c0 5 10 2.4 10 7.4 0 2.1-2.2 3.4-5 3.4s-5-1.2-5-3.4"/></svg></span></div>
                            <strong class="metric-value" id="metric-amount">—</strong>
                            <span class="metric-note">Across submitted payments</span>
                        </article>
                        <article class="metric-card">
                            <div class="metric-top"><span class="metric-label">Verified this month</span><span class="metric-icon icon-green"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"/></svg></span></div>
                            <strong class="metric-value" id="metric-verified">—</strong>
                            <span class="metric-note">Separate BPLO workflow · sample data</span>
                        </article>
                        <article class="metric-card">
                            <div class="metric-top"><span class="metric-label">Needs attention</span><span class="metric-icon icon-red"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 2.8 19h18.4L12 3Z"/><path d="M12 9v4m0 3h.01"/></svg></span></div>
                            <strong class="metric-value" id="metric-attention">—</strong>
                            <span class="metric-note">Rejected or failed payments</span>
                        </article>
                    </section>

                    <section class="panel">
                        <div class="panel-heading">
                            <div><h2>Payments to review</h2><p>Illustrative records only. Amounts and methods are not connected to Admin configuration.</p></div>
                            <a class="text-link" href="{{ route('treasurer.payments') }}">View all payments <span aria-hidden="true">→</span></a>
                        </div>
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead><tr><th>Applicant / business</th><th>Application</th><th>Sample method</th><th>Sample amount</th><th>Submitted</th><th>Status</th><th aria-label="Action"></th></tr></thead>
                                <tbody id="dashboard-payment-rows"></tbody>
                            </table>
                            <div class="empty-state" id="dashboard-empty" hidden><span class="empty-icon">✓</span><strong>All caught up</strong><p>There are no submitted payments waiting for review.</p></div>
                        </div>
                    </section>

                    <section class="panel history-panel">
                        <div class="panel-heading">
                            <div><h2>Sample activity</h2><p>Illustrative payment submissions and existing workflow events.</p></div>
                            <a class="text-link" href="{{ route('treasurer.history') }}">Full action history <span aria-hidden="true">→</span></a>
                        </div>
                        <div class="activity-list" id="dashboard-activity-list"></div>
                    </section>
                @elseif ($activePage === 'payments')
                    <section class="page-heading page-heading-compact">
                        <div><p class="eyebrow">PAYMENT OPERATIONS</p><h1>Payment review</h1><p class="page-description">Find an illustrative record and inspect its sample application and payment details.</p></div>
                    </section>

                    <section class="filter-panel" aria-label="Filter payments">
                        <label class="search-field">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 4 4"/></svg>
                            <span class="sr-only">Search applicant, application, permit, or reference</span>
                            <input id="payment-search" type="search" placeholder="Search applicant, application, permit…" autocomplete="off">
                            <kbd>⌘ K</kbd>
                        </label>
                        <label class="filter-control"><span>Status</span><select id="status-filter"><option value="">All statuses</option><option value="AWAITING">Awaiting review</option><option value="VERIFIED">Verified</option><option value="REJECTED">Rejected</option><option value="PENDING">Pending</option><option value="FAILED">Failed</option><option value="CANCELLED">Cancelled</option></select></label>
                        <label class="filter-control date-filter"><span>Payment date</span><input id="date-filter" type="date" aria-label="Filter by payment date"></label>
                        <button class="button button-quiet" id="clear-filters" type="button">Clear filters</button>
                    </section>

                    <section class="panel">
                        <div class="panel-heading payment-list-heading">
                            <div><h2>All payments <span class="heading-count" id="payment-result-count">—</span></h2><p>Sample records only; amounts and payment methods are not authoritative Admin configuration.</p></div>
                            <span class="legend-note"><span class="status-dot dot-amber"></span> Awaiting Treasurer review</span>
                        </div>
                        <div class="table-scroll">
                            <table class="data-table">
                                <thead><tr><th>Applicant / business</th><th>Application / permit status</th><th>Reference</th><th>Payment date</th><th>Sample method</th><th>Sample amount</th><th>Status</th><th aria-label="Action"></th></tr></thead>
                                <tbody id="payment-rows"></tbody>
                            </table>
                            <div class="empty-state" id="payment-empty" hidden><span class="empty-icon">⌕</span><strong>No payments match</strong><p>Try changing your search or clearing the filters.</p><button class="button button-secondary" id="empty-clear-filters" type="button">Clear filters</button></div>
                        </div>
                    </section>
                @else
                    <section class="page-heading page-heading-compact">
                        <div><p class="eyebrow">ACCOUNTABILITY</p><h1>Action history</h1><p class="page-description">Illustrative history only; this preview does not record Treasurer decisions.</p></div>
                    </section>

                    <div class="history-notice"><span aria-hidden="true">i</span><p>This is sample history, not an audit log. Decision history requires an authenticated Treasurer identity and a server-side BPLO audit transaction.</p></div>
                    <section class="panel">
                        <div class="panel-heading"><div><h2>Payment activity</h2><p>Most recent activity first.</p></div><span class="history-count" id="history-result-count">— events</span></div>
                        <div class="table-scroll">
                            <table class="data-table history-table">
                                <thead><tr><th>Date &amp; time</th><th>Actor</th><th>Action</th><th>Payment</th><th>Application</th><th>Details</th></tr></thead>
                                <tbody id="history-rows"></tbody>
                            </table>
                            <div class="empty-state" id="history-empty" hidden><span class="empty-icon">◷</span><strong>No activity in this session</strong><p>Payment submissions and decisions will appear here.</p></div>
                        </div>
                    </section>
                @endif

                <footer class="page-footer"><span>Municipal BPLO · Treasurer workspace</span><span>Interface preview · Integration pending</span></footer>
            </div>
        </main>
    </div>

    <dialog class="payment-dialog" id="payment-dialog" aria-labelledby="dialog-title">
        <div class="dialog-shell">
            <div class="dialog-header"><div><p class="eyebrow">PAYMENT RECORD</p><h2 id="dialog-title">Payment details</h2><span class="dialog-id" id="detail-payment-id"></span></div><button class="icon-button dialog-close" type="button" data-close-dialog="payment-dialog" aria-label="Close payment details">×</button></div>
            <div class="dialog-scroll">
                <div class="detail-status-line"><span id="detail-status"></span><span id="detail-submitted"></span></div>
                <div class="detail-columns">
                    <section class="detail-card"><h3>Applicant &amp; application</h3><dl class="detail-list"><div><dt>Applicant</dt><dd id="detail-applicant"></dd></div><div><dt>Business</dt><dd id="detail-business"></dd></div><div><dt>Business address</dt><dd id="detail-address"></dd></div><div><dt>Application no. · sample</dt><dd id="detail-tracking"></dd></div><div><dt>Permit / QR status</dt><dd id="detail-permit"></dd></div></dl></section>
                    <section class="detail-card"><h3>Submitted payment · sample</h3><dl class="detail-list"><div><dt>Sample amount</dt><dd id="detail-amount" class="detail-amount"></dd></div><div><dt>Sample method</dt><dd id="detail-method"></dd></div><div><dt>Reference number</dt><dd id="detail-reference"></dd></div><div><dt>Payment date</dt><dd id="detail-date"></dd></div><div><dt>Payer</dt><dd id="detail-payer"></dd></div></dl></section>
                </div>
                <section class="detail-card fee-card"><h3>Illustrative fee breakdown</h3><div id="detail-breakdown"></div><div class="fee-total"><span>Sample total</span><strong id="detail-total"></strong></div></section>
                <section class="proof-section"><div class="proof-heading"><div><h3>Proof of payment</h3><p>Applicant-submitted receipt · demo preview</p></div><span class="file-type">IMAGE</span></div><div class="receipt-preview" id="receipt-preview"></div></section>
                <div class="integration-callout"><span aria-hidden="true">i</span><p><strong>Read-only preview:</strong> proof is illustrative and cannot establish that funds were received. Permit and QR status require the trusted BPLO-issued permit record; none is connected here. Treasurer cannot issue or change permits. See <a href="{{ route('treasurer.integration') }}">integration findings</a>.</p></div>
            </div>
            <div class="dialog-actions" id="payment-actions"></div>
        </div>
    </dialog>

    <div class="toast-region" id="toast-region" role="status" aria-live="polite"></div>

    <script>
        window.treasurerDemoPayments = {{ \Illuminate\Support\Js::from($payments) }};
        window.treasurerDemoHistory = {{ \Illuminate\Support\Js::from($history) }};
    </script>
</body>
</html>
