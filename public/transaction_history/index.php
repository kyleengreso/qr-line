<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
@include_once __DIR__ . '/../includes/config.php';

// `base.php` normalizes the token into `$token` (stdClass) when a cookie exists.
// Use that normalized token if available; read properties defensively to avoid
// PHP notices when certain claims are missing.
if (!isset($token) || !$token) {
    $token = null;
}

$id = isset($token->id) ? $token->id : null;
$username = isset($token->username) ? $token->username : null;
// Prefer role from token, fallback to role_type cookie (set during set_token)
$role_type = isset($token->role_type) ? $token->role_type : (isset($_COOKIE['role_type']) ? $_COOKIE['role_type'] : null);
$email = isset($token->email) ? $token->email : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Transaction History | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
    <style>
        /* Modern transaction table visual refresh */
        .transactions-toolbar {
            display: flex;
            gap: .75rem;
            align-items: center;
            flex-wrap: wrap;
        }
        .transactions-toolbar .flex-fill { min-width: 220px; }
        .transactions-card { border-radius: 18px; }
        .transactions-table thead th {
            position: sticky;
            top: 0;
            background: var(--bs-white, #fff);
            z-index: 2;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .transactions-table tbody tr {
            border-bottom: 1px solid rgba(0,0,0,0.03);
            transition: background-color .12s ease-in-out;
        }
        .transactions-table tbody tr:hover { background-color: rgba(0,0,0,0.03); }
    /* compact row spacing */
    .transactions-table td, .transactions-table th { vertical-align: middle; padding: .45rem .6rem; font-size: .92rem; }
    .transactions-table tbody tr { height: auto; }
    .token-col { width: 110px; white-space: nowrap; }
    .time-col { width: 150px; white-space: nowrap; }
    .txn-col { width: 100px; }
    .actions-col { width: 120px; }
    .actions-col .btn { padding: .25rem .5rem; font-size: .82rem; }
    .dropdown-menu { min-width: 8rem; }
    .transactions-toolbar .form-floating { min-width: 160px; }
    .transactions-toolbar .form-floating.flex-fill { min-width: 120px; }
        .td-truncate { max-width: 220px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .small-muted { font-size: .85rem; color: #6c757d; }
        .loader-overlay {
            position: absolute; inset: 0; display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.7);z-index:5;border-radius:inherit;
        }
        .badge-small { font-size:.72rem; padding:.25rem .45rem; }
        @media (max-width: 768px) {
            .transactions-toolbar { gap:.5rem; }
            .td-truncate { max-width: 120px; }
        }
    </style>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;min-height:900px">
        <div class="col-md-10" style="min-width:400px;">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" style="text-decoration:none;color:black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Transaction History</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow transactions-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Transaction History</h5>
                        <div class="small text-muted">Recent transactions and filters</div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="small text-muted">Per page</div>
                        <select id="transactionsPerPage" class="form-select form-select-sm" style="width:88px">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="250">250</option>
                        </select>
                    </div>
                </div>
                <div class="card-body position-relative">
                    <div class="mb-3 transactions-toolbar">
                        <div class="input-group flex-fill">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="searchEmail" id="searchEmail" class="form-control" placeholder="Search token, email, or name">
                        </div>

                        <div class="d-flex gap-2 flex-wrap flex-fill w-100">
                            <div class="form-floating flex-fill">
                                <select class="form-select" name="transaction-filter-status" id="transaction-filter-status">
                                    <option value="none">All</option>
                                    <option value="completed">Completed</option>
                                    <option value="pending">Pending</option>
                                    <option value="serve">Failed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                                <label for="transaction-filter-status">Status</label>
                            </div>
                            <div class="form-floating flex-fill">
                                <select class="form-select" name="transaction-filter-daterange" id="transaction-filter-daterange">
                                    <option value="none">All</option>
                                    <option value="today">Today</option>
                                    <option value="yesterday">Yesterday</option>
                                    <option value="this_week">This Week</option>
                                    <option value="last_week">Last Week</option>
                                    <option value="this_month">This Month</option>
                                    <option value="last_month">Last Month</option>
                                    <option value="this_year">This Year</option>
                                    <option value="last_year">Last Year</option>
                                </select>
                                <label for="transaction-filter-daterange">Date</label>
                            </div>
                            <div class="form-floating flex-fill">
                                <select class="form-select" name="getPaymentType" id="getPaymentType">
                                    <option value="none">All</option>
                                    <option value="assessment">Assessment</option>
                                    <option value="registrar">Registrar</option>
                                </select>
                                <label for="getPaymentType">Payment</label>
                            </div>
                        </div>

                        <div class="ms-auto d-flex gap-2">
                            <button id="btnExportCsv" class="btn btn-outline-secondary btn-sm">Export CSV</button>
                            <button id="btnRefreshTransactions" class="btn btn-primary btn-sm">Refresh</button>
                        </div>
                    </div>

                    <!-- table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle transactions-table" id="table-transactions-history">
                            <thead class="table-light">
                                <tr>
                                    <th>Token</th>
                                    <th>Time</th>
                                    <th>Txn #</th>
                                    <th class="d-none d-md-table-cell">Name</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th class="d-none d-sm-table-cell">Counter</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- load rows here -->
                            </tbody>
                        </table>
                    </div>
                    <div id="transactionsOverlay" class="d-none loader-overlay"><div><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><div class="small text-muted mt-2">Loading...</div></div></div>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">Showing up to <strong id="showingCount">1-100</strong></small>
                        </div>
                        <div id="transactionsLoader" class="d-none">
                            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                            <small class="ms-2">Loading...</small>
                        </div>
                    </div>
                    <nav aria-label="">
                        <ul class="pagination justify-content-center mt-3 mb-0" id="transactionsPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW TRANSACTION -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Transaction</h5>
                        <div class="small text-white-75" id="viewEmployeeSubtitle">Details</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-light text-dark me-2" id="btnCopyTransaction">Copy</button>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-body py-3 px-4" id="viewEmployeeBody">
                    <!-- Filled dynamically: left column summary + right column metadata -->
                    <div class="row g-3">
                        <div class="col-md-8" id="viewTransactionSummary"></div>
                        <div class="col-md-4" id="viewTransactionMeta"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once './../includes/footer.php'; ?>
    <?php after_js()?>
</body>
<script>
    const endpointHost = "<?php echo isset($endpoint_server) ? $endpoint_server : (isset($endpoint_host) ? $endpoint_host : ''); ?>";
    let table_transactions_history = document.getElementById("table-transactions-history");
    let searchEmail = document.getElementById("searchEmail");
    let getPaymentType = document.getElementById("getPaymentType");
    // legacy variables (may be created dynamically by renderPagination)
    let pagePrevTransactions = document.getElementById("pagePrevTransactions");
    let pageNextTransactions = document.getElementById("pageNextTransactions");
    let transactionsLoader = document.getElementById('transactionsLoader');
    let transactionsOverlay = document.getElementById('transactionsOverlay');
    let perPageSelect = document.getElementById('transactionsPerPage');
    let btnExportCsv = document.getElementById('btnExportCsv');
    let btnRefreshTransactions = document.getElementById('btnRefreshTransactions');
    let pageInfo = document.getElementById('pageInfo');
    let transactionsPaginationContainer = document.getElementById('transactionsPagination');

    var page = 1;
    // paginate is driven by the per-page selector (default 100)
    var paginate = (perPageSelect && perPageSelect.value) ? Number(perPageSelect.value) : 100;
    // last fetched transactions for export
    let lastTransactions = [];
    var search_transaction = '';
    var payment = 'none';
    var status_transactions = 'none';
    var transaction_desc = true;
    var tranasction_date_range = 'none';

    // Format a date/time string into a friendly 12-hour local time with short month and day
    function formatDateTime12(input) {
        try {
            if (!input) return '';
            const s = String(input);
            let d = null;
            // Try native parse first (handles ISO 8601 and many common formats)
            const parsed = Date.parse(s);
            if (!isNaN(parsed)) {
                d = new Date(parsed);
            } else {
                // Fallback for "YYYY-MM-DD HH:MM[:SS]" without timezone
                const m = s.match(/^(\d{4})-(\d{2})-(\d{2})[ T](\d{2}):(\d{2})(?::(\d{2}))?/);
                if (m) {
                    d = new Date(Number(m[1]), Number(m[2]) - 1, Number(m[3]), Number(m[4]), Number(m[5]), m[6] ? Number(m[6]) : 0);
                }
            }
            if (!d) return s;
            const opts = { year: 'numeric', month: 'short', day: '2-digit', hour: 'numeric', minute: '2-digit', hour12: true };
            return d.toLocaleString(undefined, opts);
        } catch (e) {
            return input;
        }
    }

    let transaction_filter_daterange = document.getElementById("transaction-filter-daterange");
    transaction_filter_daterange.addEventListener('change', function (e) {
        tranasction_date_range = e.target.value;
        console.log(tranasction_date_range);
        getTransactionHistory();
    });
    let transaction_filter_status = document.getElementById("transaction-filter-status");
    transaction_filter_status.addEventListener('change', function (e) {
        status_transactions = e.target.value;
        console.log(status_transactions);
        getTransactionHistory();
    });
    // per-page selector
    if (perPageSelect) {
        perPageSelect.addEventListener('change', function (e) {
            paginate = Number(e.target.value) || 100;
            page = 1;
            getTransactionHistory();
        });
    }
    if (btnRefreshTransactions) {
        btnRefreshTransactions.addEventListener('click', function () { getTransactionHistory(); });
    }
    if (btnExportCsv) {
        btnExportCsv.addEventListener('click', function () {
            if (!lastTransactions || !lastTransactions.length) return alert('No transactions to export');
            // build CSV
            const cols = ['idtransaction','token_number','transaction_time','name','email','payment','status','counterNumber','notes'];
            const csv = [cols.join(',')].concat(lastTransactions.map(row => cols.map(c => '"'+((row[c]||'').toString().replace(/"/g,'""'))+'"').join(','))).join('\n');
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a'); a.href = url; a.download = `transactions_page_${page}.csv`; document.body.appendChild(a); a.click(); a.remove(); URL.revokeObjectURL(url);
        });
    }
    function getTransactionHistory() {
    // Loading indicator will be handled in $.ajax beforeSend

        var params = new URLSearchParams({
            transactions: true,
            page: page,
            paginate: paginate,
            email: search_transaction,
            payment: payment,
            desc: transaction_desc,
            status: status_transactions,
            date_range: tranasction_date_range,
        });
        console.log(params.toString());
        if (!(endpointHost && endpointHost.length > 0)) {
            if (transactionsOverlay) transactionsOverlay.classList.add('d-none');
            else if (transactionsLoader) transactionsLoader.classList.add('d-none');
            const tbody = table_transactions_history.querySelector('tbody');
            if (tbody) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="9" class="text-center text-danger">Service unavailable</td>`;
                tbody.innerHTML = '';
                tbody.appendChild(tr);
            }
            return;
        }
        $.ajax({
            // Call the Python Flask transactions API via configured host
            url: endpointHost.replace(/\/$/, '') + '/api/transactions?' + params,
            type: "GET",
            timeout: 10000,
            xhrFields: { withCredentials: true },
            crossDomain: true,
            beforeSend: function() {
                try {
                    if (transactionsOverlay) transactionsOverlay.classList.remove('d-none');
                    else if (transactionsLoader) transactionsLoader.classList.remove('d-none');
                    // show skeleton rows to indicate loading
                    showSkeletonRows(6);
                } catch (e) { /* ignore */ }
            },
            success: function (response) {
                // clear skeleton / rows
                const tbody = table_transactions_history.querySelector('tbody');
                tbody.innerHTML = '';

                if (response.status === 'success') {
                    const transactions = response.transactions || [];
                    // save for export
                    lastTransactions = transactions;

                    // server-driven pagination: try common fields
                    let totalPages = null;
                    let currentPage = page;
                    if (typeof response.total_pages !== 'undefined') {
                        totalPages = Number(response.total_pages);
                    } else if (response.meta && typeof response.meta.total_pages !== 'undefined') {
                        totalPages = Number(response.meta.total_pages);
                    } else if (typeof response.total !== 'undefined') {
                        totalPages = Math.ceil(Number(response.total) / paginate);
                    }
                    if (typeof response.page !== 'undefined') currentPage = Number(response.page);
                    else if (response.meta && typeof response.meta.page !== 'undefined') currentPage = Number(response.meta.page);

                    // update page info
                    if (pageInfo) {
                        if (totalPages) pageInfo.innerText = `Page ${currentPage} of ${totalPages}`;
                        else pageInfo.innerText = `Page ${currentPage}`;
                    }

                    // render numeric pagination like counters page
                    if (totalPages) {
                        renderTransactionsPagination(totalPages, currentPage);
                    } else {
                        // unknown total: render simple prev/current/next
                        renderTransactionsPaginationUnknown(currentPage, transactions.length >= paginate);
                    }

                    // update showing count (best-effort)
                    document.getElementById('showingCount').innerText = `1-${Math.min(paginate, transactions.length)}`;

                        // render rows (compact markup, improved ARIA)
                    transactions.forEach((transaction) => {
                        const tr = document.createElement('tr');

                        function statusBadge(status) {
                            switch ((status || '').toLowerCase()) {
                                case 'completed': return '<span class="badge bg-success">Completed</span>';
                                case 'pending': return '<span class="badge bg-warning text-dark">Pending</span>';
                                case 'serve': return '<span class="badge bg-danger">Failed</span>';
                                case 'cancelled': return '<span class="badge bg-secondary">Cancelled</span>';
                                default: return `<span class="badge bg-light text-dark">${status || 'Unknown'}</span>`;
                            }
                        }

                        function paymentBadge(payment) {
                            switch ((payment || '').toLowerCase()) {
                                case 'registrar': return '<span class="badge bg-primary">Registrar</span>';
                                case 'assessment': return '<span class="badge bg-info text-dark">Assessment</span>';
                                default: return `<span class="badge bg-light text-dark">${payment || 'N/A'}</span>`;
                            }
                        }

                        tr.innerHTML = `
                            <td class="token-col td-truncate"><strong>${transaction.token_number || '—'}</strong></td>
                            <td class="time-col"><small class="text-muted">${formatDateTime12(transaction.transaction_time)}</small></td>
                            <td class="txn-col td-truncate">#${transaction.idtransaction}</td>
                            <td class="d-none d-md-table-cell td-truncate">${transaction.name || '—'}</td>
                            <td class="d-none d-md-table-cell td-truncate"><a href="mailto:${transaction.email}" class="td-truncate" data-bs-toggle="tooltip" title="${transaction.email || ''}">${transaction.email || '—'}</a></td>
                            <td>${paymentBadge(transaction.payment)}</td>
                            <td>${statusBadge(transaction.status)}</td>
                            <td class="d-none d-sm-table-cell">${transaction.counterNumber ? transaction.counterNumber : '—'}</td>
                            <td class="text-end actions-col">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item btn-view" href="#" data-id="${transaction.idtransaction}">View</a></li>
                                        <li><a class="dropdown-item btn-copy" href="#" data-id="${transaction.idtransaction}">Copy</a></li>
                                        <li><a class="dropdown-item" href="mailto:${transaction.email}">Email</a></li>
                                    </ul>
                                </div>
                            </td>
                        `;

                        // attach event handlers for actions inside the row
                        const viewEl = tr.querySelector('.btn-view');
                        if (viewEl) {
                            viewEl.addEventListener('click', function (e) { e.preventDefault(); showTransactionModal(transaction); });
                        }
                        const copyEl = tr.querySelector('.btn-copy');
                        if (copyEl) {
                            copyEl.addEventListener('click', function (e) {
                                e.preventDefault();
                                const payload = `Txn #${transaction.idtransaction}\nToken: ${transaction.token_number || ''}\nName: ${transaction.name || ''}\nEmail: ${transaction.email || ''}\nPayment: ${transaction.payment || ''}\nStatus: ${transaction.status || ''}\nTime: ${transaction.transaction_time || ''}`;
                                const doNotify = (msg) => {
                                    try {
                                        // create one-off notification container if missing
                                        let container = document.getElementById('copyNotifyContainer');
                                        if (!container) {
                                            container = document.createElement('div');
                                            container.id = 'copyNotifyContainer';
                                            container.style.position = 'fixed';
                                            container.style.top = '1rem';
                                            container.style.right = '1rem';
                                            container.style.zIndex = 9999;
                                            document.body.appendChild(container);
                                        }
                                        const n = document.createElement('div');
                                        n.className = 'alert alert-success py-1 px-2 mb-2';
                                        n.style.minWidth = '160px';
                                        n.style.boxShadow = '0 2px 8px rgba(0,0,0,0.08)';
                                        n.innerText = msg;
                                        container.appendChild(n);
                                        setTimeout(()=>{ n.classList.add('fade'); n.style.transition='opacity .3s'; n.style.opacity='0'; }, 900);
                                        setTimeout(()=>{ n.remove(); if (!container.children.length) container.remove(); }, 1400);
                                    } catch (e) { /* ignore */ }
                                };

                                try {
                                    if (navigator.clipboard && navigator.clipboard.writeText) {
                                        navigator.clipboard.writeText(payload).then(()=> doNotify('Copied transaction'))
                                        .catch(()=> doNotify('Copied'));
                                    } else {
                                        const ta = document.createElement('textarea'); ta.value = payload; document.body.appendChild(ta); ta.select();
                                        try { document.execCommand('copy'); doNotify('Copied transaction'); } catch(e) { doNotify('Copy failed'); }
                                        ta.remove();
                                    }
                                } catch (err) { console.warn('Copy failed', err); doNotify('Copy failed'); }
                            });
                        }

                        tbody.appendChild(tr);

                        // initialize bootstrap tooltip for email if available
                        try {
                            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                                const tEls = tr.querySelectorAll('[data-bs-toggle="tooltip"]');
                                tEls.forEach(function (el) { new bootstrap.Tooltip(el); });
                            }
                        } catch (e) { /* ignore tooltip init errors */ }
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="9" class="text-center text-muted">No transactions found</td>`;
                    tbody.appendChild(tr);
                }
                // hide loader/overlay
                if (transactionsOverlay) transactionsOverlay.classList.add('d-none');
                else if (transactionsLoader) transactionsLoader.classList.add('d-none');
            },
            error: function (xhr, status, err) {
                // hide loader and show error row
                if (transactionsOverlay) transactionsOverlay.classList.add('d-none');
                else if (transactionsLoader) transactionsLoader.classList.add('d-none');
                const tbody = table_transactions_history.querySelector('tbody');
                tbody.innerHTML = '';
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="9" class="text-center text-danger">Error loading transactions</td>`;
                tbody.appendChild(tr);
                console.error('Transaction load error', status, err);
            },
            complete: function() {
                try {
                    if (transactionsOverlay) transactionsOverlay.classList.add('d-none');
                    else if (transactionsLoader) transactionsLoader.classList.add('d-none');
                } catch (e) { /* ignore */ }
            }
        });
    }
    getTransactionHistory();

    function showSkeletonRows(count) {
        const tbody = table_transactions_history.querySelector('tbody');
        tbody.innerHTML = '';
        for (let i = 0; i < count; i++) {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><span class="placeholder col-6 placeholder-wave"></span></td>
                <td><span class="placeholder col-4 placeholder-wave"></span></td>
                <td><span class="placeholder col-2 placeholder-wave"></span></td>
                <td class="d-none d-md-table-cell"><span class="placeholder col-6 placeholder-wave"></span></td>
                <td class="d-none d-md-table-cell"><span class="placeholder col-8 placeholder-wave"></span></td>
                <td><span class="placeholder col-4 placeholder-wave"></span></td>
                <td><span class="placeholder col-3 placeholder-wave"></span></td>
                <td class="d-none d-sm-table-cell"><span class="placeholder col-2 placeholder-wave"></span></td>
                <td class="text-end"><span class="placeholder col-3 placeholder-wave"></span></td>
            `;
            tbody.appendChild(tr);
        }
    }

    searchEmail.addEventListener('keyup', function (e) {
        search_transaction = e.target.value;
        getTransactionHistory();
    });

    getPaymentType.addEventListener('change', function (e) {
        payment = e.target.value;
        getTransactionHistory();
    });

    // Delegated pagination handler for transactions (numeric / counters-style)
    if (transactionsPaginationContainer) {
        transactionsPaginationContainer.addEventListener('click', function (e) {
            e.preventDefault();
            const target = e.target.closest('a.page-link');
            if (!target) return;
            const pageAttr = target.getAttribute('data-page');
            if (pageAttr) {
                const p = parseInt(pageAttr, 10);
                if (!isNaN(p) && p > 0) {
                    page = p;
                    getTransactionHistory();
                }
            }
        });
    }

    // Render numeric pagination when totalPages is known
    function renderTransactionsPagination(totalPages, currentPage) {
        const container = document.getElementById('transactionsPagination');
        if (!container) return;
        let items = '';

        const makeItem = (label, pageNum, disabled, active, id) => {
            return `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"><a href="#" class="page-link" ${id ? `id="${id}"` : ''} data-page="${pageNum}">${label}</a></li>`;
        };

        // First
        items += makeItem('First', 1, currentPage === 1, false, 'pageFirstTransactions');
        // Prev
        items += makeItem('Previous', Math.max(1, currentPage - 1), currentPage === 1, false, 'pagePrevTransactions');

        // Page numbers with collapsing
        const CAP = 5;
        let start = Math.max(2, currentPage - 2);
        let end = Math.min(totalPages - 1, currentPage + 2);
        if (currentPage <= 3) { start = 2; end = Math.min(totalPages - 1, CAP); }
        if (currentPage > totalPages - 3) { start = Math.max(2, totalPages - CAP); end = totalPages - 1; }

        // first page
        items += `<li class="page-item ${currentPage === 1 ? 'active' : ''}"><a href="#" class="page-link" data-page="1">1</a></li>`;
        if (start > 2) {
            items += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
        }

        for (let p = start; p <= end; p++) {
            items += `<li class="page-item ${p === currentPage ? 'active' : ''}"><a href="#" class="page-link" data-page="${p}">${p}</a></li>`;
        }

        if (end < totalPages - 1) {
            items += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
        }

        if (totalPages > 1) {
            items += `<li class="page-item ${currentPage === totalPages ? 'active' : ''}"><a href="#" class="page-link" data-page="${totalPages}">${totalPages}</a></li>`;
        }

        // Next
        items += makeItem('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages, false, 'pageNextTransactions');
        // Last
        items += makeItem('Last', totalPages, currentPage === totalPages, false, 'pageLastTransactions');

        container.innerHTML = items;
    }

    // Render simple pagination (unknown total) for transactions
    function renderTransactionsPaginationUnknown(currentPage, hasMore) {
        const container = document.getElementById('transactionsPagination');
        if (!container) return;
        const prevDisabled = currentPage === 1;
        const nextDisabled = !hasMore;
        const items = `
            <li class="page-item ${prevDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${Math.max(1, currentPage - 1)}">Previous</a></li>
            <li class="page-item active"><span class="page-link">${currentPage}</span></li>
            <li class="page-item ${nextDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${currentPage + 1}">Next</a></li>
        `;
        container.innerHTML = items;
    }

    function showTransactionModal(transaction) {
        const modalTitle = document.getElementById('viewEmployeeTitle');
        const modalSubtitle = document.getElementById('viewEmployeeSubtitle');
        const modalBody = document.getElementById('viewEmployeeBody');
        const summary = document.getElementById('viewTransactionSummary');
        const meta = document.getElementById('viewTransactionMeta');

        modalTitle.innerText = `Transaction #${transaction.idtransaction}`;
    modalSubtitle.innerText = formatDateTime12(transaction.transaction_time) || '';

        // summary (left): token, name, email, payment, status
        const statusHtml = (s => {
            switch ((s||'').toLowerCase()) {
                case 'completed': return '<span class="badge bg-success">Completed</span>';
                case 'pending': return '<span class="badge bg-warning text-dark">Pending</span>';
                case 'serve': return '<span class="badge bg-danger">Failed</span>';
                case 'cancelled': return '<span class="badge bg-secondary">Cancelled</span>';
                default: return `<span class="badge bg-light text-dark">${s || 'Unknown'}</span>`;
            }
        })(transaction.status);

        const paymentHtml = (p => {
            switch ((p||'').toLowerCase()) {
                case 'registrar': return '<span class="badge bg-primary">Registrar</span>';
                case 'assessment': return '<span class="badge bg-info text-dark">Assessment</span>';
                default: return `<span class="badge bg-light text-dark">${p || 'N/A'}</span>`;
            }
        })(transaction.payment);

        summary.innerHTML = `
            <div class="card">
                <div class="card-body">
                    <div class="mb-3"><strong>Token</strong><div class="fs-5">${transaction.token_number || '&mdash;'}</div></div>
                    <div class="mb-2"><strong>Name</strong><div>${transaction.name || '&mdash;'}</div></div>
                    <div class="mb-2"><strong>Email</strong><div><a href="mailto:${transaction.email}">${transaction.email || '&mdash;'}</a></div></div>
                    <div class="d-flex gap-2 mt-3">${paymentHtml}${statusHtml}</div>
                </div>
            </div>
        `;

        // meta (right): counter, txn id, additional metadata
        meta.innerHTML = `
            <div class="card">
                <div class="card-body small text-muted">
                    <div class="mb-2"><strong>Txn #</strong><div>#${transaction.idtransaction}</div></div>
                    <div class="mb-2"><strong>Counter</strong><div>${transaction.counterNumber || '&mdash;'}</div></div>
                    <div class="mb-2"><strong>Time</strong><div>${formatDateTime12(transaction.transaction_time) || '&mdash;'}</div></div>
                    ${transaction.notes ? `<div class="mb-2"><strong>Notes</strong><div>${transaction.notes}</div></div>` : ''}
                </div>
            </div>
        `;

        // attach copy button behavior
        const copyBtn = document.getElementById('btnCopyTransaction');
        if (copyBtn) {
            copyBtn.onclick = function () {
                const payload = `Txn #${transaction.idtransaction}\nToken: ${transaction.token_number || ''}\nName: ${transaction.name || ''}\nEmail: ${transaction.email || ''}\nPayment: ${transaction.payment || ''}\nStatus: ${transaction.status || ''}\nTime: ${transaction.transaction_time || ''}`;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(payload).then(()=>{
                        copyBtn.innerText = 'Copied';
                        setTimeout(()=>{ copyBtn.innerText = 'Copy'; }, 1200);
                    }).catch(()=>{ alert('Copy failed'); });
                } else {
                    // fallback
                    const ta = document.createElement('textarea');
                    ta.value = payload; document.body.appendChild(ta); ta.select(); try { document.execCommand('copy'); copyBtn.innerText = 'Copied'; setTimeout(()=>{ copyBtn.innerText = 'Copy'; }, 1200); } catch(e){ alert('Copy failed'); } ta.remove();
                }
            };
        }

        // show modal (Bootstrap 5 friendly)
        const modalEl = document.getElementById('viewEmployeeModal');
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } else if (window.$ && typeof window.$ === 'function') {
            $('#viewEmployeeModal').modal('show');
        } else {
            console.warn('Bootstrap modal not available to show modal');
        }
    }
</script>
</html>
