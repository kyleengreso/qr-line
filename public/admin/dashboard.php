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

                <div class="col-12 col-md-9 px-4 text-center text-md-start p-2">
                    <h1>DASHBOARD</h1>
                </div>
                <div class="col-3 d-none d-md-block card shadow p-4 mx-0 my-2" style="border-radius:30px;min-width:150px">
                    <div class="row text-center">
                        <div class="w-100">
                            <a class="btn btn-outline-primary w-100 h-100" id="employee-cut-off" data-toggle="modal" data-target="#cutOffModal">CUT OFF</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
            <div class="p-0 mx-0 my-2">
                <div class="row text-center">
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2" style="border:5px solid #00a;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#00a">
                                            TRANSACTIONS TODAY</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fw-bold" id="transactions-total"></span></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fa-solid fa-users-line fs-1"></i>                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2" style="border:5px solid #aa0;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#aa0">
                                            PENDING</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fw-bold" id="transactions-pending"></span></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fa-regular fa-hourglass-half fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2" style="border:5px solid #0a0;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#0a0">
                                            COMPLETED</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fw-bold" id="transactions-completed"></span></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fa-solid fa-check fs-1"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2" style="border:5px solid #a00;border-radius:5px;border-right:0;border-bottom:0;border-top:0">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-uppercase mb-1" style="color:#a00">
                                            COMPLETED</div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span class="fw-bold" id="transactions-cancelled"></span></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fa-solid fa-xmark fs-1"></i>  
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-100 row flex-col flex-lg-row">
                <div class="col-lg-6">
                    <div class="col mx-0 mx-md-2 my-2">
                        <div class="card shadow">
                            <div class="card-header">
                                Transaction History
                            </div>
                            <div class="card-body">
                                <div class="col w-100 col-12 col-md-6 p-2">
                                    <div class="row">
                                        <span class="fs-6 w-25">Filter</span>
                                        <div class="col-6 px-0 mx-1">
                                            <select class="custom-select custom-select-sm form-control form-control-sm" name="transactionselect" id="transaction-select">
                                                <option value="none" id="transaction-history-filter-corporate-none">All</option>
                                                <option value="psu.palawan.edu.ph" id="transaction-history-filter-corporate">Corporate</option>
                                                <option value="none" id="transaction-history-filter-non-corporate">Non-Corporate</option>
                                                <option value="registrar" id="transaction-history-filter-registrar">Registrar</option>
                                                <option value="assessment" id="transaction-history-filter-assessment">Assessment</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col p-2">
                                        <table class="table table-striped table-members" id="table-transaction-history">
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
                    </div>
                    <div class="col mx-0 mx-md-2 my-2">
                        <div class="card shadow">
                            <div class="card-header">
                                Generate Report
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-6 col-md-4 mb-2 align-middle">
                                        <select class="custom-select custom-select-sm form-control form-control-sm" name="month" id="month">
                                            <option value="01">Month</option>
                                        </select>
                                    </div>
                                    <div class="col-6 col-md-4 mb-2">
                                        <select class="custom-select custom-select-sm form-control form-control-sm" name="year" id="year">
                                            <option value="2023">Year</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-4 mb-2 d-flex justify-content-center">
                                        <button class="btn btn-primary rounded" type="button" id="btnGenerateReport">Generate Report</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="col mx-0 mx-md-2 my-2">
                        <div class="card shadow">
                            <div class="card-header">
                                Employees
                            </div>
                            <div class="card-body">
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
                                <div style="width:100%">
                                    <a class="btn btn-primary" href="./employees.php" style="width:100%">View all employees</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col mx-0 mx-md-2 my-2">
                        <div class="card shadow">
                            <div class="card-header">
                                Counters
                            </div>
                            <div class="card-body">
                                <table class="table table-striped table-members" id="table-counters">
                                    <tr>
                                        <th class="col-3">#</th>    
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
                                <div style="width:100%">
                                    <a class="btn btn-primary" href="./counters.php" style="width:100%">View all counters</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="cutOffModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Employee: <?php echo $username?> is cut off</h5>
                </div>
                <div class="modal-body py-4 px-6 fw-bold" id="viewEmployeeBody">
                    You are cut off for temporary.
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="employee-resume">Start Working</button>
                </div>
            </div>
        </div>
    </div>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/popper.min.js"></script>
    <script src="./../asset/js/bootstrap.bundle.js"></script>
    <script src="./../asset/js/dashboard_admin.js"></script>
    <?php after_js()?>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>