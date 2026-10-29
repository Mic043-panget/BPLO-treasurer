const payments = window.treasurerDemoPayments;
const seedHistory = window.treasurerDemoHistory;
const app = document.querySelector('.app-shell');
const toastRegion = document.getElementById('toast-region');
const paymentDialog = document.getElementById('payment-dialog');
let selectedPayment = null;
let toastTimeout;
let paymentDialogOpener = null;

const money = new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 2,
});

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);
}

function formatDate(value, options = { month: 'short', day: 'numeric', year: 'numeric' }) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat('en', options).format(date);
}

function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return '—';
    return new Intl.DateTimeFormat('en', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    }).format(date);
}

function getStatus(payment) {
    if (payment.verificationStatus === 'VERIFIED' && payment.paymentStatus === 'PAID') {
        return { key: 'VERIFIED', label: 'Verified', className: 'status-verified' };
    }
    if (payment.paymentStatus === 'FAILED' && payment.rejectionReason) {
        return { key: 'REJECTED', label: 'Rejected', className: 'status-rejected' };
    }
    if (payment.paymentStatus === 'PROCESSING') {
        return { key: 'AWAITING', label: 'Awaiting review', className: 'status-awaiting' };
    }
    if (payment.paymentStatus === 'PENDING') {
        return { key: 'PENDING', label: 'Pending', className: 'status-pending' };
    }
    if (payment.paymentStatus === 'FAILED') {
        return { key: 'FAILED', label: 'Failed', className: 'status-failed' };
    }
    if (payment.paymentStatus === 'CANCELLED') {
        return { key: 'CANCELLED', label: 'Cancelled', className: 'status-cancelled' };
    }
    return { key: 'UNKNOWN', label: 'Unknown status', className: 'status-unknown' };
}

function statusBadge(payment) {
    const status = getStatus(payment);
    return `<span class="status-badge ${status.className}">${status.label}</span>`;
}

function showToast(message, type = 'success') {
    if (!toastRegion) return;
    window.clearTimeout(toastTimeout);
    toastRegion.replaceChildren();
    const toast = document.createElement('div');
    toast.className = `toast${type === 'error' ? ' error' : ''}`;
    toast.textContent = message;
    toastRegion.append(toast);
    toastTimeout = window.setTimeout(() => toast.remove(), 4800);
}

function currentPaymentRecords() {
    return payments;
}

function paymentRow(payment, recent = false) {
    const status = getStatus(payment);
    const firstCell = `<div class="person-cell"><strong>${escapeHtml(payment.applicant)}</strong><span>${escapeHtml(payment.business)}</span></div>`;
    const action = `<button type="button" class="table-action" data-open-payment="${escapeHtml(payment.id)}">Review</button>`;

    if (recent) {
        return `<tr>
            <td>${firstCell}</td>
            <td class="mono-cell">${escapeHtml(payment.trackingNumber)}</td>
            <td>${escapeHtml(payment.method)}</td>
            <td class="amount-cell">${money.format(payment.amount)}</td>
            <td>${formatDateTime(payment.submittedAt)}</td>
            <td>${statusBadge(payment)}</td>
            <td>${action}</td>
        </tr>`;
    }

    return `<tr>
        <td>${firstCell}</td>
        <td><div class="person-cell"><strong class="mono-cell">${escapeHtml(payment.trackingNumber)}</strong><span>${escapeHtml(payment.permitNumber || 'Permit status unavailable')}</span></div></td>
        <td class="mono-cell">${escapeHtml(payment.reference || '—')}</td>
        <td>${formatDate(payment.paymentDate)}</td>
        <td>${escapeHtml(payment.method)}</td>
        <td class="amount-cell">${money.format(payment.amount)}</td>
        <td>${statusBadge(payment)}</td>
        <td>${action}</td>
    </tr>`;
}

function awaitingPayments() {
    return currentPaymentRecords().filter((payment) => getStatus(payment).key === 'AWAITING');
}

function updateMetrics() {
    const records = currentPaymentRecords();
    const awaiting = records.filter((payment) => getStatus(payment).key === 'AWAITING');
    const verified = records.filter((payment) => getStatus(payment).key === 'VERIFIED');
    const attention = records.filter((payment) => ['REJECTED', 'FAILED'].includes(getStatus(payment).key));

    const setText = (id, value) => {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    };

    setText('metric-awaiting', String(awaiting.length));
    setText('metric-amount', money.format(awaiting.reduce((total, payment) => total + Number(payment.amount), 0)));
    setText('metric-verified', String(verified.length));
    setText('metric-attention', String(attention.length));
    setText('nav-pending-count', String(awaiting.length));
}

function renderDashboard() {
    const rows = document.getElementById('dashboard-payment-rows');
    if (rows) {
        const recent = awaitingPayments().slice(0, 4);
        rows.innerHTML = recent.map((payment) => paymentRow(payment, true)).join('');
        document.getElementById('dashboard-empty').hidden = recent.length > 0;
    }

    const activityList = document.getElementById('dashboard-activity-list');
    if (activityList) {
        const events = [...seedHistory]
            .sort((left, right) => new Date(right.when) - new Date(left.when))
            .slice(0, 4);
        activityList.innerHTML = events.map((event) => {
            const icon = event.kind === 'verified' ? '✓' : event.kind === 'rejected' ? '!' : '↗';
            const detail = event.reason || event.detail || event.trackingNumber;
            return `<div class="activity-row">
                <span class="activity-icon ${escapeHtml(event.kind)}" aria-hidden="true">${icon}</span>
                <span class="activity-copy"><strong>${escapeHtml(event.action)} · ${escapeHtml(event.paymentId)}</strong><span>${escapeHtml(detail)}</span></span>
                <time class="activity-time" datetime="${escapeHtml(event.when)}">${formatDateTime(event.when)}</time>
            </div>`;
        }).join('');
    }
}

function renderPayments() {
    const rows = document.getElementById('payment-rows');
    if (!rows) return;

    const search = document.getElementById('payment-search').value.trim().toLocaleLowerCase();
    const status = document.getElementById('status-filter').value;
    const date = document.getElementById('date-filter').value;
    const filtered = currentPaymentRecords().filter((payment) => {
        const searchable = [
            payment.applicant,
            payment.business,
            payment.trackingNumber,
            payment.permitNumber,
            payment.reference,
            payment.id,
        ].filter(Boolean).join(' ').toLocaleLowerCase();
        return (!search || searchable.includes(search))
            && (!status || getStatus(payment).key === status)
            && (!date || payment.paymentDate === date);
    });

    rows.innerHTML = filtered.map((payment) => paymentRow(payment)).join('');
    document.getElementById('payment-result-count').textContent = String(filtered.length);
    document.getElementById('payment-empty').hidden = filtered.length > 0;
    rows.closest('.data-table').hidden = filtered.length === 0;
}

function renderHistory() {
    const rows = document.getElementById('history-rows');
    if (!rows) return;
    const events = [...seedHistory]
        .sort((left, right) => new Date(right.when) - new Date(left.when));
    rows.innerHTML = events.map((event) => `<tr>
        <td>${formatDateTime(event.when)}</td>
        <td>${escapeHtml(event.actor)}</td>
        <td><span class="event-tag ${escapeHtml(event.kind)}">${escapeHtml(event.action)}</span></td>
        <td class="mono-cell">${escapeHtml(event.paymentId)}</td>
        <td class="mono-cell">${escapeHtml(event.trackingNumber)}</td>
        <td class="history-details">${escapeHtml(event.reason || event.detail)}</td>
    </tr>`).join('');
    document.getElementById('history-result-count').textContent = `${events.length} ${events.length === 1 ? 'event' : 'events'}`;
    document.getElementById('history-empty').hidden = events.length > 0;
    rows.closest('.data-table').hidden = events.length === 0;
}

function render() {
    updateMetrics();
    renderDashboard();
    renderPayments();
    renderHistory();
}

function openPayment(paymentId, trigger) {
    selectedPayment = currentPaymentRecords().find((payment) => payment.id === paymentId);
    if (!selectedPayment) {
        showToast('That demo payment record is no longer available.', 'error');
        return;
    }
    paymentDialogOpener = trigger;

    const payment = selectedPayment;
    const status = getStatus(payment);
    document.getElementById('detail-payment-id').textContent = payment.id;
    document.getElementById('detail-status').innerHTML = statusBadge(payment);
    document.getElementById('detail-submitted').textContent = `Submitted ${formatDateTime(payment.submittedAt)}`;
    document.getElementById('detail-applicant').textContent = payment.applicant;
    document.getElementById('detail-business').textContent = payment.business;
    document.getElementById('detail-address').textContent = payment.address;
    document.getElementById('detail-tracking').textContent = payment.trackingNumber;
    document.getElementById('detail-permit').textContent = payment.permitNumber || 'Unavailable — no trusted permit record connected';
    document.getElementById('detail-amount').textContent = money.format(payment.amount);
    document.getElementById('detail-method').textContent = payment.method;
    document.getElementById('detail-reference').textContent = payment.reference || 'Not submitted';
    document.getElementById('detail-date').textContent = formatDate(payment.paymentDate);
    document.getElementById('detail-payer').textContent = [payment.payerName, payment.payerNumber].filter(Boolean).join(' · ') || 'Not provided';
    document.getElementById('detail-total').textContent = money.format(payment.amount);

    document.getElementById('detail-breakdown').innerHTML = (payment.breakdown || []).map((fee) => `<div class="fee-line"><span>${escapeHtml(fee.label)}</span><strong>${money.format(fee.amount)}</strong></div>`).join('');

    const receipt = document.getElementById('receipt-preview');
    if (payment.proofName) {
        receipt.innerHTML = `<div class="receipt-paper" aria-label="Illustrative sample payment receipt">
            <div class="receipt-brand"><span>MUNICIPAL PAYMENT RECEIPT</span><span class="receipt-demo-mark">SAMPLE</span></div>
            <div class="receipt-line"><span>Paid to</span><strong>Municipal Treasurer (demo)</strong></div>
            <div class="receipt-line"><span>Reference</span><strong>${escapeHtml(payment.proofReference || payment.reference || '—')}</strong></div>
            <div class="receipt-line"><span>Payment date</span><strong>${formatDate(payment.paymentDate)}</strong></div>
            <div class="receipt-line"><span>Amount</span><strong>${money.format(payment.amount)}</strong></div>
            <span class="receipt-paid">DEMO COPY</span>
        </div>`;
    } else {
        receipt.innerHTML = '<div class="receipt-missing"><span aria-hidden="true">▧</span><strong>No proof attached to this sample record</strong><span>In production, show an authorized private-file preview.</span></div>';
    }

    document.getElementById('payment-actions').innerHTML = '<button class="button button-secondary" type="button" data-close-dialog="payment-dialog">Close</button>';

    paymentDialog.showModal();
}

function clearFilters() {
    const search = document.getElementById('payment-search');
    if (!search) return;
    search.value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('date-filter').value = '';
    renderPayments();
    search.focus();
}

function initialize() {
    if (!app || !Array.isArray(payments) || !Array.isArray(seedHistory)) {
        const message = 'Payment data could not be loaded. Refresh the demo or check the configured data source.';
        console.error(message, { payments, seedHistory });
        const container = document.querySelector('.content');
        if (container) {
            const error = document.createElement('div');
            error.className = 'load-error';
            error.setAttribute('role', 'alert');
            error.textContent = message;
            container.prepend(error);
        }
        return;
    }

    app.setAttribute('aria-busy', 'true');
    const paymentBody = document.getElementById('payment-rows') || document.getElementById('dashboard-payment-rows');
    if (paymentBody) {
        paymentBody.innerHTML = '<tr class="loading-row" aria-label="Loading demo payments"><td><span class="skeleton"></span></td><td><span class="skeleton"></span></td><td><span class="skeleton"></span></td><td><span class="skeleton"></span></td><td><span class="skeleton"></span></td><td><span class="skeleton"></span></td><td><span class="skeleton"></span></td></tr>';
    }

    window.setTimeout(() => {
        try {
            render();
            app.removeAttribute('aria-busy');
        } catch (error) {
            console.error('Unable to render the Treasurer demo workspace:', error);
            const container = document.querySelector('.content');
            if (container) {
                const message = document.createElement('div');
                message.className = 'load-error';
                message.setAttribute('role', 'alert');
                message.textContent = 'The demo workspace could not be displayed. Refresh the page to try again.';
                container.prepend(message);
            }
            app.removeAttribute('aria-busy');
        }
    }, 220);
}

document.addEventListener('click', (event) => {
    const target = event.target instanceof Element ? event.target : null;
    if (!target) return;

    const openButton = target.closest('[data-open-payment]');
    if (openButton) {
        openPayment(openButton.dataset.openPayment, openButton);
        return;
    }

    const closeButton = target.closest('[data-close-dialog]');
    if (closeButton) {
        const dialog = document.getElementById(closeButton.dataset.closeDialog);
        if (dialog?.open) dialog.close();
        return;
    }

    if (target.closest('#clear-filters, #empty-clear-filters')) clearFilters();
});

document.getElementById('payment-search')?.addEventListener('input', renderPayments);
document.getElementById('status-filter')?.addEventListener('change', renderPayments);
document.getElementById('date-filter')?.addEventListener('change', renderPayments);

document.getElementById('dismiss-notice')?.addEventListener('click', () => {
    document.querySelector('.demo-notice')?.remove();
});

const sidebar = document.getElementById('sidebar');
const menuButton = document.getElementById('mobile-menu-button');
const sidebarScrim = document.getElementById('sidebar-scrim');

function closeSidebar() {
    sidebar?.classList.remove('is-open');
    sidebarScrim.hidden = true;
    menuButton?.setAttribute('aria-expanded', 'false');
    menuButton?.setAttribute('aria-label', 'Open navigation');
    menuButton?.focus();
}

menuButton?.addEventListener('click', () => {
    const isOpen = sidebar.classList.toggle('is-open');
    sidebarScrim.hidden = !isOpen;
    menuButton.setAttribute('aria-expanded', String(isOpen));
    menuButton.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
});
document.getElementById('sidebar-close-button')?.addEventListener('click', closeSidebar);
sidebarScrim?.addEventListener('click', closeSidebar);
sidebar?.querySelectorAll('a').forEach((link) => link.addEventListener('click', closeSidebar));

document.addEventListener('keydown', (event) => {
    if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
        const search = document.getElementById('payment-search');
        if (search) {
            event.preventDefault();
            search.focus();
        }
    }
    if (event.key === 'Escape' && sidebar?.classList.contains('is-open')) closeSidebar();
});

paymentDialog?.addEventListener('click', (event) => {
    if (event.target === paymentDialog) paymentDialog.close();
});
paymentDialog?.addEventListener('close', () => {
    selectedPayment = null;
    paymentDialogOpener?.focus();
    paymentDialogOpener = null;
});

initialize();
