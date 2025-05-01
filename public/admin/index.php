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
$counterNumber = $token->counterNumber;
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-non">
    <?php include "./../includes/navbar.php"; ?>
    <div class="container before-footer" style="margin-top: 100px">
        <div class="row" style="transform:scale(1)">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="flex-col flex-md-row justify-content-center mt-5 p-0">
                <div class="row p-0 m-0">
                    <div class="col-12">
                        <div class="alert alert-danger d-none" id="dashboardStatus">
                            <span class="" id="dashboardStatusMsg">You're has been cut off.</span>
                        </div>
                    </div>
                    <div class="col-12 col-md-9 text-center text-md-start">
                        <div class="px-4">
                            <h1>DASHBOARD</h1>
                        </div>
                    </div>
                    <div class="col-2 col-md-3 py-2 d-none d-md-block card shadow">
                        <div class="row">
                            <div class="w-100">
                                <a class="btn btn-outline-primary w-100 h-100" id="employee-cut-off" data-toggle="modal" data-target="#cutOffModal">CUT OFF</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row p-0 m-0 text-center">
                        <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #27548A;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #27548A">
                                                TRANSACTIONS TODAY
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span class="fs-1 fw-bold" id="transactions-today"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto fs-1">
                                            <i class="bi bi-people-fill"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #DDA853;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #DDA853">
                                                Pending
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span class="fs-1 fw-bold" id="transactions-pending"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto fs-1">
                                            <i class="bi bi-hourglass-split"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #328E6E;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #328E6E">
                                                Completed
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span class="fs-1 fw-bold" id="transactions-completed"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto fs-1">
                                            <i class="bi bi-check-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #F16767;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #F16767">
                                                Cancelled
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <span class="fs-1 fw-bold" id="transactions-cancelled"></span>
                                            </div>
                                        </div>
                                        <div class="col-auto fs-1">
                                            <i class="bi bi-x-lg"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row p-0 m-0">
                        <div class="col-12 m-0 mb-4">
                            <div class="d-flex justify-content-center align-items-center card border border-2 text-center bg-white p-4 shadow rounded">
                                <table style="max-width: 250px">
                                    <tr>
                                        <td class="pr-3 text-center text-muted">
                                            <i class="fs-1 bi bi-graph-up"></i>                   
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted mb-0">
                                                TOTAL TRANSACTIONS
                                                <h3 class="fs-2 fw-bold"><span id="transactions-total"></span></h3>
                                            </span>               
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        <div class="row p-0 m-0">
                            <div class="col-xl-3 col-md-6 mb-4 p-0">
                                <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #7C4585;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #7C4585">
                                                    TRANSACTION YESTERDAY</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                    <span class="fs-1 fw-bold" id="transactions-yesterday"></span>
                                                </div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-4 p-0">
                                <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #F8B55F;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #F8B55F">
                                                    THIS WEEK</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-week"></span></div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-4 p-0">
                                <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #FFCBCB;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color: #FFCBCB">
                                                    THIS MONTH</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-month"></span></div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-calendar-fill"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-4 p-0">
                                <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #0A97B0;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#0A97B0">
                                                    THIS YEAR</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-year"></span></div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-calendar"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transactions Chart -->
                    <div class="row p-0 m-0">
                        <div class="col-12 p-0 m-0">
                            <div class="card shadow m-2" id="transaction-chart-area">
                                <div class="card-header">
                                    Transactions Overview
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <div class="row">
                                                <div class="col-6">
                                                    <span>Data Range: </span>
                                                </div>
                                                <div class="col-6">
                                                    <select class="form-select" name="dateRange" id="dateRange-select">
                                                        <option value="day">Today</option>
                                                        <option value="week">This Week</option>
                                                        <option value="last-week">Last Week</option>
                                                        <option value="month">This Month</option>
                                                        <option value="last-30-days">Last 30 Days</option>
                                                        <option value="last-3-months">Last 3 months</option>
                                                        <option value="last-12-months">Last 12 months</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-100 h-auto">
                                        <canvas id="transaction-chart" class="w-100" style="height: 300px;"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Rest -->
                    <div class="row p-0 m-0">
                        <div class="col-12 col-sm-12 row-md p-0 m-0">
                            <div class="row p-0 m-0">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 p-0 m-0">
                                    <!-- Transactions History -->
                                    <div class="card shadow m-2 mb-4">
                                        <div class="card-header">
                                            Transaction History
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <span class="fs-6 col-3">Filter</span>
                                                <div class="col-9">
                                                    <select class="form-select" name="transactionselect" id="transaction-select">
                                                        <option value="none" id="transaction-history-filter-corporate-none">All</option>
                                                        <option value="psu.palawan.edu.ph" id="transaction-history-filter-corporate">Corporate</option>
                                                        <option value="none" id="transaction-history-filter-non-corporate">Non-Corporate</option>
                                                        <option value="registrar" id="transaction-history-filter-registrar">Registrar</option>
                                                        <option value="assessment" id="transaction-history-filter-assessment">Assessment</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="table-responsive">
                                                <table class="table table-striped table-members" id="table-transaction-history">
                                                    <thead>
                                                        <tr>
                                                            <th class="col-3">#</th>
                                                            <th>Email</th>
                                                            <th>Payment</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <!-- Table rows go here -->
                                                    </tbody>
                                                </table>
                                            </div>
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
                                    <!-- Generate Report -->
                                    <div class="card shadow m-2">
                                        <div class="card-header">
                                            Generate Report
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-6 col-md-4 mb-2">
                                                    <select class="form-select" name="month" id="month">
                                                        <option value="01">Month</option>
                                                    </select>
                                                </div>
                                                <div class="col-6 col-md-4 mb-2">
                                                    <select class="form-select" name="year" id="year">
                                                        <option value="2023">Year</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 col-md-4 mb-2">
                                                    <button class="btn btn-primary w-100" type="button" id="btnGenerateReport">Generate</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-6 p-0 m-0">
                                    <div class="row p-0 m-0">
                                        <div class="col-md-6 col-lg-6 p-0 m-0">
                                            <div class="card shadow m-2">
                                                <div class="card-header">
                                                    Employees
                                                </div>
                                                <div class="card-body">
                                                    <div id="employee-nonlisting">
                                                        Employee is hidden
                                                        <div class="text-center p-4">
                                                            <a class="fs-5 text-primary text-decoration-none" id="employee-show" style="cursor:pointer">Show List</a>
                                                        </div>
                                                    </div>
                                                    <div class="d-none" id="employee-listing">
                                                        <div id="table-employees"></div>
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
                                                    <div class="w-100">
                                                        <a class="btn btn-primary w-100" href="/public/employees/">View all employees</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-6 p-0 m-0">
                                            <div class="card shadow m-2">
                                                <div class="card-header">
                                                    Counters
                                                </div>
                                                <div class="card-body">
                                                    <div id="counter-nonlisting">
                                                        Counter is hidden
                                                        <div class="text-center p-4">
                                                            <a class="fs-5 text-primary text-decoration-none" id="counters-show" style="cursor:pointer">Show List</a>
                                                        </div>
                                                    </div>
                                                    <div class="d-none" id="counter-listing">
                                                        <div id="table-counters"></div>
                                                        <nav aria-label="Page navigation example">
                                                            <ul class="pagination justify-content-center">
                                                                <li class="page-item">
                                                                    <a class="page-link" id="pagePrevCounters">Previous</a>
                                                                </li>
                                                                <li class="page-item">
                                                                    <a class="page-link" id="pageNextCounters">Next</a>
                                                                </li>
                                                            </ul>
                                                        </nav>
                                                    </div>
                                                    <div class="w-100">
                                                        <a class="btn btn-primary w-100" href="/public/counters">View all counters</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-lg-6 p-0 m-0">
                                <div class="col-12 col-lg-12 col-xl-6">
                                </div>
                                <div class="d-flex flex-row flex-col-lg-6 p-0 m-0 gap-3">
                                    <div class="col-lg-12 col-xl-6">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee List -->
                    <div class="row p-0 m-0">
                    </div>

                    <!-- Counter List -->
                    <div class="row p-0 m-0">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cutOffModal" tabindex="-1" role="dialog"  aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Employee: <?php echo $username?> is cut off</h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                    You are cut off for temporary.
                    <input type="hidden" name="employee-id" id="employee-id" value="<?php echo $id?>">
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success" data-dismiss="modal" id="employee-resume">Resume</button>
                </div>
            </div>
        </div>
    </div>
    <?php after_js()?>
    <script src="./../asset/js/dashboard_admin.js"></script>
    <script>
        let btn_employee_show = document.getElementById("employee-show");
        btn_employee_show.addEventListener("click", function() {
            let employee_nonlisting = document.getElementById("employee-nonlisting");
            employee_nonlisting.classList.add('d-none');
            let employee_listing = document.getElementById("employee-listing");
            employee_listing.classList.remove('d-none');
        });
        let counters_show = document.getElementById("counters-show");
        counters_show.addEventListener("click", function() {
            let counter_nonlisting = document.getElementById("counter-nonlisting");
            counter_nonlisting.classList.add('d-none');
            let counter_listing = document.getElementById("counter-listing");
            counter_listing.classList.remove('d-none');
        });
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>