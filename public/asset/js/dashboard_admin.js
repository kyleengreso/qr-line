$(document).ready(function() {
    var page_counter = 1;
    var page_transaction = 1;
    var page_employee = 1;

    var total_counter = 0;
    var total_transaction = 0;
    var total_employee = 0;

    var paginate = 5;
    var transaction_corporate = "none";
    var transaction_payment = "none";

    function getCounter() {
        $.ajax({
            url: './../api/api_endpoint.php?counters&page=' + page_counter + '&paginate=' + paginate,
            type: 'GET',
            success: function(response) {
                // console.log(response);)
                var table_counters = $('#table-counters');
                table_counters.empty();
                table_counters.append(`
                    <tr>
                        <th class="col-3">#</th>
                        <th>Employee</th>
                        <th>Queue Count</th>
                    </tr>`);
                if (response.status === 'success') {
                    // SORT ASSENDING
                    if (response && response.counters) {
                        response.counters.sort((a,b) => {
                            return a.counterNumber = b.counterNumber;
                        });
                    }
                    response.counters.forEach(element => {
                        table_counters.append(`
                            <tr>
                                <td>${element.idcounter}</td>
                                <td>
                                    <strong>
                                    ${userStatusIcon(element.username, element.role_type, element.active)}</td>
                                    </strong>
                                <td>${element.queue_count}</td>
                            </tr>`);
                    });
                } else {
                    table_counters.append('<tr><td colspan="3">No data available</td></tr>');
                }
            },
            error: function(response) {
                console.log(response);
            }
        });
    }

    function getTransactions() {
        $.ajax({
            url: './../api/api_endpoint.php?transactions&today&desc&page=' + page_transaction + '&paginate=' + paginate + '&email=' + transaction_corporate + '&payment=' + transaction_payment,
            type: 'GET',
            success: function(response) {
                console.log(response);
                var table_transactions = $('#table-transaction-history');
                table_transactions.empty();
                table_transactions.append(`
                    <tr>
                        <th class="col-3">#</th> 
                        <th>Email</th>
                        <th>Payment</th>
                    </tr>`);
                var data = response.transactions;
                if (response.status === 'success') {
                    // use foreach
                    response.transactions.forEach(element => {
                        table_transactions.append(`
                            <tr>
                                <td>${element.queue_number}</td>
                                <td>${element.email}</td>
                                <td>${element.payment}</td>
                            </tr>`);
                    });
                } else {
                    table_transactions.append(`<tr>
                        <td colspan="3">No data available</td>
                        </tr>`);
                }
            },

        });
    }

    function getEmployees() {
        $.ajax({
            url: './../api/api_endpoint.php?employees&page=' + page_employee + '&paginate=' + paginate,
            type: 'GET',
            success: function(response) {
                var table_employees = $('#table-employees');
                table_employees.empty();
                table_employees.append(`
                    <tr>
                        <th class="col-3">#</th>
                        <th>Employee</th>
                    </tr>`);
                if (response.status === 'success') {
                    response.employees.forEach(element => {
                        table_employees.append(`
                            <tr>
                                <td class="col-2">${element.id}</td>
                                <td>
                                    <strong>
                                    ${userStatusIcon(element.username, element.role_type, element.active)}
                                    </strong>
                                </td>
                            </tr>`);
                    });
                } else {
                    table_employees.append(`<tr>
                        <td colspan="3">No data available</td>
                    </tr>`);
                }
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
        // if (transaction === 'corporate') {
        //     $('#transaction-email-select').show();
        //     $('#transaction-history-filter-payment').show();
        //     transaction_corporate = "none";
        //     transaction_payment = "none";
        // } else {
        //     $('#transaction-email-select').hide();
        //     $('#transaction-history-filter-payment').hide();
        //     transaction_corporate = "none";
        //     transaction_payment = "none";
        // }
        // getTransactions();
    });

    // Status: Merged
    // $('#transaction-email-select').change(function() {
    //     var email = $(this).val();
    //     transaction_corporate = email;
    //     getTransactions();
    // });

    // $('#transaction-history-filter-payment').change(function() {
    //     var payment = $(this).val();
    //     transaction_payment = payment;
    //     getTransactions();
    // });

    // Generate Report
    var year = 2024;
    var month = 1;
    var months = ['January', 'February', 'March',
                    'April', 'May', 'June',
                    'July', 'August', 'September',
                    'October', 'November', 'December'];
    var dd_year = $('#year');
    for (var y = 2020; y <= 2040; y++) {
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

    var protocol = window.location.protocol;
    var host = window.location.host;
    var realHost = protocol + '//' + host;

    // Dashboard Chart
    function getDashboardStat() {
        let resp = null;
        console.log(realHost);
        $.ajax({
            url: realHost + "/public/api/api_endpoint.php?dashboard_stats&day",
            type: "GET",
            async: false,
            success: function(response) {
                resp = response;
            },
            error: function(xhr, status, error) {
                console.error("Error fetching dashboard stats:", error); // Handle errors
            }
        });
        return resp;
    }

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
    cutOff.addEventListener('click', function(e) {
        e.preventDefault();
        operational = false;
    });
    
    btn_counter_resume.addEventListener('click', function(e) {
        e.preventDefault();
        operational = true;
    });
    setInterval(function() {
        if (operational) {
            rtTransaction();
            getTransactions();
        }
    }, 5000);
});