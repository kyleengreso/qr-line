
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
    $.ajax({
        url: './../api/api_endpoint.php?counters&page=' + page_counter + '&paginate=' + paginate,
        type: 'GET',
        success: function(response) {
            displayCounters(response);
        },
        error: function(response) {
            console.log(response);
        }
    });
}

function displayTransactions(response) {
    let data = response;
    console.log(data);
    let table_transactions = document.getElementById('table-transaction-history');
    while (table_transactions.rows.length > 1) {
        table_transactions.deleteRow(-1);
    }
    let pagePrevEmployees = document.getElementById('pagePrevEmployees');
    let pageNextEmployees = document.getElementById('pageNextEmployees');
    if (data.status === 'success' && data.transactions) {
        pagePrevEmployees.style.display = 'block';
        pageNextEmployees.style.display = 'block';
        data.transactions.forEach(transaction => {
            let row = table_transactions.insertRow(-1);
            // console.log(transaction);
            row.innerHTML = `
                <tr>
                    <td>${transaction.queue_number}</td>
                    <td>${transaction.email}</td>
                    <td>${transaction.payment}</td>
                </tr>
            `;
        });
    } else {
        pagePrevEmployees.style.display = 'none';
        pageNextEmployees.style.display = 'none';
        let row = table_transactions.insertRow(-1);
        row.innerHTML = `
            <td colspan="3" class="text-center fw-bold">No transaction for today</td>
        `;
    }

}
function getTransactions() {
    $.ajax({
        url: './../api/api_endpoint.php?transactions&today&desc&page=' + page_transaction + '&paginate=' + paginate + '&email=' + transaction_corporate + '&payment=' + transaction_payment,
        type: 'GET',
        success: function(response) {
            displayTransactions(response);
        },

    });
}

function displayEmployees(response) {
    let data = response;
    console.log(data);
    let table_employees = $('#table-employees');
    table_employees.empty();
    if (data.status === 'success' && data.employees) {
        data.employees.forEach(employee => {
            table_employees.append(`
                <div class="card shadow-sm w-100 p-2 mb-2 d-flex flex-row">
                    <div class="col-1" style="min-width:50px;min-height:50px;max-width:50px;max-height:50px">
                        <img class="w-100 h-100 border rounded-circle" src="/public/asset/images/user_icon.png" alt="${employee.username}"">
                    </div>
                    <div class="px-2">
                    <span class="d-none">${employee.id}</span>
                    <strong class="p-0">
                        ${userStatusIcon(employee.username, employee.role_type, employee.active)}
                    </strong>
                    </div>
                </div>
            `);
        });
    } else {
        let row = table_employees.insertRow(-1);
        row.innerHTML = `
            No data available
        `;
    }
}

function getEmployees() {
    $.ajax({
        url: './../api/api_endpoint.php?employees&page=' + page_employee + '&paginate=' + paginate,
        type: 'GET',
        success: function(response) {
            displayEmployees(response);
        },
        error: function(response) {
            console.log(response);
        }
    });
}

getEmployees();
getTransactions();
getCounter();

$('#pagePrevTransactions').click(function() {
    if (page_transaction > 1) {
        page_transaction--;
        getTransactions();
    }
});

$('#pageNextTransactions').click(function() {
    page_transaction++;
    getTransactions();
});

$('#pagePrevCounters').click(function() {
    if (page_counter > 1) {
        page_counter--;
        getCounter();
    }
});

$('#pageNextCounters').click(function() {
    page_counter++;
    getCounter();
});

$('#pagePrevEmployees').click(function() {
    if (page_employee > 1) {
        page_employee--;
        getEmployees();
    }
});

$('#pageNextEmployees').click(function() {
    page_employee++;
    getEmployees();
});

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
    // This why i added automated year
    dd_year.append('<option value="' + y + '">' + y + '</option>');
}

dd_year.change(function() {
    year = $(this).val();
    console.log("Selected value: " + year);
});

var dd_month = $('#month');
for (var m = 1; m <= 12; m++) {
    dd_month.append('<option value="' + m + '">' + months[m-1] + '</option>');
}
dd_month.change(function() {
    month = $(this).val();
    console.log("Selected value: " + month);
});

$('#btnGenerateReport').click(function() {
    var url = './../api/api_endpoint.php?generate-report&year=' + year + '&month=' + month;
    window.open(url, '_blank');

});

// Monitor
function rtTransaction() {
    $.ajax({
        url: './../api/api_endpoint.php?dashboard_stats',
        type: 'GET',
        success: function(response) {
            let stat = response.data[0]
            console.log(stat);
            if (response.status === 'success') {
                $('#transactions-total').text(stat.transaction_total_today);
                $('#transactions-pending').text(stat.transaction_total_pending);
                $('#transactions-completed').text(stat.transaction_total_completed);
                $('#transactions-cancelled').text(stat.transaction_total_cancelled);
            } else {
                $('#transaction-today').text('N/A');
            }
        },
        error: function(response) {
            console.log(response);
        }
    });
}

rtTransaction();

var operational = true;
var cutOff = document.getElementById('employee-cut-off');
var btn_counter_resume = document.getElementById('employee-resume');
// cutOff.addEventListener('click', function(e) {
//     e.preventDefault();
//     operational = false;
// });

btn_counter_resume.addEventListener('click', function(e) {
    e.preventDefault();
    operational = true;
});

// Chart System
let transaction_chart = document.getElementById('transaction-chart');
var transactionChart = null;
function initTransactionChart(data) {
    console.log(data);
    var chart_labels = [];
    var chart_transaction_total = [];
    if (data && data.stats && data.stats.length > 0) {
        // Current today
        if (data.stats[0].hour) {
            chart_labels = data.stats.map(stats => stats.hour);
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
    }
    // console.log(chart_labels);
    // console.log(chart_transaction_total);
    transactionChart = new Chart(transaction_chart, {
        type: 'line',
        data: {
            // use for loop for labels

            labels: chart_labels,
            datasets: [{
                label: "Transactions",
                lineTension: 0.3,
                backgroundColor: "rgba(78, 115, 223, 0.05)",
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
                // maxTicksLimit: 5,
                // padding: 10,
                // // Include a dollar sign in the ticks
                // callback: function(value, index, values) {
                // return '$' + number_format(value);
                // }
            }
            ,
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
            callbacks: {
            label: function(tooltipItem, chart) {
                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                return datasetLabel + ': $' + number_format(tooltipItem.yLabel);
            }
            }
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
        url: realHost + `/public/api/api_endpoint.php?${params}`,
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
    if (data && data.stats && data.stats.length > 0) {
        if (data.stats[0].hour) {
            chart_labels = data.stats.map(stats => stats.hour);
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
        getTransactions();
    }
}, 5000);
