
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
    $.ajax({
        url: '/public/api/api_endpoint.php?' + params,
        type: 'GET',
        success: function(response) {
            displayCounters(response);
        },
        error: function(response) {
            console.log(response);
        }
    });
}

function getTransactions() {
    let params = new URLSearchParams({
        transactions: true,
        today: true,
        desc: true,
        page: page_transaction,
        paginate: paginate,
        email: transaction_corporate,
        payment: transaction_payment,
    });
    console.log(params.toString());
    $.ajax({
        url: '/public/api/api_endpoint.php?' + params,
        type: 'GET',
        success: function(response) {
            displayTransactions(response);
        },

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
        var url = './../api/api_endpoint.php?generate-report&year=' + year + '&month=' + month;
        window.open(url, '_blank');
    }

});

// Monitor
function rtTransaction() {
    $.ajax({
        url: './../api/api_endpoint.php?dashboard_stats',
        type: 'GET',
        success: function(response) {
            let stat = response.data;
            console.log(stat);
            if (response.status === 'success') {

                // For transaction for today
                let transactionsToday = stat.find(item => item.setup_key === 'transactions_today');
                let transactionsPending = stat.find(item => item.setup_key === 'transactions_today_pending');
                let transactionsCompleted = stat.find(item => item.setup_key === 'transactions_today_completed');
                let transactionsCancelled = stat.find(item => item.setup_key === 'transactions_today_cancelled');

                $('#transactions-today').text(transactionsToday ? transactionsToday.setup_value_int : 'N/A');
                $('#transactions-pending').text(transactionsPending ? transactionsPending.setup_value_int : 'N/A');
                $('#transactions-completed').text(transactionsCompleted ? transactionsCompleted.setup_value_int : 'N/A');
                $('#transactions-cancelled').text(transactionsCancelled ? transactionsCancelled.setup_value_int : 'N/A');

                // Transaction Total
                let transactionsTotal = stat.find(item => item.setup_key === 'transactions_total');
                $('#transactions-total').text(transactionsTotal ? transactionsTotal.setup_value_int : 'N/A');

                // Transaction History for pasts
                let transactionsYesterday = stat.find(item => item.setup_key === 'transactions_yesterday');
                let transactionsThisWeek = stat.find(item => item.setup_key === 'transactions_this_week');
                let transactionsThisMonth = stat.find(item => item.setup_key === 'transactions_this_month');
                let transactionsThisYear = stat.find(item => item.setup_key === 'transactions_this_year');

                $('#transactions-yesterday').text(transactionsYesterday ? transactionsYesterday.setup_value_int : 'N/A');
                $('#transactions-week').text(transactionsThisWeek ? transactionsThisWeek.setup_value_int : 'N/A');
                $('#transactions-month').text(transactionsThisMonth ? transactionsThisMonth.setup_value_int : 'N/A');
                $('#transactions-year').text(transactionsThisYear ? transactionsThisYear.setup_value_int : 'N/A');
            } else {
                // Reserved
            }
        },
        error: function(response) {
            console.log(response);
        }
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

    // Create gradient for the fill effect
    var ctx = transaction_chart.getContext('2d');
    var gradientFill = ctx.createLinearGradient(0, 0, 0, 400);
    gradientFill.addColorStop(0, "rgba(255, 110, 55, 0.4)"); // Start color (semi-transparent)
    gradientFill.addColorStop(1, "rgba(255, 110, 55, 0)");   // End color (fully transparent)

    transactionChart = new Chart(transaction_chart, {
        type: 'line',
        data: {
            labels: chart_labels,
            datasets: [{
                label: "Transactions",
                lineTension: 0.3,
                backgroundColor: gradientFill,
                borderColor: "rgba(255, 110, 55, 1)",
                pointRadius: 3,
                pointBackgroundColor: "rgba(255, 110, 55, 1)",
                pointBorderColor: "rgba(255, 110, 55, 1)",
                pointHoverRadius: 3,
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
            scales: {
                xAxes: [{
                    time: {
                        unit: 'date'
                    },
                    gridLines: {
                        display: false,
                        drawBorder: false
                    },
                    ticks: {
                        maxTicksLimit: 7
                    }
                }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true // Ensure the y-axis starts at 0
                    },
                    gridLines: {
                        color: "rgb(234, 236, 244)",
                        zeroLineColor: "rgb(234, 236, 244)",
                        drawBorder: false,
                        borderDash: [2],
                        zeroLineBorderDash: [2]
                    }
                }],
            },
            legend: {
                display: true
            },
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                titleMarginBottom: 10,
                titleFontColor: '#6e707e',
                titleFontSize: 14,
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                intersect: false,
                mode: 'index',
                caretPadding: 10,
            }
        }
    });
}
var transaction_stat_data_range = 'day';
function getTransactionChart() {
    let resp = null;
    let params = new URLSearchParams({
        transactionStats: true,
        data_range: transaction_stat_data_range,
    });
    $.ajax({
        url: '/public/api/api_endpoint.php?' + params,
        type: 'GET',
        async: false,
        success: function(response) {
            console.log(response);
            if (response.status === 'success') {
                resp = response;
            }
        },
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
});

setInterval(function() {
    if (operational) {
        updateTransactionChart(getTransactionChart());
        rtTransaction();
    }
}, 5000);
