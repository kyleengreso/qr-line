<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();

$token = $_COOKIE['token'];
$token = decryptToken($token, $master_key);
$token = json_encode($token);
$token = json_decode($token);

$id = $token->id;
$username = $token->username;
$role_type = $token->role_type;
$email = $token->email;
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
            <div class="card shadow">
                <div class="card-header">
                    <span>Transaction History</span>
                </div>
                <div class="card-body">
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-start my-1 mx-2 fw-bold">Transaction History</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="input-group">
                                <div class="form-floating">
                                    <input type="text" name="searchEmail" id="searchEmail" class="form-control" placeholder="Search email">
                                    <label for="searchEmail">Search email</label>
                                </div>
                                <a class="input-group-text px-4 fw-bold fs-3" id="btn-transaction-show-filters" data-bs-toggle="collapse" href="#transaction-show-filters" role="button" aria-expanded="false" aria-controls="transaction-show-filters">
                                    <i class="bi bi-filter-left"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="" id="transaction-show-filters">
                            <div class="row">
                                <div class="col-4">
                                    <div class="form-floating">
                                        <select class="form-select" name="transaction-filter-status" id="transaction-filter-status">
                                            <option value="none">All</option>
                                            <option value="completed">Completed</option>
                                            <option value="pending">Pending</option>
                                            <option value="serve">Failed</option>
                                            <option value="cancelled">Cancelled</option>
                                        </select>
                                        <label for="transaction-filter-status">Status</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="form-floating">
                                        <select class="form-select" name="transaction-filter-daterange" id="transaction-filter-daterange">
                                            <option value="none">--</option>
                                            <option value="today">Today</option>
                                            <option value="yesterday">Yesterday</option>
                                            <option value="this_week">This Week</option>
                                            <option value="last_week">Last Week</option>
                                            <option value="this_month">This Month</option>
                                            <option value="last_month">Last Month</option>
                                            <option value="this_year">This Year</option>
                                            <option value="last_year">Last Year</option>
                                        </select>
                                        <label for="getTransactionDesc">Date Range</label>
                                    </div>
                                </div>
                                <div class="col-4">
                                <div class="form-floating">
                                    <select class="form-select" name="getPaymentType" id="getPaymentType">
                                        <option value="none">All</option>
                                        <option value="assessment">Assessment</option>
                                        <option value="registrar">Registrar</option>
                                    </select>
                                    <label for="getPaymentType">Payment</label>
                                </div>
                            </div>
                            </div>
                        </div>
                        <div class="d-none" id="transaction-show-filters">
                        
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">Showing up to <strong id="showingCount">1-100</strong></small>
                        </div>
                        <div id="transactionsLoader" class="d-none">
                            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                            <small class="ms-2">Loading...</small>
                        </div>
                        <div class="d-flex align-items-center">
                            <nav aria-label="Page navigation example">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item">
                                        <a class="page-link disabled" id="pagePrevTransactions">Previous</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" id="pageNextTransactions">Next</a>
                                    </li>
                                </ul>
                            </nav>
                            <small id="pageInfo" class="text-muted ms-3">Page 1</small>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="table-transactions-history">
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
                                <!-- Load -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW TRANSACTION -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include_once './../includes/footer.php'; ?>
    <?php after_js()?>
</body>
<script>
    let table_transactions_history = document.getElementById("table-transactions-history");
    let searchEmail = document.getElementById("searchEmail");
    let getPaymentType = document.getElementById("getPaymentType");
    let pagePrevTransactions = document.getElementById("pagePrevTransactions");
    let pageNextTransactions = document.getElementById("pageNextTransactions");
    let transactionsLoader = document.getElementById('transactionsLoader');
    let pageInfo = document.getElementById('pageInfo');

    var page = 1;
    var paginate = 100;
    var search_transaction = '';
    var payment = 'none';
    var status_transactions = 'none';
    var transaction_desc = true;
    var tranasction_date_range = 'none';

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
    function getTransactionHistory() {
        // show loader and disable pagination while loading
        if (transactionsLoader) transactionsLoader.classList.remove('d-none');
        if (pageNextTransactions) pageNextTransactions.classList.add('disabled');
        if (pagePrevTransactions) pagePrevTransactions.classList.add('disabled');
        // show skeleton rows to indicate loading
        showSkeletonRows(6);

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
        $.ajax({
            url: "/public/api/api_endpoint.php?" + params,
            type: "GET",
            timeout: 10000,
            success: function (response) {
                // clear skeleton / rows
                const tbody = table_transactions_history.querySelector('tbody');
                tbody.innerHTML = '';

                if (response.status === 'success') {
                    const transactions = response.transactions || [];

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

                    // update pagination state using totalPages when available
                    if (totalPages) {
                        if (currentPage <= 1) pagePrevTransactions.classList.add('disabled'); else pagePrevTransactions.classList.remove('disabled');
                        if (currentPage >= totalPages) pageNextTransactions.classList.add('disabled'); else pageNextTransactions.classList.remove('disabled');
                    } else {
                        if (transactions.length < paginate) pageNextTransactions.classList.add('disabled'); else pageNextTransactions.classList.remove('disabled');
                        if (page > 1) pagePrevTransactions.classList.remove('disabled');
                    }

                    // update showing count (best-effort)
                    document.getElementById('showingCount').innerText = `1-${Math.min(paginate, transactions.length)}`;

                    // render rows
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
                            <td><strong>${transaction.token_number || '—'}</strong></td>
                            <td><small class="text-muted">${transaction.transaction_time}</small></td>
                            <td>#${transaction.idtransaction}</td>
                            <td class="d-none d-md-table-cell">${transaction.name || '—'}</td>
                            <td class="d-none d-md-table-cell"><a href="mailto:${transaction.email}">${transaction.email || '—'}</a></td>
                            <td>${paymentBadge(transaction.payment)}</td>
                            <td>${statusBadge(transaction.status)}</td>
                            <td class="d-none d-sm-table-cell">${transaction.counterNumber ? transaction.counterNumber : '—'}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-outline-primary btn-view">View</button>
                            </td>
                        `;

                        // attach event
                        tr.querySelector('.btn-view').addEventListener('click', function () {
                            showTransactionModal(transaction);
                        });

                        tbody.appendChild(tr);
                    });
                } else {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `<td colspan="9" class="text-center text-muted">No transactions found</td>`;
                    tbody.appendChild(tr);
                }
                // hide loader
                if (transactionsLoader) transactionsLoader.classList.add('d-none');
            },
            error: function (xhr, status, err) {
                // hide loader and show error row
                if (transactionsLoader) transactionsLoader.classList.add('d-none');
                const tbody = table_transactions_history.querySelector('tbody');
                tbody.innerHTML = '';
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="9" class="text-center text-danger">Error loading transactions</td>`;
                tbody.appendChild(tr);
                console.error('Transaction load error', status, err);
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

    pagePrevTransactions.addEventListener('click', function (e) {
        if (page > 1) {
            page--;
            if (page === 1) {
                pagePrevTransactions.classList.add('disabled');
            }
            getTransactionHistory();
        }
    });

    pageNextTransactions.addEventListener('click', function (e) {
        page++;
        if (page > 1) {
            pagePrevTransactions.classList.remove('disabled');
        }
        getTransactionHistory();
    });

    function showTransactionModal(transaction) {
        const modalTitle = document.getElementById('viewEmployeeTitle');
        const modalBody = document.getElementById('viewEmployeeBody');
        modalTitle.innerText = `Transaction #${transaction.idtransaction}`;

        const html = `
            <div class="row">
                <div class="col-12 mb-2"><strong>Token:</strong> ${transaction.token_number || '—'}</div>
                <div class="col-6"><strong>Time:</strong> ${transaction.transaction_time}</div>
                <div class="col-6"><strong>Counter:</strong> ${transaction.counterNumber || '—'}</div>
                <div class="col-12 mt-2"><strong>Name:</strong> ${transaction.name || '—'}</div>
                <div class="col-12"><strong>Email:</strong> <a href="mailto:${transaction.email}">${transaction.email || '—'}</a></div>
                <div class="col-6 mt-2"><strong>Payment:</strong> ${transaction.payment || '—'}</div>
                <div class="col-6 mt-2"><strong>Status:</strong> ${transaction.status || '—'}</div>
            </div>
        `;

        modalBody.innerHTML = html;
        // use Bootstrap 5 Modal API (no jQuery dependency)
        const modalEl = document.getElementById('viewEmployeeModal');
        if (typeof bootstrap !== 'undefined') {
            const bsModal = new bootstrap.Modal(modalEl);
            bsModal.show();
        } else if (window.$ && typeof window.$ === 'function') {
            // fallback to jQuery if bootstrap not available globally
            $('#viewEmployeeModal').modal('show');
        } else {
            console.warn('Bootstrap modal not available to show modal');
        }
    }
</script>
</html>
