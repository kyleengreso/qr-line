<?php
include_once __DIR__ . "/../base.php";
// API endpoint host (Flask) config
// Load optional endpoint host/server from includes/config.php if present
@include_once __DIR__ . '/../includes/config.php';
// Require admin access for this dashboard
requireAdmin();
// Ensure $token and $username are populated for templates (some pages rely on $username)
$token = $_COOKIE['token'] ?? null;
if ($token) {
    $token = decryptToken($token, $master_key ?? '');
    // normalize to object as other pages do
    $token = json_encode($token);
    $token = json_decode($token);
}
$username = isset($token->username) ? $token->username : null;
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
    <script>
        // Expose raw JWT token from PHP cookie for Authorization header in AJAX calls
        window.phpToken = <?php echo isset($_COOKIE['token']) ? "'" . addslashes($_COOKIE['token']) . "'" : "null"; ?>;
    </script>
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
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3">
                    <div class="col">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase text-secondary small fw-semibold">Today</div>
                                    <div class="display-6 fw-bold mb-0" id="transactions-today"></div>
                                </div>
                                <div class="rounded-3 bg-light p-3 text-primary"><i class="bi bi-people-fill fs-4"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase text-secondary small fw-semibold">Pending</div>
                                    <div class="display-6 fw-bold mb-0" id="transactions-pending"></div>
                                </div>
                                <div class="rounded-3 bg-light p-3 text-warning"><i class="bi bi-hourglass-split fs-4"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase text-secondary small fw-semibold">Completed</div>
                                    <div class="display-6 fw-bold mb-0" id="transactions-completed"></div>
                                </div>
                                <div class="rounded-3 bg-light p-3 text-success"><i class="bi bi-check-lg fs-4"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        <div class="card h-100 shadow-sm border">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="text-uppercase text-secondary small fw-semibold">Cancelled</div>
                                    <div class="display-6 fw-bold mb-0" id="transactions-cancelled"></div>
                                </div>
                                <div class="rounded-3 bg-light p-3 text-danger"><i class="bi bi-x-lg fs-4"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                    <div class="row row-cols-1 row-cols-lg-2 g-3 mt-1">
                        <div class="col">
                            <div class="card h-100 shadow-sm border text-center">
                                <div class="card-body">
                                    <div class="text-uppercase text-secondary small fw-semibold">Student Transactions Today</div>
                                    <div class="display-6 fw-bold mb-0" id="transactions-student-today"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="card h-100 shadow-sm border text-center">
                                <div class="card-body">
                                    <div class="text-uppercase text-secondary small fw-semibold">Total Transactions</div>
                                    <div class="display-6 fw-bold mb-0" id="transactions-total"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 mt-1">
                            <div class="col">
                                <div class="card h-100 shadow-sm border">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-uppercase text-secondary small fw-semibold">Yesterday</div>
                                                <div class="display-6 fw-bold mb-0" id="transactions-yesterday"></div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 shadow-sm border">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-uppercase text-secondary small fw-semibold">This Week</div>
                                                <div class="display-6 fw-bold mb-0" id="transactions-week"></div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-people"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 shadow-sm border">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-uppercase text-secondary small fw-semibold">This Month</div>
                                                <div class="display-6 fw-bold mb-0" id="transactions-month"></div>
                                            </div>
                                            <div class="col-auto fs-1">
                                                <i class="bi bi-calendar-fill"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card h-100 shadow-sm border">
                                    <div class="card-body text-center">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-uppercase text-secondary small fw-semibold">This Year</div>
                                                <div class="display-6 fw-bold mb-0" id="transactions-year"></div>
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
                    <div class="card shadow-sm border mt-2 px-0" id="transaction-chart-area">
                        <div class="card-header py-2">
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary border border-primary rounded-circle" style="width:28px;height:28px;">
                                        <i class="bi bi-graph-up"></i>
                                    </span>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">Transactions Overview</span>
                                        <small class="text-white-50 d-none d-sm-inline">Auto-refresh 5s • Range: <span id="dateRange-badge" class="badge bg-white text-primary border border-primary">Today</span></small>
                                    </div>
                                </div>
                                <div class="d-none d-md-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center gap-1 p-1 bg-white rounded-3 border border-primary">
                                        <div class="btn-group btn-group-sm" id="dateRange-buttons" role="group" aria-label="Date range (quick)">
                                            <button type="button" class="btn btn-outline-primary" data-range="day">Today</button>
                                            <button type="button" class="btn btn-outline-primary" data-range="week">Week</button>
                                            <button type="button" class="btn btn-outline-primary" data-range="month">Month</button>
                                            <button type="button" class="btn btn-outline-primary" data-range="last-12-months">12m</button>
                                        </div>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
                                            <ul class="dropdown-menu dropdown-menu-end" id="dateRange-more">
                                                <li><a class="dropdown-item" href="#" data-range="last-week">Last Week</a></li>
                                                <li><a class="dropdown-item" href="#" data-range="last-30-days">Last 30 Days</a></li>
                                                <li><a class="dropdown-item" href="#" data-range="last-3-months">Last 3 months</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Chart actions">
                                        <button type="button" class="btn btn-outline-light text-white border-white" id="btnRefreshChart" title="Refresh">
                                            <i class="bi bi-arrow-clockwise"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-light text-white border-white" id="btnExportChart" title="Download PNG">
                                            <i class="bi bi-download"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-md-none w-100">
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
                        <div class="card-body">
                            <div class="w-100">
                                <canvas id="transaction-chart" class="w-100" style="height: 360px;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Generate Report -->
                    <div class="card shadow-sm border mt-3 px-0">
                        <div class="card-header py-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary border border-primary rounded-circle" style="width:28px;height:28px;">
                                    <i class="bi bi-filetype-pdf"></i>
                                </span>
                                <span class="fw-semibold">Generate Report</span>
                            </div>
                            <div class="d-none d-md-flex align-items-center gap-2">
                                <div class="btn-group btn-group-sm" role="group" aria-label="Quick select">
                                    <button type="button" class="btn btn-outline-primary" id="btnQuickThisMonth">This month</button>
                                    <button type="button" class="btn btn-outline-primary" id="btnQuickLastMonth">Last month</button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger d-none" id="generateReportNotify">
                                <span>Specify the month and year to generate</span>
                            </div>
                            <div class="row g-3 align-items-end">
                                <div class="col-12 col-md-4">
                                    <label for="month" class="form-label">Month</label>
                                    <select class="form-select" name="month" id="month">
                                        <option value="">--</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <label for="year" class="form-label">Year</label>
                                    <select class="form-select" name="year" id="year">
                                        <option value="">----</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-4">
                                    <button class="btn btn-primary btn-lg w-100" type="button" id="btnGenerateReport">Generate</button>
                                </div>
                            </div>
                            <div class="d-md-none mt-3">
                                <div class="btn-group w-100" role="group" aria-label="Quick select (mobile)">
                                    <button type="button" class="btn btn-outline-primary" id="btnQuickThisMonthMobile">This month</button>
                                    <button type="button" class="btn btn-outline-primary" id="btnQuickLastMonthMobile">Last month</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <script>
    var endpointHost = "<?php echo isset($endpoint_server) ? rtrim($endpoint_server, '/') : ''; ?>";
    var currentUsername = "<?php echo isset($username) ? htmlentities($username) : ''; ?>";
        var page_counter = 1;
        var page_transaction = 1;
        var page_employee = 1;

        var total_counter = 0;
        var total_transaction = 0;
        var total_employee = 0;

        var paginate = 5;
        var transaction_corporate = "none";
        var transaction_payment = "none";

        function displayCounters(response) {
            let data = response;
            let table_counters = $('#table-counters');
            table_counters.empty();
            console.log(data);
            let pagePrevCounters = document.getElementById('pagePrevCounters');
            let pageNextCounters = document.getElementById('pageNextCounters');
            if (data.status === 'success' && data.counters) {
                pagePrevCounters.style.display = 'block';
                pageNextCounters.style.display = 'block';
                data.counters.forEach(counter => {
                    table_counters.append(`
                        <div class="card shadow-sm w-100 p-2 mb-2 d-flex flex-row">
                            <div class="col-1" style="min-width:50px;min-height:50px;max-width:50px;max-height:50px">
                                <h2 class="w-100 h-100 m-0 text-center">${counter.counterNumber}</h2>
                            </div>
                            <div class="px-2">
                            <strong class="p-0">
                                ${userStatusIcon(counter.username, counter.role_type, counter.active)}
                            </strong>
                            </div>
                        </div>
                    `);
                });
            } else {
                pagePrevCounters.style.display = 'none';
                pageNextCounters.style.display = 'none';
                table_counters.append(`
                    <div class="w-100 fw-bold text-center p-4">
                        No counters assigned
                    </div>
                `);
            }
        }

        function getCounter() {
            let params = new URLSearchParams({
                counters: true,
                page: page_counter,
                paginate: paginate,
            });
            if (!(endpointHost && endpointHost.length > 0)) {
                console.warn('Counters service unavailable: endpointHost not set');
                return;
            }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/counters?' + params.toString(),
                type: 'GET',
                xhrFields: { withCredentials: true },
                success: function(response) { displayCounters(response); },
                error: function(err) { console.error('Counters fetch failed', err); }
            });
        }

        function getTransactions() {
            let params = new URLSearchParams({
                today: true,
                desc: true,
                page: page_transaction,
                paginate: paginate,
                email: transaction_corporate,
                payment: transaction_payment,
            });
            if (!(endpointHost && endpointHost.length > 0)) {
                console.warn('Transactions service unavailable: endpointHost not set');
                return;
            }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/transactions?' + params.toString(),
                type: 'GET',
                xhrFields: { withCredentials: true },
                success: function(response) { displayTransactions(response); },
                error: function(err) { console.error('Transactions fetch failed', err); }
            });
        }

        // Transaction History if corporate or not

        $('#transaction-select').change(function() {
            var transaction = $(this).val();
            console.log(transaction);
            if (transaction === "none") {
                transaction_corporate = "none";
                transaction_payment = "none";
            } else if (transaction === "psu.palawan.edu.ph") {
                transaction_corporate = "psu.palawan.edu.ph";
                transaction_payment = "none";
            } else if (transaction === "registrar") {
                transaction_corporate = "none";
                transaction_payment = "registrar";
            } else if (transaction === "assessment") {
                transaction_corporate = "none";
                transaction_payment = "assessment";
            }
            getTransactions();
        });

        // Generate Report :>
    var year = 2025;
    var month = 1;
        var months = ['January', 'February', 'March',
                        'April', 'May', 'June',
                        'July', 'August', 'September',
                        'October', 'November', 'December'];
        var dd_year = $('#year');
        var now = new Date();
        for (var y = 2020; y <= (new Date().getFullYear()) + 5; y++) {
            dd_year.append('<option value="' + y + '">' + y + '</option>');
        }

        dd_year.change(function() {
            year = $(this).val();
            // console.log("Selected value: " + year);
        });

        var dd_month = $('#month');
        for (var m = 1; m <= 12; m++) {
            dd_month.append('<option value="' + m + '">' + months[m-1] + '</option>');
        }
        dd_month.change(function() {
            month = $(this).val();
            // console.log("Selected value: " + month);
        });

        // Set defaults to current month/year
        (function setDefaultMonthYear(){
            var currentMonth = now.getMonth() + 1; // 1..12
            var currentYear = now.getFullYear();
            dd_month.val(String(currentMonth));
            dd_year.val(String(currentYear));
            month = currentMonth;
            year = currentYear;
        })();

        // Quick preset helpers
        function applyThisMonth(){
            var d = new Date();
            var m = d.getMonth() + 1;
            var y = d.getFullYear();
            dd_month.val(String(m)).trigger('change');
            dd_year.val(String(y)).trigger('change');
        }
        function applyLastMonth(){
            var d = new Date();
            var m = d.getMonth(); // 0..11 last month index
            var y = d.getFullYear();
            if (m === 0) { // Jan -> last month is Dec prev year
                m = 12;
                y = y - 1;
            }
            // else m is 1..11 representing last month number already
            dd_month.val(String(m)).trigger('change');
            dd_year.val(String(y)).trigger('change');
        }

        // Wire quick preset buttons (desktop & mobile)
        $('#btnQuickThisMonth, #btnQuickThisMonthMobile').on('click', applyThisMonth);
        $('#btnQuickLastMonth, #btnQuickLastMonthMobile').on('click', applyLastMonth);

        $('#btnGenerateReport').click(function() {
            let generateReportNotify = document.getElementById('generateReportNotify');
            let month = $('#month').val();
            let year = $('#year').val();
            // console.log(month, year);
            if (!month || !year) {
                generateReportNotify.classList.remove('d-none');
                setTimeout(() => {
                    generateReportNotify.classList.add('d-none');
                }, 5000);
            } else {
                if (!(endpointHost && endpointHost.length > 0)) {
                    generateReportNotify.classList.remove('d-none');
                    generateReportNotify.innerHTML = '<span>Report service unavailable. Please try again later.</span>';
                    setTimeout(() => { generateReportNotify.classList.add('d-none'); }, 5000);
                    return;
                }
                var pdfUrl = endpointHost.replace(/\/$/, '') + '/api/report/monthly?year=' + year + '&month=' + month;
                if (currentUsername && currentUsername.length > 0) {
                    pdfUrl += '&user=' + encodeURIComponent(currentUsername);
                }
                window.open(pdfUrl, '_blank');
            }

        });

        // Monitor transaction
        function rtTransaction() {
            if (!(endpointHost && endpointHost.length > 0)) { return; }
            let headers = {};
            if (window.phpToken) {
                headers['Authorization'] = 'Bearer ' + window.phpToken;
            }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/dashboard/admin',
                type: 'GET',
                xhrFields: { withCredentials: true },
                headers: headers,
                success: function(response) {
                    let stat = (response && response.data) ? response.data : {};
                    if (response && response.status === 'success') {
                        $('#transactions-today').text(stat.transaction_today_total ?? 0);
                        $('#transactions-pending').text(stat.transaction_today_pending ?? 0);
                        $('#transactions-completed').text(stat.transaction_today_completed ?? 0);
                        $('#transactions-cancelled').text(stat.transaction_today_cancelled ?? 0);
                        $('#transactions-student-today').text(stat.transaction_today_student ?? 0);
                        $('#transactions-total').text(stat.transaction_total ?? 'N/A');
                        $('#transactions-yesterday').text(stat.transction_yesterday_total ?? 0);
                        $('#transactions-week').text(stat.transaction_week_total ?? 0);
                        $('#transactions-month').text(stat.transaction_month_total ?? 0);
                        $('#transactions-year').text(stat.transaction_year_total ?? 0);
                    }
                },
                error: function(err) { console.error('Dashboard stats fetch failed', err); }
            });
        }

        rtTransaction();

        var operational = true;

        let btn_counter_resume = document.getElementById('employee-resume');
        let dashboardStatus = document.getElementById('dashboardStatus');
        let dashboardStatusMsg = document.getElementById('dashboardStatusMsg');

        // Chart System
        function fmtHr(hour) {
            const hourInt = parseInt(hour, 10);
            const p = hourInt >= 12 ? 'PM' : 'AM';
            const fH = hourInt % 12 || 12;
            return `${fH} ${p}`;
        }
        console.log(Chart.version);

        let transaction_chart = document.getElementById('transaction-chart');
        var transactionChart = null;
        function initTransactionChart(data) {
            console.log(data);
            var chart_labels = [];
            var chart_transaction_total = [];

            if (data && data.stats && data.stats.length > 0) {
                // Current today
                if (data.stats[0].hour) {
                    chart_labels = data.stats.map(stats => fmtHr(stats.hour));
                    chart_transaction_total = data.stats.map(stat => stat.total_transactions);
                    console.log('Hour present');
                // Current week
                } else if (data.stats[0].date) {
                    chart_labels = data.stats.map(stats => stats.date);
                    chart_transaction_total = data.stats.map(stats => stats.total_transactions);
                    console.log('Date present');
                // Current month
                } else if (data.stats[0].month) {
                    chart_labels = data.stats.map(stats => stats.month);
                    chart_transaction_total = data.stats.map(stats => stats.total_transactions);
                    console.log('Month present');
                }
            } else {
                // No data available
                chart_labels = ["No Data"];
                chart_transaction_total = [0];
                console.log("No data available");
            }

            // Create gradient for the fill effect (match theme orange)
            var ctx = transaction_chart.getContext('2d');
            var gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
            gradientFill.addColorStop(0, "rgba(255, 110, 55, 0.25)");
            gradientFill.addColorStop(1, "rgba(255, 110, 55, 0.0)");

            transactionChart = new Chart(transaction_chart, {
                type: 'line',
                data: {
                    labels: chart_labels,
                    datasets: [{
                        label: "Transactions",
                        tension: 0.35,
                        backgroundColor: gradientFill,
                        borderColor: "rgba(255, 110, 55, 1)",
                        pointRadius: 0,
                        pointBackgroundColor: "rgba(255, 110, 55, 1)",
                        pointBorderColor: "rgba(255, 110, 55, 1)",
                        pointHoverRadius: 4,
                        pointHoverBackgroundColor: "rgba(255, 110, 55, 1)",
                        pointHoverBorderColor: "rgba(255, 110, 55, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: chart_transaction_total,
                        fill: true
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            left: 10,
                            right: 25,
                            top: 25,
                            bottom: 0
                        }
                    },
                    interaction: {
                        mode: 'index',
                        intersect: false
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                maxTicksLimit: 7
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: "#eef1f5",
                                drawBorder: false,
                                borderDash: [2]
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: true
                        },
                        tooltip: {
                            backgroundColor: "rgb(255,255,255)",
                            bodyColor: "#858796",
                            titleColor: '#6e707e',
                            titleFont: { size: 14 },
                            borderColor: '#dddfeb',
                            borderWidth: 1,
                            padding: 15,
                            displayColors: false,
                            caretPadding: 10
                        }
                    }
                }
            });
        }
        var transaction_stat_data_range = 'day';
        function getTransactionChart() {
            // ONLY Flask endpoint via endpointHost; no PHP fallback
            let resp = { stats: [] };
            let params = new URLSearchParams({ data_range: transaction_stat_data_range });
            if (!(endpointHost && endpointHost.length > 0)) {
                console.warn('Transaction stats service unavailable: endpointHost not set');
                return resp;
            }
            let headers = {};
            if (window.phpToken) {
                headers['Authorization'] = 'Bearer ' + window.phpToken;
            }
            $.ajax({
                url: endpointHost.replace(/\/$/, '') + '/api/transaction_stats?' + params.toString(),
                type: 'GET',
                async: false,
                xhrFields: { withCredentials: true },
                headers: headers,
                success: function(response) {
                    if (response && response.status === 'success') {
                        resp = response;
                    }
                },
                error: function() {
                    console.error('Failed to load transaction stats');
                }
            });
            return resp;
        }

        initTransactionChart(getTransactionChart());

        function updateTransactionChart(data) {
            console.log(data);
            var chart_labels = [];
            var chart_transaction_total = [];
            if (data && data.stats && data.stats.length > 0) {
                if (data.stats[0].hour) {
                    chart_labels = data.stats.map(stats => fmtHr(stats.hour));
                    chart_transaction_total = data.stats.map(stat => stat.total_transactions);
                    console.log('Hour present');
                } else if (data.stats[0].date) {
                    chart_labels = data.stats.map(stats => stats.date);
                    chart_transaction_total = data.stats.map(stats => stats.total_transactions);
                    console.log('Date present');
                } else if (data.stats[0].month) {
                    chart_labels = data.stats.map(stats => stats.month);
                    chart_transaction_total = data.stats.map(stats => stats.total_transactions);
                    console.log('Month present');
                } 
            }
            transactionChart.data.labels = chart_labels;
            transactionChart.data.datasets[0].data = chart_transaction_total;
            transactionChart.update();
        }

        $('#dateRange-select').change(function() {
            var dataRange = $(this).val();
            transaction_stat_data_range = dataRange;
            // updateTransactionChart(getTransactionChart());
            setActiveRange(dataRange);
        });

        // Desktop quick filter buttons for date range
        function setActiveRange(range) {
            var group = document.getElementById('dateRange-buttons');
            if (!group) return;
            group.querySelectorAll('[data-range]').forEach(function(btn) {
                var isActive = btn.getAttribute('data-range') === range;
                btn.classList.toggle('btn-primary', isActive);
                btn.classList.toggle('text-white', isActive);
                btn.classList.toggle('btn-outline-primary', !isActive);
            });
            // Sync mobile select if different
            var sel = document.getElementById('dateRange-select');
            if (sel && sel.value !== range) sel.value = range;
            // Update badge label in header
            var badge = document.getElementById('dateRange-badge');
            if (badge) badge.textContent = rangeLabel(range);
            // Update dropdown active item
            var more = document.getElementById('dateRange-more');
            if (more) {
                more.querySelectorAll('[data-range]').forEach(function(a){
                    a.classList.toggle('active', a.getAttribute('data-range') === range);
                });
            }
        }

        document.querySelectorAll('#dateRange-buttons [data-range]')?.forEach(function(btn){
            btn.addEventListener('click', function(){
                var val = this.getAttribute('data-range');
                transaction_stat_data_range = val;
                setActiveRange(val);
                updateTransactionChart(getTransactionChart());
            });
        });

        // More dropdown range options
        document.querySelectorAll('#dateRange-more [data-range]')?.forEach(function(a){
            a.addEventListener('click', function(e){
                e.preventDefault();
                var val = this.getAttribute('data-range');
                transaction_stat_data_range = val;
                setActiveRange(val);
                updateTransactionChart(getTransactionChart());
            });
        });

        // Initialize active range button state
        setActiveRange(transaction_stat_data_range);

        // Map internal range value to human-readable label for header badge
        function rangeLabel(val) {
            switch (val) {
                case 'day': return 'Today';
                case 'week': return 'This Week';
                case 'last-week': return 'Last Week';
                case 'month': return 'This Month';
                case 'last-30-days': return 'Last 30 Days';
                case 'last-3-months': return 'Last 3 months';
                case 'last-12-months': return 'Last 12 months';
                default: return val;
            }
        }

        // Chart actions: refresh and export
        var btnRefresh = document.getElementById('btnRefreshChart');
        if (btnRefresh) {
            btnRefresh.addEventListener('click', function(){
                updateTransactionChart(getTransactionChart());
            });
        }
        var btnExport = document.getElementById('btnExportChart');
        if (btnExport) {
            btnExport.addEventListener('click', function(){
                try {
                    var canvas = document.getElementById('transaction-chart');
                    var link = document.createElement('a');
                    link.download = 'transactions-' + (transaction_stat_data_range || 'range') + '.png';
                    link.href = canvas.toDataURL('image/png');
                    link.click();
                } catch (e) { console.error('Export failed', e); }
            });
        }

        setInterval(function() {
            if (operational) {
                updateTransactionChart(getTransactionChart());
                rtTransaction();
            }
        }, 5000);

    </script>
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
            url: endpointHost.replace(/\/$/, '') + '/public/api/api_endpoint.php?' + params,
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
                    url: endpointHost.replace(/\/$/, '') + '/public/api/api_endpoint.php',
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
                            }, 2000);      
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX Error:', status, error);
                    }
                });
            } else {
                $.ajax({
                    url: endpointHost.replace(/\/$/, '') + '/public/api/api_endpoint.php',
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