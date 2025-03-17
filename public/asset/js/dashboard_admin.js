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

    function getTotalCounter() {
        return $.ajax({
            url: './../api/api_counter.php?total_count',
            type: 'GET'
        });
    }

    function getTotalTransaction() {
        return $.ajax({
            url: './../api/api_transaction_history.php?total_count',
            type: 'GET'
        });
    }

    function getTotalEmployee() {
        return $.ajax({
            url: './../api/api_employee.php?total_count',
            type: 'GET'
        });
    }

    $.when(getTotalCounter(), getTotalTransaction(), getTotalEmployee()).done(function(counterResponse, transactionResponse, employeeResponse) {
        if (counterResponse[0].status === 'success') {
            total_counter = counterResponse[0].data.total;
        }
        if (transactionResponse[0].status === 'success') {
            total_transaction = transactionResponse[0].data.total;
        }
        if (employeeResponse[0].status === 'success') {
            total_employee = employeeResponse[0].data.total;
        }
    
        // Print out the total count... but i cant escape
        console.log(`Total Counter: ${total_counter}`);
        console.log(`Total Transaction: ${total_transaction}`);
        console.log(`Total Employee: ${total_employee}`);
    });

    $('#pagePrevCounters').click(function(e) {
        e.preventDefault();
        if (page_counter > 1) {
            page_counter--;
        } else {
            page_counter = 1;
        }
        getCounters();
    });

    $('#pageNextCounters').click(function(e) {
        e.preventDefault();
        page_counter++;
        getCounters();
    });
    function displayTransactionHistory(transactions) {
        var table = $('#table-transaction-history');

        table.empty()

        var tableHeader = `
            <tr>
                <th class="p-2">Datetime</th>
                <th class="p-2">Status</th>
                <th class="p-2">Payment</th>
                <th class="p-2">Employee</th>
                <th class="p-2">Counter No.</th>
                <th class="p-2">Email</th>
            </tr>`;
        table.append(tableHeader);

        if (transactions.status === 'empty') {
            var row = `
                <tr>
                    <td colspan="6" class="text-center">No transactions found</td>
                </tr>`;
            table.append(row);
            return;
        }

        transactions = transactions.data;

        for (var i = 0; i < transactions.length; i++) {
            var row = `
                <tr>
                    <td class="p-2">${transactions[i].transaction_time}</td>
                    <td class="p-2">${transactions[i].status}</td>
                    <td class="p-2">${transactions[i].payment}</td>
                    <td class="p-2">${transactions[i].employee_name}</td>
                    <td class="p-2">${transactions[i].idcounter}</td>
                    <td class="p-2">${transactions[i].email}</td>
                </tr>`;
            table.append(row);
        }
    }

    if ($('#pageNextTransaction').length) {
        $('#pageNextTransaction').click(function() {
            nextPaginateTransactions();
        });
    } else if ($('#pagePrevTransaction').length) {
        $('#pagePrevTransaction').click(function() {
            prevPaginateTransactions();
        });
    }

    getTransactions();

    function getTransactions() {
        $.ajax({
            url: `./../api/api_transaction_history.php?page=${page_transaction}&paginate=${paginate}&corporate=${transaction_corporate}&payment=${transaction_payment}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success' || response.status === 'empty') {
                    // total_transaction = response.data;
                    // console.log(response.data);
                    displayTransactionHistory(response);
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                console.error('Response Text:', xhr.responseText); // Log the response text
            }
        });
    }

    $('#pagePrevEmployees').click(function() {
        if (page_employee > 1) {
            page_employee--;
        } else {
            page_employee = 1;
        }
        getEmployees();
    });

    $('#pageNextEmployees').click(function() {
        if (page_employee < total_employee) {
            page_employee++;
        } 
        getEmployees();
    });

    function displayCounters(counters) {
        var table = $('#table-counters');
        table.empty();
        var tableHeader = `
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Queue Count</th>
            </tr>`;
        table.append(tableHeader);
        if (counters.length == 0) {
            var row = `
                <tr>
                    <td colspan="3" class="text-center">No counters found</td>
                </tr>`;
            table.append(row);
            return;
        }
        
        for (var i = 0; i < counters.length; i++) {
            var row = `
                <tr>
                    <td>${counters[i].counterNumber}</td>
                    <td>${counters[i].username}</td>
                    <td>${counters[i].queue_count}</td>
                </tr>`;
            table.append(row);
        }
    }

    function getCounters() {
        $.ajax({
            url: './../api/api_counter.php?page=' + page_counter + '&paginate=' + paginate,
            type: 'GET',
            data: {
                page: page_counter,
                paginate: paginate},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    total_counter = response.data.total;
                    displayCounters(response.data);
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    getCounters();

    if ($('#pageNextCounter').length) {
        $('#pageNextCounter').click(function() {
            nextPaginateCounters();
        });
    } else if ($('#pagePrevCounter').length) {
        $('#pagePrevCounter').click(function() {
            prevPaginateCounters();
        });
    }   

    $('#pagePrevTransactions').click(function() {
        if (page_transaction > 1) {
            page_transaction--;
        }
        getTransactions();
    });

    $('#pageNextTransactions').click(function() {
        page_transaction++;
        getTransactions();
    });

    function displayEmployees(employees) {
        var table = $('#table-employees');
        table.empty();
        if (employees.length == 0) {
            var row = `
                <tr>
                    <td colspan="3" class="text-center">No employees found</td>
                </tr>`;
            table.append(row);
            return;
        }

        var tableHeader = `
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Created At</th>
            </tr>`;
        table.append(tableHeader);
        employees.forEach(function(employee) {
            var row = `
                <tr>
                    <td>${employee.id}</td>
                    <td>${employee.username}</td>
                    <td>${employee.created_at}</td>
                </tr>`;
            table.append(row);
        });
    }

    if ($('#pageNextEmployee').length) {
        $('#pageNextEmployee').click(function() {
            nextPaginateEmployees();
        });
    } else if ($('#pagePrevEmployee').length) {
        $('#pagePrevEmployee').click(function() {
            prevPaginateEmployees();
        });
    }

    function getEmployees() {
        $.ajax({
            url: './../api/api_employee.php?page=' + page_employee + '&paginate=' + paginate,
            type: 'GET',
            data: {
                page: page_employee,
                paginate: paginate
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    displayEmployees(response.data);
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    getEmployees();

    async function displayTransctionRecords() {
        var token = localStorage.getItem('token');
        if (!token) {
            $.ajax({
                url: './../api/api_authenticate.php',
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    localStorage.removeItem('token');
                    window.location.href = './../auth/login.php';
                },
                error: function(xhr, status, error) {
                    localStorage.removeItem('token');
                    window.location.href = './../auth/login.php';
                }
            });
        } else {
            var role = atob(localStorage.getItem('token')).split('!!')[3];
            var token = localStorage.getItem('token');
            var data = {
                "method" : "dashboard_"+role,
                "token" : token
            }

            $.ajax({
                url: './../api/api_monitor.php',
                type: 'POST',
                data: JSON.stringify(data),
                dataType: 'json',
                success: function(response) {
                    // console.log(response);
                    if (response.status === 'success') {
                        var total_transactions = response.total_transactions;
                        var pending_transactions = response.pending_transactions;
                        var completed_transactions = response.completed_transactions;
                        var canceled_transactions = response.canceled_transactions;

                        $('#transactions-total').text(total_transactions);
                        $('#transactions-pending').text(pending_transactions);
                        $('#transactions-completed').text(completed_transactions);
                        $('#transactions-canceled').text(canceled_transactions);
                    } else {
                        console.log('Error:', response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
    }

    // Display transaction records every 5 seconds
    setInterval(
        displayTransctionRecords, 5000
    );
    

    // Transaction History if corporate or not
    $('#transaction-email-select').change(function() {
        var email = $(this).val();
        transaction_corporate = email;
        getTransactions();
    });

    $('#transaction-history-filter-payment').change(function() {
        var payment = $(this).val();
        transaction_payment = payment;
        getTransactions();
    });

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
        // new tab using windows.location.href
        var url = './../api/api_generate_report.php?year=' + year + '&month=' + month;
        window.open(url, '_blank');

    });
});