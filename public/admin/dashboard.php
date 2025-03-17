<?php
include "./../includes/db_conn.php";
include "./../base.php";

login_as_employee();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Dashboard | <?php echo $project_name; ?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg-non">
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer" style="margin-top: 100px">
        <div class="flex-col flex-md-row justify-content-center mt-5">
            <div class="col-12 text-center p-2">
                <h1>ADMIN DASHBOARD</h1>
            </div>

            <div class="card p-4 mx-0 my-2">
                <div class="row text-center">
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">TRANSACTIONS TODAY</h5>
                        <h1 class="text-center" id="transactions-total">N/A</h1>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">PENDING</h5>
                        <h1 class="text-center" id="transactions-pending">N/A</h1>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">COMPLETED</h5>
                        <h1 class="text-center" id="transactions-completed">N/A</h1>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">CANCELLED</h5>
                        <h1 class="text-center" id="transactions-canceled">N/A</h1>
                    </div>
                </div>
            </div>

            <div class="row flex-col flex-lg-row">
                <div class="col col-md-7 px-0">
                    <div class="col mx-0 my-2">
                        <div class="card shadow-sm p-2 p-md-4">
                            <div class="row w-100 mb-4">
                                <h4 class="text-center">Transaction History</h4>
                            </div>
                            <div class="row my-2">
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-5 px-0" stlye="font-size: 1.5vw;">Email</div>
                                        <div class="col-6 px-0 mx-1">
                                            <select style="width:100%;padding:10px" name="transaction-email-select" id="transaction-email-select">
                                                <option value="none" id="transaction-history-filter-corporate-none">All</option>
                                                <option value="true" id="transaction-history-filter-corporate">Corporate</option>
                                                <option value="false" id="transaction-history-filter-non-corporate">Non-Corporate</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-5 px-0" stlye="font-size: 1.5vw">Payment</div>
                                        <div class="col-6 px-0 mx-1">
                                            <select style="width:100%;padding:10px" name="transaction-history-filter-payment" id="transaction-history-filter-payment">
                                                <option value="none" id="transaction-history-filter-payment-none">All</option>
                                                <option value="registrar" id="transaction-history-filter-registrar">Registrar</option>
                                                <option value="assessment" id="transaction-history-filter-assessment">Assessment</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <table class="table table-striped table-members" id="table-transaction-history">
                                <tr>
                                    <th>Datetime</th>
                                    <th>Transaction Type</th>
                                </tr>
                            </table>
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item">
                                        <a class="page-link" id="pagePrevTransactions">Previous</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" id="pageNextTransactions">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="col mx-0 my-2">
                        <div class="card shadow-sm p-4">
                            <div class="row w-100 mb-4">
                                <h4 class="text-center">Generate Report (Tentative)</h4>
                            </div>
                            <div class="row d-flex justify-content-center">
                                <select style="width:30%;margin:0 10px 0;" name="month" id="month">
                                    <option value="01">Month</option>
                                </select>
                                <select style="width:30%;margin:0 10px 0;" name="year" id="year">
                                    <option value="2023">Year</option>
                                </select>
                                <div class="col-12 col-md-4 d-flex justify-content-center my-2">
                                    <button class="btn btn-primary rounded" type="button" id="btnGenerateReport">Generate Report</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col col-md-5 px-0">
                    <div class="col mx-0 my-2">
                        <div class="card shadow-sm p-4">
                            <div class="row align-center my-2">
                                <div class="col-8">
                                <h4 class="text-center">Employees</h4>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success" style="width: 80%" onclick="window.location.href='./add_employee.php'">Add</button>
                                </div>
                            </div>

                            <table class="table table-striped table-members" id="table-employees">
                                <tr>
                                    <th>#</th>
                                    <th>Username</th>
                                    <th>Created At</th>
                                </tr>
                            </table>
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item">
                                        <a class="page-link" id="pagePrevEmployees">Previous</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" id="pageNextEmployees">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                    <div class="col mx-0 my-2">
                        <div class="card shadow-sm p-4">
                            <div class="row align-center my-2">
                                <div class="col-8">
                                <h4 class="text-center">Counters</h4>
                                </div>
                                <div class="col-4">
                                    <button class="btn btn-success" style="width: 80%" onclick="window.location.href='./add_counter.php'">Add</button>
                                </div>
                            </div>
                            <table class="table table-striped table-members" id="table-counters">
                                <tr>
                                    <th class="col-3">Counter No.</th>    
                                    <th>Employee</th>
                                    <th>Queue Count</th>
                                </tr>
                            </table>
                            <nav aria-label="Page navigation example">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item">
                                        <a class="page-link" id="pagePrevCounters">Previous</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link"id="pageNextCounters">Next</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js"></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/dashboard_admin.js"></script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>