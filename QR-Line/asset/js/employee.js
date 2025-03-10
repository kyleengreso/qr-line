$(document).ready(function() {

    // Add employee
    function addEmployee(employee) {
        var form = $('#frmAddEmployee');
        $.ajax({
            url: './../api/api_employee.php',
            type: 'POST',
            data: JSON.stringify(employee),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    message_success(form, response.message);
                    setTimeout(function() {
                        window.location.href = "./employees.php";
                    }, 1000);
                } else {
                    message_error(form, response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                message_error(form, 'An error occurred while adding the employee');
            }
        });
    }

    function updateEmployee(employee) {
        var form = $('#frmUpdateEmployee');
        $.ajax({
            url: './../api/api_employee.php?id=' + employee.id,
            type: 'POST',
            data: JSON.stringify(employee),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    message_success(form, response.message);
                    setTimeout(function() {
                        window.location.href = "./employees.php";
                    }, 500);
                } else {
                    message_error(form, response.message);
                }
            },
            error: function(status) {
                console.error('AJAX Error:', status);
                message_error(form, 'An error occurred while updating the employee');
            }
        });
    }

    function loadEmployee(id) {
        $.ajax({
            url: './../api/api_employee.php?id=' + id,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var employee = response.data;
                    $('#username').val(employee.username);
                    $('#username').text(employee.username);
                } else {
                    console.log('Error:', response.message);
                }},
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    function deleteEmployee(employee) {
        var form = $('#frmDeleteEmployee');
        $.ajax({
            url: './../api/api_employee.php?id=' + employee.id,
            type: 'POST',
            data: JSON.stringify(employee),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    message_success(form, response.message);
                    setTimeout(function() {
                        window.location.href = "./employees.php";
                    }, 500);
                } else {
                    message_error(form, response.message);
                }
            },
            error: function(status) {
                console.error('AJAX Error:', status);
                message_error(form, 'An error occurred while deleting the employee');
            }
        });
    }

    function loadEmployees(searchQuery = '') {
        $.ajax({
            url: './../api/api_employee.php',
            type: 'GET',
            data: { search: searchQuery },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    var table = $('#table-employees');
                    table.empty();
                    var employees = response.data;
                    var tableHeader = `
                        <tr>
                            <th>#</th>
                            <th>Username</th>
                            <th>Created At</th>
                            <th>Action</th>
                        </tr>`;
                    table.append(tableHeader);
                    if (employees.length == 0) {
                        var row = `
                            <tr>
                                <td colspan="2" class="text-center">No employees found</td>
                            </tr>`;
                        table.append(row);
                        return;
                    } else {
                        employees.forEach(function(employee) {
                            var row = `
                                <tr>
                                    <td>${employee.id}</td>
                                    <td>${employee.username}</td>
                                    <td>${employee.created_at}</td>
                                    <td>
                                        <div class="d-flex flex-column flex-md-row justify-content-center p-0 w-100">
                                            <button class="btn btn-primary m-1" onclick="window.location.href='./edit_employee.php?id=${employee.id}'">Update</button>
                                            <button class="btn btn-danger m-1" onclick="window.location.href='./delete_employee.php?id=${employee.id}'">Delete</button>
                                        </div>
                                    </td>
                                </tr>`;
                            table.append(row);
                        });
                    }
                } else {
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    if ($('#table-employees').length) {
        loadEmployees();
    }

    if ($('#frmAddEmployee').length) {
        $('#frmAddEmployee').submit(function(e) {
            e.preventDefault();
            var employee = {
                username: $('#username').val(),
                password: $('#password').val(),
                confirm_password: $('#confirm_password').val(),
                "set_method": "create"
            };
            addEmployee(employee);
        });
    }

    if ($('#frmUpdateEmployee').length) {
        id = new URLSearchParams(window.location.search).get('id');
        loadEmployee(id);
        $('#frmUpdateEmployee').submit(function(e) {
            e.preventDefault();
            password = $('#password').val();
            confirm_password = $('#confirm_password').val();

            if (password.length == 0 || confirm_password.length == 0) {
                message_error('#frmUpdateEmployee', 'Password cannot be empty');
                return;
            }

            var employee = {
                id: id,
                password: password,
                confirm_password: confirm_password,
                "set_method": "update"
            };
            updateEmployee(employee);
        });
    }

    if ($('#frmDeleteEmployee').length) {
        id = new URLSearchParams(window.location.search).get('id');
        $('#frmDeleteEmployee').ready(function() {
            loadEmployee(id);
        });
        $('#frmDeleteEmployee').submit(function(e) {
            e.preventDefault();
            var employee = {
                id: id,
                "set_method": "delete"
            };
            deleteEmployee(employee); 
        });
    }
});