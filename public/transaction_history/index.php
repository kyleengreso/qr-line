<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
@include_once __DIR__ . '/../includes/config.php';
if (!isset($token) || !$token) $token = null;
$id = isset($token->id) ? $token->id : null;
$username = isset($token->username) ? $token->username : null;
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
        .table-transactions thead th { position:sticky; top:0; background:#fff; z-index:2; }
        .table-transactions tbody tr:hover { background:rgba(0,0,0,0.02); }
        .td-truncate { max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .loader-overlay { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.7); z-index:5; border-radius:inherit; }
    </style>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>
    <div class="min-h-screen pt-24 pb-32 flex justify-center px-4">
        <div class="w-full max-w-5xl">
            <div id="logOutNotify" class="hidden mb-4 p-4 bg-green-100 text-green-800 rounded-lg text-left">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="bg-white shadow rounded-full px-6 py-2 mb-4">
                <nav class="text-sm">
                    <a href="/public/admin" class="text-gray-700 hover:text-[rgb(255,110,55)]">Dashboard</a>
                    <span class="mx-2 text-gray-400">/</span>
                    <span class="text-gray-500">Transaction History</span>
                </nav>
            </div>
            <div class="bg-white shadow rounded-2xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h5 class="text-lg font-semibold">Transaction History</h5>
                        <div class="text-sm text-gray-500">Recent transactions and filters</div>
                    </div>
                    <div class="flex items-center gap-2 text-sm">
                        <span class="text-gray-500">Per page</span>
                        <select id="transactionsPerPage" class="border border-gray-300 rounded px-2 py-1 text-sm">
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100" selected>100</option>
                            <option value="250">250</option>
                        </select>
                    </div>
                </div>
                <div class="p-6 relative">
                    <div class="mb-4 flex flex-wrap gap-3">
                        <div class="flex-1 min-w-[200px]">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchEmail" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg" placeholder="Search token, email, or name">
                            </div>
                        </div>
                        <select id="transaction-filter-status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">All Status</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="serve">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                        <select id="transaction-filter-daterange" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">All Dates</option>
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this_week">This Week</option>
                            <option value="last_week">Last Week</option>
                            <option value="this_month">This Month</option>
                            <option value="last_month">Last Month</option>
                            <option value="this_year">This Year</option>
                            <option value="last_year">Last Year</option>
                        </select>
                        <select id="getPaymentType" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                            <option value="none">All Payment</option>
                            <option value="assessment">Assessment</option>
                            <option value="registrar">Registrar</option>
                        </select>
                        <div class="flex gap-2 ml-auto">
                            <button id="btnExportCsv" class="px-3 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Export CSV</button>
                            <button id="btnRefreshTransactions" class="px-4 py-2 bg-[rgb(255,110,55)] text-white rounded-lg text-sm hover:bg-[rgb(230,60,20)]">Refresh</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full table-transactions text-sm">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-4 py-3 font-medium">Token</th>
                                    <th class="px-4 py-3 font-medium">Time</th>
                                    <th class="px-4 py-3 font-medium">Txn #</th>
                                    <th class="px-4 py-3 font-medium hidden md:table-cell">Name</th>
                                    <th class="px-4 py-3 font-medium hidden md:table-cell">Email</th>
                                    <th class="px-4 py-3 font-medium">Payment</th>
                                    <th class="px-4 py-3 font-medium">Status</th>
                                    <th class="px-4 py-3 font-medium hidden sm:table-cell">Counter</th>
                                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="transactionsBody"></tbody>
                        </table>
                    </div>
                    <div id="transactionsOverlay" class="hidden loader-overlay"><div class="text-center"><div class="animate-spin w-8 h-8 border-4 border-[rgb(255,110,55)] border-t-transparent rounded-full mx-auto"></div><div class="text-sm text-gray-500 mt-2">Loading...</div></div></div>
                    <div class="flex items-center justify-between mt-4">
                        <small class="text-gray-500">Showing <strong id="showingCount">1-100</strong></small>
                    </div>
                    <nav class="mt-4">
                        <ul id="transactionsPagination" class="flex justify-center gap-1"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div id="viewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="fixed inset-0 bg-black/50" onclick="closeViewModal()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-auto">
            <div class="bg-[rgb(255,110,55)] text-white px-6 py-4 flex items-center justify-between rounded-t-2xl">
                <div>
                    <h5 class="font-bold" id="viewModalTitle">Transaction</h5>
                    <div class="text-sm opacity-80" id="viewModalSubtitle"></div>
                </div>
                <div class="flex gap-2">
                    <button id="btnCopyTransaction" class="px-3 py-1 bg-white text-gray-800 rounded text-sm">Copy</button>
                    <button onclick="closeViewModal()" class="text-white hover:text-gray-200 text-2xl">&times;</button>
                </div>
            </div>
            <div class="p-6 grid md:grid-cols-3 gap-4">
                <div class="md:col-span-2" id="viewTransactionSummary"></div>
                <div id="viewTransactionMeta"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 text-right">
                <button onclick="closeViewModal()" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300">Close</button>
            </div>
        </div>
    </div>

    <?php include_once './../includes/footer.php'; ?>
    <?php after_js()?>
<script>
const endpointHost = window.endpointHost;
let searchEmail = document.getElementById("searchEmail");
let getPaymentType = document.getElementById("getPaymentType");
let perPageSelect = document.getElementById('transactionsPerPage');
let btnExportCsv = document.getElementById('btnExportCsv');
let btnRefreshTransactions = document.getElementById('btnRefreshTransactions');
let transactionsPaginationContainer = document.getElementById('transactionsPagination');
let transactionsOverlay = document.getElementById('transactionsOverlay');
let transactionsBody = document.getElementById('transactionsBody');

var page = 1;
var paginate = (perPageSelect?.value) ? Number(perPageSelect.value) : 100;
let lastTransactions = [];
var search_transaction = '';
var payment = 'none';
var status_transactions = 'none';
var transaction_desc = true;
var tranasction_date_range = 'none';

function formatDateTime12(input) {
    if (!input) return '';
    const d = new Date(input);
    if (isNaN(d)) return input;
    return d.toLocaleString(undefined, { year:'numeric', month:'short', day:'2-digit', hour:'numeric', minute:'2-digit', hour12:true });
}

document.getElementById("transaction-filter-daterange").addEventListener('change', e => { tranasction_date_range = e.target.value; getTransactionHistory(); });
document.getElementById("transaction-filter-status").addEventListener('change', e => { status_transactions = e.target.value; getTransactionHistory(); });
perPageSelect?.addEventListener('change', e => { paginate = Number(e.target.value) || 100; page = 1; getTransactionHistory(); });
btnRefreshTransactions?.addEventListener('click', () => getTransactionHistory());
btnExportCsv?.addEventListener('click', () => {
    if (!lastTransactions?.length) return alert('No transactions to export');
    const cols = ['idtransaction','token_number','transaction_time','name','email','payment','status','counterNumber','notes'];
    const csv = [cols.join(',')].concat(lastTransactions.map(row => cols.map(c => '"'+((row[c]||'').toString().replace(/"/g,'""'))+'"').join(','))).join('\n');
    const blob = new Blob([csv], { type:'text/csv' });
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `transactions_page_${page}.csv`; a.click();
});

function getTransactionHistory() {
    const params = new URLSearchParams({ transactions:true, page, paginate, email:search_transaction, payment, desc:transaction_desc, status:status_transactions, date_range:tranasction_date_range });
    if (!endpointHost) { transactionsBody.innerHTML = '<tr><td colspan="9" class="text-center text-red-500 py-4">Service unavailable</td></tr>'; return; }
    transactionsOverlay?.classList.remove('hidden');
    $.ajax({
        url: endpointHost.replace(/\/$/, '') + '/api/transactions?' + params,
        type: 'GET',
        timeout: 10000,
        xhrFields: { withCredentials: true },
        success: function(response) {
            transactionsBody.innerHTML = '';
            if (response.status === 'success') {
                const transactions = response.transactions || [];
                lastTransactions = transactions;
                let totalPages = response.total_pages || response.meta?.total_pages || (response.total ? Math.ceil(response.total/paginate) : null);
                let currentPage = response.page || response.meta?.page || page;
                document.getElementById('showingCount').innerText = `1-${Math.min(paginate, transactions.length)}`;
                if (totalPages) renderPagination(totalPages, currentPage);
                transactions.forEach(t => {
                    const statusBadge = { completed:'bg-green-500', pending:'bg-yellow-500 text-gray-900', serve:'bg-red-500', cancelled:'bg-gray-400' }[t.status?.toLowerCase()] || 'bg-gray-200 text-gray-800';
                    const paymentBadge = { registrar:'bg-blue-500', assessment:'bg-cyan-500 text-gray-900' }[t.payment?.toLowerCase()] || 'bg-gray-200 text-gray-800';
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-gray-100';
                    tr.innerHTML = `
                        <td class="px-4 py-3 font-medium">${t.token_number || '—'}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">${formatDateTime12(t.transaction_time)}</td>
                        <td class="px-4 py-3">#${t.idtransaction}</td>
                        <td class="px-4 py-3 hidden md:table-cell td-truncate">${t.name || '—'}</td>
                        <td class="px-4 py-3 hidden md:table-cell td-truncate"><a href="mailto:${t.email}" class="text-[rgb(255,110,55)] hover:underline">${t.email || '—'}</a></td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded text-white ${paymentBadge}">${t.payment || 'N/A'}</span></td>
                        <td class="px-4 py-3"><span class="px-2 py-1 text-xs rounded text-white ${statusBadge}">${t.status || 'Unknown'}</span></td>
                        <td class="px-4 py-3 hidden sm:table-cell">${t.counterNumber || '—'}</td>
                        <td class="px-4 py-3 text-right">
                            <button class="btn-view px-2 py-1 text-xs border border-gray-300 rounded hover:bg-gray-100" data-id="${t.idtransaction}">View</button>
                        </td>`;
                    tr.querySelector('.btn-view')?.addEventListener('click', () => showModal(t));
                    transactionsBody.appendChild(tr);
                });
                if (!transactions.length) transactionsBody.innerHTML = '<tr><td colspan="9" class="text-center text-gray-500 py-4">No transactions found</td></tr>';
            } else {
                transactionsBody.innerHTML = '<tr><td colspan="9" class="text-center text-gray-500 py-4">No transactions found</td></tr>';
            }
            transactionsOverlay?.classList.add('hidden');
        },
        error: function() {
            transactionsOverlay?.classList.add('hidden');
            transactionsBody.innerHTML = '<tr><td colspan="9" class="text-center text-red-500 py-4">Error loading transactions</td></tr>';
        }
    });
}

function renderPagination(totalPages, currentPage) {
    let html = '';
    const btn = (label, p, disabled, active) => `<li><button class="px-3 py-1 rounded ${active?'bg-[rgb(255,110,55)] text-white':'border border-gray-300 hover:bg-gray-100'} ${disabled?'opacity-50 cursor-not-allowed':''}" ${disabled?'disabled':''} data-page="${p}">${label}</button></li>`;
    html += btn('Prev', Math.max(1,currentPage-1), currentPage===1, false);
    for (let p = 1; p <= totalPages; p++) {
        if (p === 1 || p === totalPages || (p >= currentPage-2 && p <= currentPage+2)) html += btn(p, p, false, p===currentPage);
        else if (p === currentPage-3 || p === currentPage+3) html += '<li><span class="px-2">...</span></li>';
    }
    html += btn('Next', Math.min(totalPages,currentPage+1), currentPage===totalPages, false);
    transactionsPaginationContainer.innerHTML = html;
    transactionsPaginationContainer.querySelectorAll('button[data-page]').forEach(b => b.addEventListener('click', () => { page = parseInt(b.dataset.page); getTransactionHistory(); }));
}

searchEmail.addEventListener('keyup', e => { search_transaction = e.target.value; getTransactionHistory(); });
getPaymentType.addEventListener('change', e => { payment = e.target.value; getTransactionHistory(); });

let currentTransaction = null;
function showModal(t) {
    currentTransaction = t;
    document.getElementById('viewModalTitle').innerText = `Transaction #${t.idtransaction}`;
    document.getElementById('viewModalSubtitle').innerText = formatDateTime12(t.transaction_time);
    const statusBadge = { completed:'bg-green-500', pending:'bg-yellow-500 text-gray-900', serve:'bg-red-500', cancelled:'bg-gray-400' }[t.status?.toLowerCase()] || 'bg-gray-200 text-gray-800';
    const paymentBadge = { registrar:'bg-blue-500', assessment:'bg-cyan-500 text-gray-900' }[t.payment?.toLowerCase()] || 'bg-gray-200 text-gray-800';
    document.getElementById('viewTransactionSummary').innerHTML = `
        <div class="bg-gray-50 rounded-xl p-4">
            <div class="mb-3"><div class="text-sm text-gray-500">Token</div><div class="text-xl font-bold">${t.token_number || '—'}</div></div>
            <div class="mb-3"><div class="text-sm text-gray-500">Name</div><div>${t.name || '—'}</div></div>
            <div class="mb-3"><div class="text-sm text-gray-500">Email</div><div><a href="mailto:${t.email}" class="text-[rgb(255,110,55)] hover:underline">${t.email || '—'}</a></div></div>
            <div class="flex gap-2"><span class="px-2 py-1 text-xs rounded text-white ${paymentBadge}">${t.payment || 'N/A'}</span><span class="px-2 py-1 text-xs rounded text-white ${statusBadge}">${t.status || 'Unknown'}</span></div>
        </div>`;
    document.getElementById('viewTransactionMeta').innerHTML = `
        <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600">
            <div class="mb-2"><div class="font-medium">Txn #</div><div>#${t.idtransaction}</div></div>
            <div class="mb-2"><div class="font-medium">Counter</div><div>${t.counterNumber || '—'}</div></div>
            <div class="mb-2"><div class="font-medium">Time</div><div>${formatDateTime12(t.transaction_time)}</div></div>
            ${t.notes ? `<div class="mb-2"><div class="font-medium">Notes</div><div>${t.notes}</div></div>` : ''}
        </div>`;
    document.getElementById('viewModal').classList.remove('hidden');
}
function closeViewModal() { document.getElementById('viewModal').classList.add('hidden'); }
document.getElementById('btnCopyTransaction')?.addEventListener('click', () => {
    if (!currentTransaction) return;
    const t = currentTransaction;
    const payload = `Txn #${t.idtransaction}\nToken: ${t.token_number||''}\nName: ${t.name||''}\nEmail: ${t.email||''}\nPayment: ${t.payment||''}\nStatus: ${t.status||''}\nTime: ${t.transaction_time||''}`;
    navigator.clipboard?.writeText(payload).then(() => { document.getElementById('btnCopyTransaction').innerText = 'Copied'; setTimeout(() => document.getElementById('btnCopyTransaction').innerText = 'Copy', 1200); });
});

getTransactionHistory();
</script>
</body>
</html>
