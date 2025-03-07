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

    window.nextPaginateTransactions = function() {
        page_transaction++;
        getTransactions();
    }

    window.prevPaginateTransactions = function() {
        if (page_transaction > 1) {
            page_transaction--;
        } else {
            page_transaction = 1;
        }
        getTransactions();
    }

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
            url: './../api/api_transaction_history.php?page=' + page_transaction + '&paginate=' + paginate + "&corporate=" + transaction_corporate + "&payment=" + transaction_payment, 
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





    window.nextPaginateCounters = function() {
        page_counter++;
        getCounters();
    }

    window.prevPaginateCounters = function() {
        if (page_counter > 1) {
            page_counter--;
        } else {
            page_counter = 1;
        }
        getCounters();
    }

    function displayCounters(counters) {
        var table = $('#table-counters');

        table.empty();
        if (counters.length == 0) {
            var row = `
                <tr>
                    <td colspan="4" class="text-center">No counters found</td>
                </tr>`;
            table.append(row);
            return;
        }
        
        var tableHeader = `
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Queue Count</th>
            </tr>`;
        table.append(tableHeader);
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


    window.nextPaginateEmployees = function() {
        page_employee++;
        $.ajax({
            url : './../api/api_employee.php?page=' + page_employee + '&paginate=' + paginate,
            type: 'GET',
            data: {
                page: page_employee,
                paginate: paginate},
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

    window.prevPaginateEmployees = function() {
        if (page_employee > 1) {
            page_employee--;
        } else {
            page_employee = 1;
        }
        $.ajax({
            url : './../api/api_employee.php?page=' + page_employee + '&paginate=' + paginate,
            type: 'GET',
            data: {
                page: page_employee,
                paginate: paginate},
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

    setInterval(
        displayTransctionRecords, 5000
    );
    
    // At Transaction History can do filter
    var btn_transaction_history_filter_corporate = $('#transaction-history-filter-corporate');
    var btn_transaction_history_filter_non_corporate = $('#transaction-history-filter-non-corporate');
    var transaction_history_filter_email = $('#transaction-history-filter-email');

    btn_transaction_history_filter_corporate.click(function() {
        transaction_corporate = "true";
        transaction_history_filter_email.text("Corporate");
        getTransactions();
    });

    btn_transaction_history_filter_non_corporate.click(function() {
        transaction_corporate = "false";
        transaction_history_filter_email.text("Non-Corporate");
        getTransactions();
    });

    var btn_payment_registrar = $('#transaction-history-filter-registrar');
    var btn_payment_assessment = $('#transaction-history-filter-assessment');
    var btn_transaction_history_filter_payment = $('#transaction-history-filter-payment');

    btn_payment_registrar.click(function() {
        transaction_payment = "registrar";
        btn_transaction_history_filter_payment.text("Registrar");
        getTransactions();
    });

    btn_payment_assessment.click(function() {
        transaction_payment = "assessment";
        btn_transaction_history_filter_payment.text("Assessment");
        getTransactions();
    });


});