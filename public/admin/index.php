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
                        <div class="alert text-start alert-success d-none" id="cutOffNotification">
                            Operational
                        </div>

                    </div>
                    <div class="col-12 col-md-9 text-center text-md-start">
                        <div class="pl-4">
                            <h1>
                                DASHBOARD 
                                <span class="text-danger d-none"id="cutOffState">(Cut Off)</span>
                            </h1>
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
                                                Today
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
                                        <td class="d-none pr-4 text-center text-muted">
                                            <i class="fs-1 bi bi-graph-up"></i>                   
                                        </td>
                                        <td class="text-center">
                                            <span class="text-muted text-uppercase mb-0">
                                                Total Transactions
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
                                                    Yesterday</div>
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
                                                    This Week</div>
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
                                                    This Month</div>
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
                                                    This Year</div>
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
                                                <div class="col-12">
                                                    <div class="form-floating">
                                                        <select class="form-select" name="dateRange" id="dateRange-select">
                                                            <option value="day">Today</option>
                                                            <option value="week">This Week</option>
                                                            <option value="last-week">Last Week</option>
                                                            <option value="month">This Month</option>
                                                            <option value="last-30-days">Last 30 Days</option>
                                                            <option value="last-3-months">Last 3 months</option>
                                                            <option value="last-12-months">Last 12 months</option>
                                                        </select>
                                                        <label for="dateRange-select" class="form-label`">Date Range</label>
                                                    </div>
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

                                                        <!-- Generate Report -->
                                                        <div class="card shadow m-2">
                                        <div class="card-header">
                                            Generate Report
                                        </div>
                                        <div class="card-body">
                                            <div class="alert alert-danger d-none" id="generateReportNotify">
                                                <span>Specify the month and year to generate</span>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 col-md-4 mb-2">
                                                    <div class="form-floating">
                                                        <select class="form-select" name="month" id="month">
                                                            <option value="">--</option>
                                                        </select>
                                                        <label for="month" class="form-label">Month</label>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-4 mb-2">
                                                    <div class="form-floating">
                                                        <select class="form-select" name="year" id="year">
                                                            <option value="">----</option>
                                                        </select>
                                                        <label for="year" class="form-label">Year</label>
                                                    </div>
                                                </div>
                                                <div class="col-12 col-md-4 mb-2">
                                                    <div class="form-floating align-stretch">
                                                        <button class="btn btn-primary btn-lg btn-lg w-100" type="button" id="btnGenerateReport">Generate</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/dashboard_admin.js"></script>
    <script>
        // Cut Off
        let cutOffNotification = document.getElementById('cutOffNotification');
        let cutOffState = document.getElementById('cutOffState');
        let cutOff = document.getElementById('employee-cut-off');
        const params = new URLSearchParams({
            employeeCutOff: true,
            id: <?php echo $id?>
        });

        $.ajax({
            url: `${realHost}/public/api/api_endpoint.php?${params}`,
            type: 'GET',
            success: function(response) {
                console.log(response);
                if (response.status == "success") {
                    console.log(response.cut_off);
                    if (response.cut_off_state == 1) {
                        operational = false;
                        cutOffNotification.classList.remove('alert-success');
                        cutOffNotification.classList.add('alert-danger');
                        cutOffNotification.innerHTML = 'You have been cut-off';
                        cutOff.classList.remove('btn-danger');
                        cutOff.innerText = "Resume";
                        cutOff.classList.add('btn-success');
                        cutOffState.classList.remove('d-none');
                        // setTimeout(() => {
                        //     cutOffNotification.classList.add('d-none');
                        // }, 5000);  
                    } else {
                        operational = true;
                        cutOffNotification.classList.remove('alert-danger');
                        cutOffNotification.classList.add('alert-success');
                        cutOffNotification.innerHTML = 'You are back to operational';
                        cutOff.classList.remove('btn-success');
                        cutOff.innerText = "Cut Off";
                        cutOff.classList.add('btn-danger');
                        cutOffState.classList.add('d-none');
                        // setTimeout(() => {
                        //     cutOffNotification.classList.add('d-none');
                        // }, 5000);
                    }
                }
            }
        });

        cutOff.addEventListener('click', function(e) {
            e.preventDefault();
            if (operational) {
                $.ajax({
                    url: '/public/api/api_endpoint.php',
                    type: 'POST',
                    data: JSON.stringify({
                        method: 'employee-cut-off',
                        id: <?php echo $id?>,
                    }),
                    success: function(response) {
                        if (response.status === 'success') {
                            operational = false;
                            cutOffNotification.classList.remove('alert-success', 'd-none');
                            cutOffNotification.classList.add('alert-danger');
                            cutOffNotification.innerHTML = 'You have been cut-off';
                            cutOff.classList.remove('btn-danger');
                            cutOff.innerText = "Resume";
                            cutOff.classList.add('btn-success');
                            cutOffState.classList.remove('d-none');
                            setTimeout(() => {
                                cutOffNotification.classList.add('d-none');
                            }, 5000);      
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            } else {
                $.ajax({
                    url: `${realHost}/public/api/api_endpoint.php`,
                    type: 'POST',
                    data: JSON.stringify({
                        method: 'employee-cut-off',
                        id: <?php echo $id?>,
                    }),
                    success: function(response) {
                        if (response.status === 'success') {
                            operational = true;
                            cutOffNotification.classList.remove('alert-danger', 'd-none');
                            cutOffNotification.classList.add('alert-success');
                            cutOffNotification.innerHTML = 'You are back to operational';
                            cutOff.classList.remove('btn-success');
                            cutOff.innerText = "Cut Off";
                            cutOff.classList.add('btn-danger');
                            cutOffState.classList.add('d-none');
                            setTimeout(() => {
                                cutOffNotification.classList.add('d-none');
                            }, 5000);
                        } else {
                            console.log('Error:', response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                })
            }
        }); 
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>