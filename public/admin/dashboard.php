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

                <div class="col-9 text-center p-2">
                    <h1>ADMIN DASHBOARD</h1>
                </div>
            <div class="col-3 card shadow-sm p-4 mx-0 my-2" style="border-radius:30px">
                <div class="row text-center">
                <div class="w-100">
                        <a class="btn btn-outline-primary w-100 h-100" id="employee-cut-off" data-toggle="modal" data-target="#cutOffModal">CUT OFF</a>
                    </div>
                </div>
            </div>
        </div>
            <div class="card shadow-sm p-4 mx-0 my-2" style="border-radius:30px">
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
                        <h1 class="text-center" id="transactions-cancelled">N/A</h1>
                    </div>
                </div>
            </div>

            <div class="row flex-col flex-lg-row">
                <div class="col col-md-7 px-0">
                    <div class="col mx-0 mx-md-2 my-2">
                        <div class="card shadow-sm p-2 p-md-4" style="border-radius:30px">
                            <div class="row w-100 mb-4">
                                <h4 class="text-center fw-bold">Transaction History</h4>
                            </div>
                            <div class="row my-2">
                                <div class="col-6">
                                    <div class="row">
                                        <div class="col-5 px-0" stlye="font-size: 1.5vw;">Filter</div>
                                        <div class="col-6 px-0 mx-1">
                                            <select style="width:100%;padding:10px" name="transactionselect" id="transaction-select">
                                                <option value="none" id="transaction-history-filter-corporate-none">All</option>
                                                <option value="psu.palawan.edu.ph" id="transaction-history-filter-corporate">Corporate</option>
                                                <option value="none" id="transaction-history-filter-non-corporate">Non-Corporate</option>
                                                <option value="registrar" id="transaction-history-filter-registrar">Registrar</option>
                                                <option value="assessment" id="transaction-history-filter-assessment">Assessment</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <!-- <div class="col-6">
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
                                </div> -->
                            </div>
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
                    <div class="col mx-0 mx-md-2 my-2">
                        <div class="card shadow-sm p-4" style="border-radius:30px">
                            <div class="row w-100 mb-4 p-4">
                                <h4 class="text-center">Generate Report</h4>
                            </div>
                            <div class="row d-flex justify-content-center">
                                <select style="width:30%;padding:10px;margin:0 10px 0;" name="month" id="month">
                                    <option value="01">Month</option>
                                </select>
                                <select style="width:30%;padding:10px;margin:0 10px 0;" name="year" id="year">
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
                        <div class="card shadow-sm p-4" style="border-radius:30px">
                            <div class="row align-center my-2">
                                <div class="col-12">
                                <h4 class="text-center fw-bold">Employees</h4>
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
                            <div style="width:100%">
                                <a class="btn btn-primary" href="./employees.php" style="width:100%">View all employees</a>
                            </div>
                        </div>
                    </div>
                    <div class="col mx-0 my-2">
                        <div class="card shadow-sm p-4" style="border-radius:30px">
                            <div class="row align-center my-2">
                                <div class="col-12">
                                    <h4 class="text-center fw-bold">Counters</h4>
                                </div>
                            </div>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" id="employee-resume">Close</button>
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