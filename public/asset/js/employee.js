$(document).ready(function() {
    var search = '';
    var page_employee = 1;
    var paginate = 5;

    var employeeData = null;

    // Add employee
    function addEmployee(employee) {
        var form = $('#frmAddEmployee');
        $.ajax({
            url: './../api/api_endpoint.php',
            type: 'POST',
            data: JSON.stringify(employee),
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
        console.log(employee);
        var form = $('#frmUpdateEmployee');
        $.ajax({
            url: './../api/api_endpoint.php',
            type: 'POST',
            data: JSON.stringify(employee),
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
        let resp = null;
    
        $.ajax({
            url: './../api/api_endpoint.php?employees&id=' + id,
            async: false,
            success: function(response) {
                if (response.status === 'success') {
                    $('#username').text(response.employee.username);
                    $('#email').val(response.employee.email);
                    $('#status').val(response.employee.active);
                    resp = response;
                } else {
                    console.error('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    
        return resp; 
    }
    

    function deleteEmployee(employee) {
        var form = $('#frmDeleteEmployee');
        $.ajax({
            url: './../api/api_endpoint.php',
            type: 'POST',
            data: JSON.stringify(employee),
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

    var role_type = 'none';
    function loadEmployees() {
        $.ajax({
            url: './../api/api_endpoint.php?employees&page=' + page_employee + '&paginate=' + paginate + '&search=' + search + '&role_type=' + role_type,
            type: 'GET',
            success: function(response) {
                var table_employees = $('#table-employees');
                table_employees.empty();
                table_employees.append(`
                    <tr>
                        <th class="col-2">#</th>
                        <th>Username</th>
                        <th>Actions</th>
                    </tr>`);

                if (response.status === 'success') {
                    response.employees.forEach(element => {
                        var d_employee_icon = null;
                        var d_username = null;
                        var d_employee_active = null;
                        var d_role_type = null;

                        if (element.role_type === 'admin') {
                            if (element.active == 1) {
                                d_employee_icon = `<i class="fas fa-user-shield role_type_admin_icon"></i>`;
                                d_username = `<span class="role_type_admin_icon">${element.username}</span>`;
                            } else if (element.active == 0) {
                                d_employee_icon = `<i class="fa-solid fa-user-slash employeeActive-no-icon"></i>`;
                                d_username = `<span class="employeeActive-no-icon">${element.username}</span>`;
                            }
                            d_role_type = `<div class="role_type_admin">Admin</div>`;
                        } else if (element.role_type === 'employee') {
                            if (element.active == 1) {
                                d_employee_icon = `<i class="fas fa-user role_type_employee_icon"></i>`;
                                d_username = `<span class="role_type_employee_icon">${element.username}</span>`;
                            } else if (element.active == 0) {
                                d_employee_icon = `<i class="fa-solid fa-user-slash employeeActive-no-icon"></i>`;
                                d_username = `<span class="employeeActive-no-icon">${element.username}</span>`;
                            }
                            // d_role_type = `<div class="role_type_employee">Cashier</div>`;
                        }
                        table_employees.append(`
                            <tr>
                                <td class="col-2">${element.id}</td>
                                <td>
                                    <strong>
                                    ${d_employee_icon}
                                    ${d_username}
                                    </strong>
                                </td>
                                <td>
                                    <a class="btn btn-outline-info text-info" id="view-employee-${element.id}" data-toggle="modal" data-target="#viewEmployeeModal" style="border-top-right-radius:0px;border-bottom-right-radius:0px">View</a>
                                    <a class="btn btn-outline-primary text-primary" id="update-employee-${element.id}" data-toggle="modal" data-target="#updateEmployeeModal" style="border-top-right-radius:0px;border-bottom-right-radius:0px;border-top-left-radius:0px;border-bottom-left-radius:0px">Update</a>
                                    <a class="btn btn-outline-danger text-danger" id="delete-employee-${element.id}" data-toggle="modal" data-target="#deleteEmployeeModal" style="border-top-left-radius:0px;border-bottom-left-radius:0px">Delete</a>
                                </td>
                            </tr>`);
                    });
                } else {
                    table_employees.append(`<tr>
                        <td colspan="5">No data available</td>
                    </tr>`);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // Add Employee Modal
    $(document).on('click', '#btn-add-employee', function(e) {
        e.preventDefault();
        // Clear the form
        let addEmployeeTitle = document.getElementById('addEmployeeTitle');
        addEmployeeTitle.textContent = 'Add Employee';

        let addEmployeeBody = document.getElementById('addEmployeeBody');
        addEmployeeBody.innerHTML = '';
        let addEmployeeForm = document.createElement('form');
        addEmployeeForm.id = 'frmAddEmployee';
        addEmployeeForm.method = 'POST';

        // Username field
        let usernameContainer = document.createElement('div');
        usernameContainer.classList.add('mb-4');

        let usernameInputGroup = document.createElement('div');
        usernameInputGroup.classList.add('input-group');

        let usernameSpan = document.createElement('span');
        usernameSpan.classList.add('input-group-text');

        let usernameIcon = document.createElement('i');
        usernameIcon.classList.add('fas', 'fa-user');
        usernameSpan.appendChild(usernameIcon);

        let usernameInput = document.createElement('input');
        usernameInput.type = 'text';
        usernameInput.name = 'username';
        usernameInput.id = 'username';
        usernameInput.classList.add('form-control');
        usernameInput.placeholder = 'Username';
        usernameInput.required = true;

        usernameInputGroup.appendChild(usernameSpan);
        usernameInputGroup.appendChild(usernameInput);
        usernameContainer.appendChild(usernameInputGroup);
        addEmployeeForm.appendChild(usernameContainer);

        // Password field
        let passwordContainer = document.createElement('div');
        passwordContainer.classList.add('mb-4');

        let passwordInputGroup = document.createElement('div');
        passwordInputGroup.classList.add('input-group');

        let passwordSpan = document.createElement('span');
        passwordSpan.classList.add('input-group-text');

        let passwordIcon = document.createElement('i');
        passwordIcon.classList.add('fas', 'fa-lock');
        passwordSpan.appendChild(passwordIcon);

        let passwordInput = document.createElement('input');
        passwordInput.type = 'password';
        passwordInput.name = 'password';
        passwordInput.id = 'password';
        passwordInput.classList.add('form-control');
        passwordInput.placeholder = 'Password';
        passwordInput.required = true;

        passwordInputGroup.appendChild(passwordSpan);
        passwordInputGroup.appendChild(passwordInput);
        passwordContainer.appendChild(passwordInputGroup);
        addEmployeeForm.appendChild(passwordContainer);

        // Confirm Password field
        let confirmPasswordContainer = document.createElement('div');
        confirmPasswordContainer.classList.add('mb-4');

        let confirmPasswordInputGroup = document.createElement('div');
        confirmPasswordInputGroup.classList.add('input-group');

        let confirmPasswordSpan = document.createElement('span');
        confirmPasswordSpan.classList.add('input-group-text');

        let confirmPasswordIcon = document.createElement('i');
        confirmPasswordIcon.classList.add('fas', 'fa-lock');
        confirmPasswordSpan.appendChild(confirmPasswordIcon);

        let confirmPasswordInput = document.createElement('input');
        confirmPasswordInput.type = 'password';
        confirmPasswordInput.name = 'confirm_password';
        confirmPasswordInput.id = 'confirm_password';
        confirmPasswordInput.classList.add('form-control');
        confirmPasswordInput.placeholder = 'Confirm password';
        confirmPasswordInput.required = true;

        confirmPasswordInputGroup.appendChild(confirmPasswordSpan);
        confirmPasswordInputGroup.appendChild(confirmPasswordInput);
        confirmPasswordContainer.appendChild(confirmPasswordInputGroup);
        addEmployeeForm.appendChild(confirmPasswordContainer);

        // Email field
        let emailContainer = document.createElement('div');
        emailContainer.classList.add('mb-4');

        let emailInputGroup = document.createElement('div');
        emailInputGroup.classList.add('input-group');

        let emailSpan = document.createElement('span');
        emailSpan.classList.add('input-group-text');

        let emailIcon = document.createElement('i');
        emailIcon.classList.add('fas', 'fa-envelope');
        emailSpan.appendChild(emailIcon);                                                   

        let emailInput = document.createElement('input');
        emailInput.type = 'email';
        emailInput.name = 'email';
        emailInput.id = 'email';
        emailInput.classList.add('form-control');
        emailInput.placeholder = 'Email';
        emailInput.required = true;

        emailInputGroup.appendChild(emailSpan);
        emailInputGroup.appendChild(emailInput);
        emailContainer.appendChild(emailInputGroup);
        addEmployeeForm.appendChild(emailContainer);

        // Role field                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
        let roleContainer = document.createElement('div');
        roleContainer.classList.add('mb-4');

        let roleSelect = document.createElement('select');
        roleSelect.classList.add('form-select');
        roleSelect.name = 'role_type';
        roleSelect.id = 'role_type';
        roleSelect.required = true;

        let roleOptionDefault = document.createElement('option');
        roleOptionDefault.value = '';
        roleOptionDefault.textContent = 'Select Role';
        roleSelect.appendChild(roleOptionDefault);

        let roleOptionAdmin = document.createElement('option');
        roleOptionAdmin.value = 'admin';
        roleOptionAdmin.textContent = 'Admin';
        roleSelect.appendChild(roleOptionAdmin);

        let roleOptionEmployee = document.createElement('option');
        roleOptionEmployee.value = 'employee';
        roleOptionEmployee.textContent = 'Employee';
        roleSelect.appendChild(roleOptionEmployee);

        roleContainer.appendChild(roleSelect);
        addEmployeeForm.appendChild(roleContainer);

        // Status field
        let statusContainer = document.createElement('div');
        statusContainer.classList.add('mb-4', 'form-check', 'form-switch');

        let statusInput = document.createElement('input');
        statusInput.classList.add('form-check-input');
        statusInput.type = 'checkbox';
        statusInput.name = 'status';
        statusInput.id = 'status';
        statusInput.value = '1';

        let statusLabel = document.createElement('label');
        statusLabel.classList.add('form-check-label');
        statusLabel.textContent = 'Activate Employee';
        statusLabel.setAttribute('for', 'status');

        statusContainer.appendChild(statusInput);
        statusContainer.appendChild(statusLabel);
        addEmployeeForm.appendChild(statusContainer);

        // Buttons container
        let buttonsContainer = document.createElement('div');
        buttonsContainer.classList.add('mb-4');

        // let submitButton = document.createElement('button');
        // submitButton.type = 'submit';
        // submitButton.classList.add('btn', 'btn-primary', 'w-100', 'mb-2');
        // submitButton.textContent = 'Add Employee';
        // buttonsContainer.appendChild(submitButton);

        // let cancelButton = document.createElement('a');
        // cancelButton.classList.add('btn', 'btn-secondary', 'w-100');
        // cancelButton.textContent = 'Cancel';
        // cancelButton.href = 'employees.php';
        // buttonsContainer.appendChild(cancelButton);

        // addEmployeeForm.appendChild(buttonsContainer);
        addEmployeeBody.appendChild(addEmployeeForm);
    });

    $('#btnAddEmployee').click(function(e) {
        console.log('OK');
        e.preventDefault();
        password = $('#password').val();
        confirm_password = $('#confirm_password').val();
        if (password !== confirm_password) {
            message_error('#frmAddEmployee', 'Passwords do not match');
            return;
        }

        var employee = {
            username: $('#username').val(),
            password: $('#password').val(),
            email: $('#email').val(),
            role_type: $('#role_type').val(),
            active: $('#status').is(':checked') ? 1 : 0,
            method: "employees-add"
        };
        addEmployee(employee);
    });

    // View Employee Modal
    $(document).on('click', '[id^="view-employee-"]', function() {
        const user_id = $(this).attr('id').split('-')[2];
        const employeeData = loadEmployee(user_id); // Replace with your actual function to fetch employee data
    
        const viewEmployeeTitle = document.getElementById('viewEmployeeTitle');
        if (viewEmployeeTitle) {
            viewEmployeeTitle.textContent = 'View Employee: ' + employeeData.employee.username;
        } else {
            console.error('Element with ID "viewEmployeeTitle" not found.');
        }
    
        const viewEmployeeBody = document.getElementById('viewEmployeeBody');
        if (viewEmployeeBody) {
            // Clear any existing content in the modal body
            viewEmployeeBody.innerHTML = `
                <div class="mb-2 row">
                    <div class="col-6">User ID</div>
                    <div class="col-6 fw-bold">${employeeData.employee.id}</div>
                </div>
                <div class="mb-2 row">
                    <div class="col-6">Username:</div>
                    <div class="col-6 fw-bold">${employeeData.employee.username}</div>
                </div>
                <div class="mb-2 row">
                    <div class="col-6">Active</div>
                    <div class="col-6 fw-bold">${employeeData.employee.active ? 'Yes' : 'No'}</div>
                </div>
                <div class="mb-2 row">
                    <div class="col-6">Role Type</div>
                    <div class="col-6 fw-bold">${employeeData.employee.role_type}</div>
                </div>
                <div class="mb-2 row">
                    <div class="col-6">Last Login</div>
                    <div class="col-6 fw-bold">${employeeData.employee.employee_last_login}</div>
                </div>
                <div class="mb-2 row">
                    <div class="col-6">Created At</div>
                    <div class="col-6 fw-bold">${employeeData.employee.created_at}</div>
                </div>
            `;
        } else {
            console.error('Element with ID "viewEmployeeBody" not found.');
        }
    
        console.log(employeeData);
    });


    // Update Employee Modal
    $(document).on('click', '[id^="update-employee-"]', function() {
        user_id = $(this).attr('id').split('-')[2];
        var employeeData = loadEmployee(user_id);
        console.log(employeeData);
        // Update the modal title
        let updateEmployeeTitle = document.getElementById('updateEmployeeTitle');
        if (updateEmployeeTitle) {
            updateEmployeeTitle.textContent = 'Update Employee: ' + employeeData.employee.username;
        } else {
            console.error('Element with ID "updateEmployeeTitle" not found.');
        }

        // Get the modal body container
        let updateEmployeeBody = document.getElementById('updateEmployeeBody');
        if (updateEmployeeBody) {
            // I will try to mix with teaching from WebSys... for not using $(...) :)

            // Clear any existing content in the modal body
            updateEmployeeBody.innerHTML = '';

            // Create the form
            let updateEmployeeForm = document.createElement('form');
            updateEmployeeForm.id = 'frmUpdateEmployee';
            updateEmployeeForm.method = 'POST';

            // Username field
            let usernameContainer = document.createElement('div');
            usernameContainer.classList.add('mb-3');
            let usernameInputGroup = document.createElement('div');
            usernameInputGroup.classList.add('input-group');
            let usernameSpan = document.createElement('span');
            usernameSpan.classList.add('input-group-text');
            let usernameIcon = document.createElement('i');
            usernameIcon.classList.add('fas', 'fa-user');
            usernameSpan.appendChild(usernameIcon);
            let usernameInput = document.createElement('input');
            usernameInput.type = 'text';
            usernameInput.id = 'username';
            console.log(employeeData.employee);
            usernameInput.value = employeeData.employee.username;
            usernameInput.classList.add('form-control');
            usernameInput.placeholder = 'Username';
            usernameInput.disabled = true;
            usernameInputGroup.appendChild(usernameSpan);
            usernameInputGroup.appendChild(usernameInput);
            usernameContainer.appendChild(usernameInputGroup);
            updateEmployeeForm.appendChild(usernameContainer);

            // Password field
            let passwordContainer = document.createElement('div');
            passwordContainer.classList.add('mb-3');
            let passwordInputGroup = document.createElement('div');
            passwordInputGroup.classList.add('input-group');
            let passwordSpan = document.createElement('span');
            passwordSpan.classList.add('input-group-text');
            let passwordIcon = document.createElement('i');
            passwordIcon.classList.add('fas', 'fa-lock');
            passwordSpan.appendChild(passwordIcon);
            let passwordInput = document.createElement('input');
            passwordInput.type = 'password';
            passwordInput.name = 'password';
            passwordInput.id = 'password';
            passwordInput.classList.add('form-control');
            passwordInput.placeholder = 'Password';
            passwordInputGroup.appendChild(passwordSpan);
            passwordInputGroup.appendChild(passwordInput);
            passwordContainer.appendChild(passwordInputGroup);
            updateEmployeeForm.appendChild(passwordContainer);

            // Confirm Password field
            let confirmPasswordContainer = document.createElement('div');
            confirmPasswordContainer.classList.add('mb-3');
            let confirmPasswordInputGroup = document.createElement('div');
            confirmPasswordInputGroup.classList.add('input-group');
            let confirmPasswordSpan = document.createElement('span');
            confirmPasswordSpan.classList.add('input-group-text');
            let confirmPasswordIcon = document.createElement('i');
            confirmPasswordIcon.classList.add('fas', 'fa-lock');
            confirmPasswordSpan.appendChild(confirmPasswordIcon);
            let confirmPasswordInput = document.createElement('input');
            confirmPasswordInput.type = 'password';
            confirmPasswordInput.name = 'confirm_password';
            confirmPasswordInput.id = 'confirm_password';
            confirmPasswordInput.classList.add('form-control');
            confirmPasswordInput.placeholder = 'Confirm Password';
            confirmPasswordInputGroup.appendChild(confirmPasswordSpan);
            confirmPasswordInputGroup.appendChild(confirmPasswordInput);
            confirmPasswordContainer.appendChild(confirmPasswordInputGroup);
            updateEmployeeForm.appendChild(confirmPasswordContainer);

            // Email field
            let emailContainer = document.createElement('div');
            emailContainer.classList.add('mb-4');
            let emailInputGroup = document.createElement('div');
            emailInputGroup.classList.add('input-group');
            let emailSpan = document.createElement('span');
            emailSpan.classList.add('input-group-text');
            let emailIcon = document.createElement('i');
            emailIcon.classList.add('fas', 'fa-envelope');
            emailSpan.appendChild(emailIcon);
            let emailInput = document.createElement('input');
            emailInput.type = 'email';
            emailInput.name = 'email';
            emailInput.id = 'email';
            emailInput.classList.add('form-control');
            emailInput.placeholder = 'Email';
            emailInput.value = employeeData.employee.email;
            emailInputGroup.appendChild(emailSpan);
            emailInputGroup.appendChild(emailInput);
            emailContainer.appendChild(emailInputGroup);
            updateEmployeeForm.appendChild(emailContainer);

            // Role field
            let roleContainer = document.createElement('div');
            roleContainer.classList.add('mb-4');
            let roleSelect = document.createElement('select');
            roleSelect.classList.add('form-select');
            roleSelect.name = 'role_type';
            roleSelect.id = 'role_type';
            let roleOptionDefault = document.createElement('option');
            if (employeeData.employee.role_type == 'admin') {
                roleOptionDefault.value = 'admin';
                roleOptionDefault.textContent = 'Admin';
            } else if (employeeData.employee.role_type == 'employee') {
                roleOptionDefault.value = 'employee';
                roleOptionDefault.textContent = 'Employee';
            } else {
                roleOptionDefault.value = '';
                roleOptionDefault.textContent = 'Role';
            }

            let roleOptionAdmin = document.createElement('option');
            roleOptionAdmin.value = 'admin';
            roleOptionAdmin.textContent = 'Admin';
            let roleOptionEmployee = document.createElement('option');
            roleOptionEmployee.value = 'employee';
            roleOptionEmployee.textContent = 'Employee';
            roleSelect.appendChild(roleOptionDefault);
            roleSelect.appendChild(roleOptionAdmin);
            roleSelect.appendChild(roleOptionEmployee);
            roleContainer.appendChild(roleSelect);
            updateEmployeeForm.appendChild(roleContainer);

            // Status field
            let statusContainer = document.createElement('div');
            statusContainer.classList.add('mb-4', 'form-check', 'form-switch');
            let statusInput = document.createElement('input');
            statusInput.classList.add('form-check-input');
            statusInput.type = 'checkbox';
            statusInput.name = 'status';
            statusInput.id = 'status';
            statusInput.value = '1';
            if (employeeData.employee.active == 1) {
                statusInput.checked = true;
            } else {
                statusInput.checked = false;
            }
            let statusLabel = document.createElement('label');
            statusLabel.classList.add('form-check-label');
            statusLabel.textContent = 'Activate Employee';
            statusContainer.appendChild(statusInput);
            statusContainer.appendChild(statusLabel);
            updateEmployeeForm.appendChild(statusContainer);

            // Append the form to the modal body
            updateEmployeeBody.appendChild(updateEmployeeForm);
        } else {
            console.error('Element with ID "updateEmployeeBody" not found.');
        }
    });

    $('#btnUpdateEmployee').click(function (e) {
        e.preventDefault();
        const username = $('#username').val();
        const password = $('#password').val();
        const confirmPassword = $('#confirm_password').val();
        const email = $('#email').val();
        const roleType = $('#role_type').val();
        const isActive = $('#status').is(':checked') ? 1 : 0;
        if (password !== confirmPassword) {
            message_error('#frmUpdateEmployee', 'Passwords do not match');
            return;
        }
        const employee = {
            id: user_id,
            username,
            password,
            email,
            role_type: roleType,
            active: isActive,
            method: "employees-update"
        };
    
        // Log employee object for debugging
        console.log(employee);
    
        // Call update function and close modal
        updateEmployee(employee);
        // $('#updateEmployeeModal').modal('hide');
    });
    

    $(document).on('click', '[id^="delete-employee-"]', function() {
        user_id = $(this).attr('id').split('-')[2];
        var employeeData = loadEmployee(user_id);
        console.log(employeeData);
    
        let deleteEmployeeTitle = document.getElementById('deleteEmployeeTitle');
        if (deleteEmployeeTitle) {
            deleteEmployeeTitle.textContent = 'Delete Employee: ' + employeeData.employee.username;
        } else {
            console.error('Element with ID "deleteEmployeeTitle" not found.');
        }
    
        let deleteEmployeeBody = document.getElementById('deleteEmployeeBody');
        if (deleteEmployeeBody) {
            // Clear any existing content in the modal body
            deleteEmployeeBody.innerHTML = '';
    
            let form = document.createElement('form');
            form.method = 'POST';
            form.id = 'frmDeleteEmployee';
    
            let mb4Div = document.createElement('div');
            mb4Div.className = 'mb-4';
    
            let dFlexDiv = document.createElement('div');
            dFlexDiv.className = 'd-flex justify-content-center mb-4';
    
            let svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('width', '64');
            svg.setAttribute('height', '64');
            svg.setAttribute('fill', 'currentColor');
            svg.setAttribute('class', 'bi bi-exclamation-triangle');
            svg.setAttribute('viewBox', '0 0 16 16');
            svg.setAttribute('style', 'color:red');
    
            let path1 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path1.setAttribute('d', 'M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z');
    
            let path2 = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path2.setAttribute('d', 'M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z');
    
            svg.appendChild(path1);
            svg.appendChild(path2);
            dFlexDiv.appendChild(svg);
            mb4Div.appendChild(dFlexDiv);
    
            let label = document.createElement('label');
            label.className = 'form-label';
            label.innerHTML = 'Do you want to delete this employee <strong><span id="username"></span></strong>?';
            mb4Div.appendChild(label);
            form.appendChild(mb4Div);
    
            let colDiv = document.createElement('div');
            colDiv.className = 'col col-12 offset-md-3 col-md-6 p-0 mb-4';
    
            form.appendChild(colDiv);
    
            deleteEmployeeBody.appendChild(form);
    
            document.getElementById('username').textContent = employeeData.employee.username;
        } else {
            console.error('Element with ID "deleteEmployeeBody" not found.');
        }
    });
    

    $('#btnDeleteEmployee').click(function (e) {
        e.preventDefault();
        const employee = {
            id: user_id,
            method: "employees-delete"
        };
    
        // Call delete function and close modal
        deleteEmployee(employee);
        $('#deleteEmployeeModal').modal('hide');

    });

    $('#pagePrevEmployees').click(function() {
        if (page_employee > 1) {
            page_employee--;
            loadEmployees();
        }
    });

    $('#pageNextEmployees').click(function() {
        page_employee++;
        loadEmployees();
    });

    if ($('#table-employees').length) {
        loadEmployees();
    }

    $('#search').keyup(function() {
        search = $(this).val();
        page_employee = 1;
        loadEmployees();
    });

    if ($('#frmAddEmployee').length) {
        $('#frmAddEmployee').submit(function(e) {
            e.preventDefault();
            password = $('#password').val();
            confirm_password = $('#confirm_password').val();
            if (password !== confirm_password) {
                message_error('#frmAddEmployee', 'Passwords do not match');
                return;
            }

            var employee = {
                username: $('#username').val(),
                password: $('#password').val(),
                email: $('#email').val(),
                role_type: $('#role_type').val(),
                active: $('#status').is(':checked') ? 1 : 0,
                method: "employees-add"
            };
            addEmployee(employee);
        });
    }

    $('#getRoleType').change(function() {
        var employee_role_type = $(this).val();
        // console.log(employee_role_type);
        if (employee_role_type === "none") {
            role_type = "none";
        } else if (employee_role_type === "admin") {
            role_type = "admin";
        } else if (employee_role_type === "employee") {
            role_type = "employee";
        }
        loadEmployees();
    });

});