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
    <title>Employees | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;margin-top:100px;min-height:500px">
        <div class="col-md-6" style="min-width:400px;max-width:900px;transform:scale(0.9)">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" style="text-decoration:none;color:black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow">
                <div class="card-header">
                    <span>Employees</span>
                </div>
                <div class="card-body">
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-start my-1 mx-2 fw-bold">Employees</h3>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <a class="btn btn-success text-white px-4" id="btn-add-employee" data-toggle="modal" data-target="#addEmployeeModal"><span class="fw-bold">+</span> Add New</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col-8">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-search"></i></div>
                                    <div class="form-floating">
                                        <input type="text" name="search" id="search" class="form-control" placeholder="Search username">
                                        <label for="search">Search</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-floating mb-2">
                                    <select class="form-control" name="getRoleType" id="getRoleType">
                                        <option value="none">All</option>
                                        <option value="admin">Admin</option>
                                        <option value="employee">Cashier</option>
                                    </select>
                                    <label for="getRoleType" class="form-label">Role</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped table-members" id="table-employees">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Load -->
                        </tbody>
                    </table>
                    <nav aria-label="Page navigation example">
                        <ul class="pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link" id="pagePrevEmployees">Previous</a>
                            </li>
                            <!-- Page number reserved -->
                            <li class="page-item">
                                <a class="page-link" id="pageNextEmployees">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- View Employee -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">View Employee: <span id="viewUsernameDisplay"></span></h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                    <div class="col">
                        <div class="row-12">
                            <div class="col text-center">
                                <h4 class="text-center my-1 fw-bold">Employee Details</h4>
                            </div>
                        </div>
                        <!-- <div class="row-12">
                            <div class="col d-flex justify-content-center" style="max-width:300px;max-height:300px;">
                                <img class="w-100 h-100" src="./../asset/images/user_icon.png" alt="">
                            </div>
                        </div> -->
                        <div class="row">
                            <div class="col">
                                ID
                            </div>
                            <div class="col">
                                <span id="viewEmployeeId">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Username
                            </div>
                            <div class="col">
                                <span id="viewEmployeeUsername">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Email
                            </div>
                            <div class="col">
                                <span id="viewEmployeeEmail">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Role
                            </div>
                            <div class="col">
                                <span id="viewEmployeeRoleType">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Status
                            </div>
                            <div class="col">
                                <span id="viewEmployeeStatus">N/A</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <form method="POST" id="frmAddEmployee">
                <div class="modal-content">
                    <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                        <h5 class="modal-title fw-bold" id="addEmployeeTitle">Add Employee</h5>
                    </div>
                    <div class="modal-body py-4 px-6" id="addEmployeeBody">
                        <div class="mb-2">
                            <div class="alert alert-danger w-100 d-none" id="addEmployeeAlert">
                                <span id="addEmployeeAlertMsg"></span>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-person-fill"></i></div>
                            <div class="form-floating">
                                <input type="text" name="add_username" id="add_username" class="form-control" placeholder="Username" required>
                                <label for="add_username">Username</label>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-shield-lock-fill"></i></div>
                            <div class="form-floating">
                                <input type="password" name="add_password" id="add_password" class="form-control" placeholder="Password" required>
                                <label for="add_password">Password</label>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-shield-lock-fill"></i></div>
                            <div class="form-floating">
                                <input type="password" name="add_confirm_password" id="add_confirm_password" class="form-control" placeholder="Confirm password" required>
                                <label for="add_confirm_password">Confirm password</label>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-envelope-fill"></i></div>
                            <div class="form-floating">
                                <input type="email" name="add_email" id="add_email" class="form-control" placeholder="Email" required>
                                <label for="add_email">Email</label>
                            </div>
                        </div>
                        <div class="input-group mb-4">
                            <div class="input-group-text"><i class="bi bi-person-up"></i></div>
                            <div class="form-floating">
                                <select class="form-select" name="add_role_type" id="add_role_type" required>
                                    <option value="">Select Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Employee</option>
                                </select>
                                <label for="add_role_type" class="form-label">Role</label>
                            </div>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="add_status" id="add_status" value="1">
                            <label class="form-check-label">Activate Employee</label>
                        </div>
                        <div class="modal-footer" id="addEmployeeFooter">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" id="btnAddEmployee">Add</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Update Employee -->
    <div class="modal fade" id="updateEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <form id="frmUpdateEmployee" method="POST">
                <div class="modal-content">
                    <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                        <h5 class="modal-title fw-bold" id="updateEmployeeTitle">Update Employee: <span id="updateUsernameDisplay"></span></h5>
                    </div>
                    <div class="modal-body py-4 px-6" id="updateEmployeeBody">
                        <div class="mb-2">
                            <div class="alert alert-danger w-100 d-none" id="updateEmployeeAlert">
                                <span id="updateEmployeeAlertMsg"></span>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-person-fill"></i></div>
                            <div class="form-floating">
                                <input type="text" name="update_username" id="update_username" class="form-control" placeholder="Username">
                                <label for="update_username">Username</label>
                            </div>
                        </div>
                        <!-- <div class="mb-4"> -->
                            <input type="hidden" name="update_id" id="update_id">
                        <!-- </div> -->
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-shield-lock-fill"></i></div>
                            <div class="form-floating">
                                <input type="password" name="update_password" id="update_password" class="form-control" placeholder="Password">
                                <label for="update_password">Password</label>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-shield-lock-fill"></i></div>
                            <div class="form-floating">
                                <input type="password" name="update_confirm_password" id="update_confirm_password" class="form-control" placeholder="Confirm Password">
                                <label for="update_confirm_password">Confirm Password</label>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-envelope-fill"></i></div>
                            <div class="form-floating">
                                <input type="email" name="update_email" id="update_email" class="form-control" placeholder="Email">
                                <label for="update_email">Email</label>
                            </div>
                        </div>
                        <div class="input-group mb-4">
                            <div class="input-group-text"><i class="bi bi-person-up"></i></div>
                            <div class="form-floating">
                                <select class="form-select" name="update_role_type" id="update_role_type">
                                    <option value="">Role</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Employee</option>
                                </select>
                                <label for="update_role_type" class="form-label">Role</label>
                            </div>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="update_active" id="update_active" value="1">
                            <label class="form-check-label">Activate Employee</label>
                        </div>
                    </div>
                    <div class="modal-footer" id="updateEmployeeFooter">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateEmployee">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Employee -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <form method="POST" id="frmDeleteEmployee">
                <div class="modal-content">
                    <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                        <h5 class="modal-title fw-bold" id="deleteEmployeeTitle">
                            Delete Employee: <span id="deleteUsernameDisplay"></span>
                        </h5>
                    </div>
                    <div class="modal-body p-4 px-6" id="deleteEmployeeBody">
                        <div class="mb-4">
                            <div class="mb-2">
                                <div class="alert alert-danger w-100 d-none" id="deleteEmployeeAlert">
                                    <span id="deleteEmployeeAlertMsg"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16" style="color:red">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                </svg>
                            </div>
                            <input type="hidden" name="delete_id" id="delete_id">
                            <label class="form-label">
                                Do you want to delete this employee <strong><span id="delete_username"></span></strong>?
                            </label>
                        </div>
                        <div class="modal-footer" id="deleteEmployeeFooter">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="btnDeleteEmployee">Delete</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php after_js()?>
    <?php include_once "./../includes/footer.php";?>
    <script src="./../asset/js/message.js"></script>
    <script>       
        var employee_search = '';
        var page_employees = 1;
        var paginate = 10;
        var role_type_employee = 'none';

        // Search
        let search = document.getElementById('search');
        search.addEventListener('keyup', (e)=> {
            page_employees = 1;
            employee_search = e.target.value;
            loadEmployees();

        });

        let getRoleType = document.getElementById('getRoleType');
        getRoleType.addEventListener('change', (e) => {
            page_employees = 1;
            role_type_employee = e.target.value;
            loadEmployees();
        });

        function loadEmployees() {
            let table_employees = document.getElementById('table-employees');
            if (table_employees) {
                const params = new URLSearchParams({
                    employees: true,
                    page: page_employees,
                    paginate: paginate,
                    search: employee_search,
                    role_type: role_type_employee
                });
                $.ajax({
                    url: '/public/api/api_endpoint.php?' + params,
                    type: 'GET',
                    success: function (response) {
                        while (table_employees.rows.length > 1) {
                            table_employees.deleteRow(-1);
                        }
                        if (response.status === 'success') {
                            const employees = response.employees;
                            if (employees.length < paginate) {
                                pageNextEmployees.classList.add('disabled');
                            } else {
                                pageNextEmployees.classList.remove('disabled');
                            }
                            employees.forEach((employee) => {
                                let row = table_employees.insertRow(-1);
                                row.innerHTML = `
                                    <tr>
                                        <td class="col-2">${employee.id}</td>
                                        <td>
                                            <strong>
                                                ${userStatusIcon(employee.username, employee.role_type, employee.active)}
                                            </strong>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <a class="btn btn-outline-info text-info" id="view-employee-${employee.id}" data-toggle="modal" data-target="#viewEmployeeModal"><i class="bi bi-eye-fill"></i></a>
                                                <a class="btn btn-outline-primary text-primary" id="update-employee-${employee.id}" data-toggle="modal" data-target="#updateEmployeeModal"><i class="bi bi-pencil-square"></i></a>
                                                <a class="btn btn-outline-danger text-danger" id="delete-employee-${employee.id}" data-toggle="modal" data-target="#deleteEmployeeModal"><i class="bi bi-trash-fill"></i></a>
                                            </div>
                                    </tr>              
                                `;
                            });
                        } else {
                            let row = table_employees.insertRow(-1);
                            row.innerHTML = `
                                <tr>
                                    <td colspan="3" class="fw-bold text-center">No employees assigned</td>
                                </tr>            
                            `;
                        }
                    },
                });
            }
        }

        // View Employee
        $(document).on('click', '[id^="view-employee-"]', function (e) {
            e.preventDefault();

            const elementId = $(this).attr('id');
            const employeeId = elementId.split('-').pop();
            console.log(employeeId);

            const params = new URLSearchParams({
                employees: true,
                id: employeeId
            });

            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function (response) {
                    console.log(response);
                    const employee = response.employee;
                    const username = employee.username;
                    const email = employee.email;
                    const role_type = employee.role_type;
                    const active = employee.active;

                    let viewUsernameDisplay = document.getElementById('viewUsernameDisplay');
                    viewUsernameDisplay.innerText = username;
                    let viewEmployeeId = document.getElementById('viewEmployeeId');
                    viewEmployeeId.innerText = employeeId;
                    let viewEmployeeUsername = document.getElementById('viewEmployeeUsername');
                    viewEmployeeUsername.innerText = username;
                    let viewEmployeeEmail = document.getElementById('viewEmployeeEmail');
                    viewEmployeeEmail.innerText = email ? email : 'Not present';
                    let viewEmployeeRoleType = document.getElementById('viewEmployeeRoleType');
                    viewEmployeeRoleType.innerText = role_type;
                    let viewEmployeeStatus = document.getElementById('viewEmployeeStatus');
                    if (active === 1) {
                        viewEmployeeStatus.innerHTML = textBadge('Active', 'success');
                    } else {
                        viewEmployeeStatus.innerHTML = textBadge('Inactive', 'danger');
                    }
                }

            });

        });

        // Add Employee
        let btnAddEmployeeModal = document.getElementById('btn-add-employee');
        btnAddEmployeeModal.addEventListener('click', (e) => {
            e.preventDefault();
            let form = document.getElementById('frmAddEmployee');
            form.reset();
        });

        let frmAddEmployee = document.getElementById('frmAddEmployee');
        frmAddEmployee.addEventListener('submit', function (e) {
            e.preventDefault();

            let formAlert = document.getElementById('addEmployeeAlert');
            let formAlertMsg = document.getElementById('addEmployeeAlertMsg');
            const formData = new FormData(this);
            const username = formData.get('add_username');
            const password = formData.get('add_password');
            const confirm_password = formData.get('add_confirm_password');
            const email = formData.get('add_email');
            const role_type = formData.get('add_role_type');
            const active = formData.get('add_status');
            console.log(username, password, confirm_password, email, role_type, status);

            if (password !== confirm_password) {
                formAlertMsg.innerText = 'Password and Confirm Password do not match';
                formAlert.classList.remove('d-none');
                setTimeout(() => {
                    formAlert.classList.add('d-none');
                }, 5000);
            }

            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify({
                    username : username,
                    password : password,
                    email : email,
                    role_type : role_type,
                    method : "employees-add",
                    active: active
                }),
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(()=> {
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none',);
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    formAlertMsg.innerText = 'Error: ' + x.responseText;
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                }
            });
        });

        // Edit Employee
        $(document).on('click', '[id^="update-employee-"]', function (e) {
            e.preventDefault();

            // Get the ID of the clicked element
            const elementId = $(this).attr('id');
            const employeeId = elementId.split('-').pop(); 

            console.log(employeeId);
            const params = new URLSearchParams({
                employees: true,
                id: employeeId
            });

            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function (response) {
                    console.log(response);

                    const employee = response.employee;
                    const username = employee.username;
                    const email = employee.email;
                    const role_type = employee.role_type;
                    const active = employee.active;

                    let updateUsernameDisplay = document.getElementById('updateUsernameDisplay');
                    updateUsernameDisplay.innerText = username;

                    let frmUpdateEmployee = document.getElementById('frmUpdateEmployee');
                    frmUpdateEmployee.reset();
                    frmUpdateEmployee.elements['update_id'].value = employeeId;
                    frmUpdateEmployee.elements['update_username'].value = username;
                    frmUpdateEmployee.elements['update_email'].value = email;
                    frmUpdateEmployee.elements['update_role_type'].value = role_type;  
                    frmUpdateEmployee.elements['update_active'].checked = active === 1; 
                }
            });
        });

        let frmUpdateEmployee = document.getElementById('frmUpdateEmployee');
        frmUpdateEmployee.addEventListener('submit', function (e) {
            e.preventDefault();

            let formAlert = document.getElementById('updateEmployeeAlert');
            let formAlertMsg = document.getElementById('updateEmployeeAlertMsg');
            
            const formData = new FormData(this);
            const employeeId = formData.get('update_id');
            console.log(employeeId);
            const username = formData.get('update_username');
            const password = formData.get('update_password');
            const confirm_password = formData.get('update_confirm_password');
            const email = formData.get('update_email');
            const role_type = formData.get('update_role_type');
            const active = formData.get('update_active') ? 1 : 0;

            console.log('ID: ' + employeeId);
            console.log('Username: ' + username);
            console.log('Password: ' + password);
            console.log('Confirm Password: ' + confirm_password);
            console.log('Email: ' + email);
            console.log('Role Type: ' + role_type);
            console.log('Active: ' + active);
            // console.log('Status: ' + status);
            if (password !== confirm_password) {
                formAlertMsg.innerText = 'Password and Confirm Password do not match';
                formAlert.classList.remove('d-none');
                setTimeout(() => {
                    formAlert.classList.add('d-none');
                }, 5000);
            }
            
            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify({
                    id: employeeId,
                    username : username,
                    password : password,
                    email : email,
                    role_type : role_type,
                    method : "employees-update",
                    active: active
                }),
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none',);
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    formAlertMsg.innerText = 'Error: ' + x.responseText;
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                }
            });
        });

        // Delete Employee
        $(document).on('click', '[id^="delete-employee-"]', function (e) {
            e.preventDefault();

            const elementId = $(this).attr('id');
            const employeeId = elementId.split('-').pop(); 

            console.log(employeeId);
            const params = new URLSearchParams({
                employees: true,
                id: employeeId
            });

            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                success: function (response) {
                    console.log(response);
                    let frmDeleteEmployee = document.getElementById('frmDeleteEmployee');
                    frmDeleteEmployee.reset();
                    const employee = response.employee;
                    const username = employee.username;

                    let updateUsernameDisplay = document.getElementById('deleteUsernameDisplay');
                    updateUsernameDisplay.innerText = username;

                    frmDeleteEmployee.elements['delete_id'].value = employeeId;

                    let del_username = document.getElementById('delete_username');
                    del_username.innerText = username;
                }
            });
        });

        let frmDeleteEmployee = document.getElementById('frmDeleteEmployee');
        frmDeleteEmployee.addEventListener('submit', function (e) {
            e.preventDefault();

            let formAlert = document.getElementById('deleteEmployeeAlert');
            let formAlertMsg = document.getElementById('deleteEmployeeAlertMsg');
            
            const formData = new FormData(this);
            const employeeId = formData.get('delete_id');
            console.log(employeeId);
            console.log('ID: ' + employeeId);
            
            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify({
                    id: employeeId,
                    method : "employees-delete"
                }),
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none',);
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    formAlertMsg.innerText = 'Error: ' + x.responseText;
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                }
            });
        });

        let pagePrevEmployees = document.getElementById('pagePrevEmployees');
        let pageNextEmployees = document.getElementById('pageNextEmployees');

        pagePrevEmployees.addEventListener('click', (e) => {
            if (page_employees > 1) {
                page_employees--;
                if (page_employees === 1) {
                    pagePrevEmployees.classList.add('disabled');
                }
                loadEmployees();    
            }
        });

        pageNextEmployees.addEventListener('click', (e) => {
            page_employees++;
            if (page_employees > 1) {
                pagePrevEmployees.classList.remove('disabled');
            }
            loadEmployees();
        });

        loadEmployees();
    </script>
</body>
</html>
