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
                    <div class="w-100">
                        <span>Show 1-100</span>
                    </div>
                    <table class="table table-striped table-members" id="table-transactions-history">
                        <thead>
                            <th>Counter No</th>
                            <th>Transaction Time</th>
                            <th>#</th>
                            <th class="d-none d-md-none d-lg-block">Email</th>
                            <th>Payment</th>
                        </thead>
                        <tbody>
                            <!-- Load -->
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link disabled" id="pagePrevTransactions">Previous</a>
                            </li>
                            <!-- Page number reserved -->
                            <li class="page-item">
                                <a class="page-link" id="pageNextTransactions">Next</a>
                            </li>
                        </ul>
                    </nav>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
            success: function (response) {
                while (table_transactions_history.rows.length > 1) {
                    table_transactions_history.deleteRow(1);
                }
                if (response.status === 'success') {
                    const transactions = response.transactions;
                    // console.log(transactions);
                    if (transactions.length < paginate) {
                        pageNextTransactions.classList.add('disabled');
                    } else {
                        pageNextTransactions.classList.remove('disabled');
                    }
                    transactions.forEach((transaction) => {
                        let row = table_transactions_history.insertRow(-1);
                        row.innerHTML += `
                            <td>${transaction.counterNumber ? transaction.counterNumber : "Not Countered" }</td>
                            <td>${transaction.transaction_time}</td>
                            <td>${transaction.idtransaction}</td>
                            <td class="d-none d-md-none d-lg-block">
                                <span class="d-none d-md-none d-lg-block">${transaction.email}</span>
                            </td>
                            <td>${transaction.payment}</td>
                        `;
                    });
                } else {
                    let row = table_transactions_history.insertRow(-1);
                    pageNextTransactions.classList.add('disabled');
                    row.innerHTML += `
                        <td colspan="4" class="text-center">No transactions found</td>
                    `;
                }
            }
        });
    }
    getTransactionHistory();

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
</script>
</html>
