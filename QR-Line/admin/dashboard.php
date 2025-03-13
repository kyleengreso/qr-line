<?php
session_start();
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
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container" style="margin-top: 15vh;">
        <div class="flex-col flex-md-row justify-content-center mt-5">
            <div class="col-12 text-center p-2">
                <h1>DASHBOARD</h1>
            </div>

            <div class="card p-4 mx-0 my-2">
                <div class="row text-center">
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">TRANSACTIONS TODAY</h5>
                        <h1 class="text-center" id="transactions-total">0</h1>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">PENDING</h5>
                        <h1 class="text-center" id="transactions-pending">0</h1>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">COMPLETED</h5>
                        <h1 class="text-center" id="transactions-completed">0</h1>
                    </div>
                    <div class="col-6 col-md-3">
                        <h5 class="text-center" style="font-size: 2vh">CANCELLED</h5>
                        <h1 class="text-center" id="transactions-canceled">0</h1>
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
                                        <div class="col-6" stlye="font-size: 1.5vw;">Email</div>
                                        <div class="col-6 px-0">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="transaction-history-filter-email" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    All
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" id="transaction-history-filter-corporate-none" value="none">All</a>
                                                    <a class="dropdown-item" id="transaction-history-filter-corporate" value="true">Corporate Email</a>
                                                    <a class="dropdown-item" id="transaction-history-filter-non-corporate" value="false">Non-corporate</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-6" stlye="font-size: 1.5vw">Payment</div>
                                        <div class="col-6 px-0">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary dropdown-toggle" type="button" id="transaction-history-filter-payment" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    All
                                                </button>
                                                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <a class="dropdown-item" id="transaction-history-filter-payment-none" value="none">All</a>
                                                    <a class="dropdown-item" id="transaction-history-filter-registrar" value="registrar">Registrar</a>
                                                    <a class="dropdown-item" id="transaction-history-filter-assessment" value="assessment">Assessment</a>
                                                </div>
                                            </div>
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
                            <div class="row">
                                <div class="col-12 col-md-8 d-flex justify-content-center my-2">
                                    <div class="dropdown mx-2">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="month">
                                            Month
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton" id="generate-report-month">
                                            <a class="dropdown-item text-center" href="#">Month</a>
                                        </div>
                                    </div>
                                    <div class="dropdown mx-2">
                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" id="year">
                                            Year
                                        </button>
                                        <div class="dropdown-menu text-center" aria-labelledby="dropdownMenuButton" id="generate-report-year">
                                        <a class="dropdown-item" href="#">Year</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4 d-flex justify-content-center my-2">
                                    <button class="btn btn-primary rounded" type="submit">Generate Report</button>
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
</body>
</html>