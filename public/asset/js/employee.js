
var employee_search = '';
var page_employees = 1;
var paginate = 5;
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
            url: realHost + '/public/api/api_endpoint.php?' + params,
            type: 'GET',
            success: function (response) {
                if (response.status === 'success') {
                    const employees = response.employees;
                    while (table_employees.rows.length > 1) {
                        table_employees.deleteRow(-1);
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
                                    <a class="btn btn-outline-info text-info" id="view-employee-${employee.id}" data-toggle="modal" data-target="#viewEmployeeModal">View</a>
                                    <a class="btn btn-outline-primary text-primary" id="update-employee-${employee.id}" data-toggle="modal" data-target="#updateEmployeeModal">Update</a>
                                    <a class="btn btn-outline-danger text-danger" id="delete-employee-${employee.id}" data-toggle="modal" data-target="#deleteEmployeeModal">Delete</a>
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
        url: realHost + '/public/api/api_endpoint.php?' + params,
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
    const status = formData.get('add_status');
    
    console.log(username, password, confirm_password, email, role_type, status);

    if (password !== confirm_password) {
        formAlertMsg.innerText = 'Password and Confirm Password do not match';
        formAlert.classList.remove('d-none');
        setTimeout(() => {
            formAlert.classList.add('d-none');
        }, 5000);
    }

    $.ajax({
        url: realHost + '/public/api/api_endpoint.php',
        type: 'POST',
        data: JSON.stringify({
            username : username,
            password : password,
            email : email,
            role_type : role_type,
            method : "employees-add",
            status: status
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
        url: realHost + '/public/api/api_endpoint.php?' + params,
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
        url: realHost + '/public/api/api_endpoint.php',
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
        url: realHost + '/public/api/api_endpoint.php?' + params,
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
        url: realHost + '/public/api/api_endpoint.php',
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
    pagePrevEmployees.classList.remove('disabled');
    loadEmployees();
});

loadEmployees();