$(document).ready(function() {
    var page_counter = 1;
    var page_transaction = 1;
    var page_employee = 1;

    var total_counter = 0;
    var total_transaction = 0;
    var total_employee = 0;

    var paginate = 5;

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

        table.empty();
        if (transactions.length == 0) {
            var row = `
                <tr>
                    <td colspan="2" class="text-center">No transactions found</td>
                </tr>`;
            table.append(row);
            return;
        }

        var tableHeader = `
            <tr>
                <th>Datetime</th>
                <th>Transaction Type</th>
            </tr>`;
        table.append(tableHeader);
        for (var i = 0; i < transactions.length; i++) {
            var row = `
                <tr>
                    <td>${transactions[i].transaction_time}</td>
                    <td>${transactions[i].payment}</td>
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
            url: './../api/api_transaction_history.php?page=' + page_transaction + '&paginate=' + paginate,
            type: 'GET',
            data: {
                page: page_transaction,
                paginate: paginate},
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    total_transaction = response.data.total;
                    displayTransactionHistory(response.data);
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
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
                <th>Counter No.</th>
                <th>Queue Count</th>
            </tr>`;
        table.append(tableHeader);
        for (var i = 0; i < counters.length; i++) {
            var row = `
                <tr>
                    <td>${counters[i].idcounter}</td>
                    <td>${counters[i].idemployee}</td>
                    <td>${counters[i].counterNumber}</td>
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
        for (var i = 0; i < employees.length; i++) {
            var row = `
                <tr>
                    <td>${employees[i].id}</td>
                    <td>${employees[i].username}</td>
                    <td>${employees[i].created_at}</td>
                </tr>`;
            table.append(row);
        }
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

    getEmployees();

});