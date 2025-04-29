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
        <div class="flex-col flex-md-row justify-content-center mt-5">
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-danger d-none" id="dashboardStatus">
                        <span class="" id="dashboardStatusMsg">You're has been cut off.</span>
                    </div>
                </div>
                <div class="colGenerate-12 col-md-9 text-center text-md-start">
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
        </div>
            <div class="p-0 mx-0 my-2">
                <div class="row text-center">
                    <div class="col-xl-3 col-md-6 mb-4 p-0">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #00a;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body p-4 p-md-2">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#00a">
                                            TRANSACTIONS TODAY</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-total"></span></div>
                                    </div>
                                    <div class="col-auto fs-1">
                                        <i class="bi bi-people-fill"></i>                              
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4 p-0">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #aa0;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body p-4 p-md-2">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#aa0">
                                            PENDING</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-pending"></span></div>
                                    </div>
                                    <div class="col-auto fs-1">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4 p-0">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #0a0;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body p-4 p-md-2">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#0a0">
                                            COMPLETED</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-completed"></span></div>
                                    </div>
                                    <div class="col-auto fs-1">
                                        <i class="bi bi-check-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4 p-0">
                        <div class="card border-left-primary shadow h-100 py-2 m-2" style="border:5px solid #a00;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body p-4 p-md-2">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#a00">
                                            CANCELLED</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fs-1 fw-bold" id="transactions-cancelled"></span></div>
                                    </div>
                                    <div class="col-auto fs-1">
                                        <i class="bi bi-x-lg"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row flex-col flex-lg-row">
                <div class="col-12 p-0 m-0">
                    <div class="card shadow m-2" id="transaction-chart-area">
                        <div class="card-header">
                            Transactions Overview
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6 col-md-6">
                                    <div class="row">
                                        <div class="col-3">
                                            <span>Data Range: </span>
                                        </div>
                                        <div class="col-9">
                                            <select class="form-select" name="dateRange" id="dateRange-select">
                                                <option value="day">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="last-week">Last Week</option>
                                                <option value="month">This Month</option>
                                                <option value="last-30-days">Last 30 Days</option>
                                                <option value="last-3-months">Last 3 months</option>
                                                <!-- <option value="year">This year</option> -->
                                                <option value="last-12-months">Last 12 months</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-6 col-md-6">
                                    <div class="row">
                                        <div class="col-3">
                                            <span>Data Range: </span>
                                        </div>
                                        <div class="col-9">
                                            <select class="form-select" name="dateRange" id="dateRange-select">
                                                <option value="day">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="last-week">Last Week</option>
                                                <option value="month">This Month</option>
                                                <option value="last-30-days">Last Month</option>
                                                <option value="last-3-months">Last 3 months</option>
                                                <option value="year">This year</option>
                                                <option value="last-12-months">Last 12 months</option>
                                            </select>
                                        </div>
                                    </div>
                                </div> -->
                            </div>
                            <div class="w-100 h-auto">
                                <canvas id="transaction-chart" style="max-width:100%; height:300px;max-height:320px"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 p-0 m-0">
                    <div class="card shadow m-2">
                        <div class="card-header">
                            Transaction History
                        </div>
                        <div class="card-body">
                            <div class="col w-100 col-12 col-md-6 p-2">
                                <div class="row">
                                    <span class="fs-6 w-25">Filter</span>
                                    <div class="col-6 px-0 mx-1">
                                        <select class="form-select" name="transactionselect" id="transaction-select">
                                            <option value="none" id="transaction-history-filter-corporate-none">All</option>
                                            <option value="psu.palawan.edu.ph" id="transaction-history-filter-corporate">Corporate</option>
                                            <option value="none" id="transaction-history-filter-non-corporate">Non-Corporate</option>
                                            <option value="registrar" id="transaction-history-filter-registrar">Registrar</option>
                                            <option value="assessment" id="transaction-history-filter-assessment">Assessment</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col p-2">
                                    <table class="w-100 table table-striped table-members" id="table-transaction-history">
                                        <tr>
                                            <th class="col-3">#</th>
                                            <th>Email</th>
                                            <th>Payment</th>
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
                        </div>
                    </div>
                    <div class="col m-0 p-0 m-0">
                        <div class="card shadow m-2">
                            <div class="card-header">
                                Generate Report
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 col-md-4 mb-2 align-middle">
                                        <select class="form-select" name="month" id="month">
                                            <option value="01">Month</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 mb-2">
                                        <select class="form-select" name="year" id="year">
                                            <option value="2023">Year</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2 d-flex justify-content-center">
                                        <button class="btn btn-primary rounded w-100 w-md-auto" type="button" id="btnGenerateReport">Generate</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row col-lg-6 p-0 m-0">
                        <div class="col-lg-12 col-xl-6 m-0 p-0">
                            <div class="card shadow m-2">
                                <div class="card-header">
                                    Employees
                                </div>
                                <div class="card-body">
                                    <div class="col" id="employee-nonlisting">
                                        Employee is hidden
                                        <div class="col text-center p-4">
                                            <a class="fs-5 text-primary text-decoration-none" id="employee-show" style="cursor:pointer">Show List</a>
                                        </div>
                                    </div>
                                    <div class="col d-none" id="employee-listing">
                                        <div id="table-employees">
                                        </div>
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
                                        <a class="btn btn-primary" href="./employees.php" style="width:100%">View all employees</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-12 col-xl-6 m-0 p-0">
                            <div class="card shadow m-2">
                                <div class="card-header">
                                    Counters
                                </div>
                                <div class="card-body">
                                    <div class="col" id="counter-nonlisting">
                                        Counter is hidden
                                        <div class="col text-center p-4">
                                            <a class="fs-5 text-primary text-decoration-none" id="counters-show" style="cursor:pointer">Show List</a>
                                        </div>
                                    </div>
                                    <div class="col d-none" id="counter-listing">
                                        <div id="table-counters">
                                        </div>
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
                                    <div style="width:100%">
                                        </div>
                                        <a class="btn btn-primary" href="./counters.php" style="width:100%">View all counters</a>
                                </div>
                            </div>
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