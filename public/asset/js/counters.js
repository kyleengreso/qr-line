
var counter_search = '';
var counter_page = 1;
var paginate = 10;

var counter_page_modal = 1;

function loadCounters() {
    let table_counters_registered = document.getElementById('table-counters-registered');
    if (table_counters_registered) {
        const params = new URLSearchParams({
            counters: true,
            page: counter_page,
            paginate: paginate,
            search: counter_search,
        });
        $.ajax({
            url: realHost + '/public/api/api_endpoint.php?' + params,
            type: 'GET',
            success: function(response) {
                while (table_counters_registered.rows.length > 1) {
                    table_counters_registered.deleteRow(-1);
                }
                if (response.status === 'success') {
                    const counters = response.counters;
                    if (counters.length < paginate) {
                        pageNextCounterRegistered.classList.add('disabled');
                    } else {
                        pageNextCounterRegistered.classList.remove('disabled');
                    }
                    counters.forEach(counter => {
                        let row = table_counters_registered.insertRow(-1);
                        row.innerHTML = `
                            <tr>
                                <td style="min-width:20px;max-width:35px">${counter.counterNumber}</td>
                                <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                                    <span>${userStatusIcon(counter.username, counter.role_type, counter.active)}</span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a class="btn btn-outline-primary text-primary" id="update-counter-${counter.idcounter}" data-toggle="modal" data-target="#updateCounterModal">Update</a>
                                        <a id="delete-counter-${counter.idcounter}" class="btn btn-outline-danger" id="delete-counter" data-toggle="modal" data-target="#deleteCounterModal">Delete</a>
                                    </div>
                                </td>
                            </tr>    
                        `;
                    });
                } else {
                    let row = table_counters_registered.insertRow(-1);
                    row.innerHTML = `
                        <tr>
                            <td colspan="3" class="fw-bold text-center">No counters assigned</td>
                        </tr>
                    `;
                }
            }
        })
    }
}

loadCounters();

// Add Counter
let btnAddCounterModal = document.getElementById('btn-add-counter');
btnAddCounterModal.addEventListener('click', function(e) {
    counter_page_modal = 1;
    e.preventDefault();

    let form = document.getElementById('frmAddCounter');
    form.reset();
    loadAddEmployees();
});

function loadAddEmployees() {
    let table_counters_available = document.getElementById('table-add-counter-available');
    if (table_counters_available) {
        const params = new URLSearchParams({
            counters: true,
            available: true,
            search: counter_search,
            page: counter_page_modal,
            paginate: paginate,
        });
        $.ajax({
            url: realHost + '/public/api/api_endpoint.php?' + params,
            type: 'GET',
            success: function (response) {
                while (table_counters_available.rows.length > 1) {
                    table_counters_available.deleteRow(-1);
                }
                if (response.status === 'success') {
                    const employees = response.counters;
                    employees.forEach(employee => {
                        let row = table_counters_available.insertRow(-1);
                        row.innerHTML = `
                            <tr>
                                <td style="min-width:20px;max-width:35px">
                                    <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-set-${employee.id}" value="${employee.id}">
                                </td>
                                <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                                    <span>${userStatusIcon(employee.username, employee.role_type, employee.active)}</span>
                                </td>
                                <td style="min-width:20px;max-width:35px">
                                    ${textBadge(employee.availability, employee.availability === 'Available' ? 'success' : employee.availability === 'Assigned' ? 'danger' : 'warning')}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    let row = table_counters_available.insertRow(-1);
                    row.innerHTML = `
                        <tr>
                            <td colspan="3" class="fw-bold text-center fw-bold">No employee available</td> 
                        </tr>
                    `;
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading employees:', error);
            }
        });
    }
}

let pagePrevCounterAvailableAdd = document.getElementById('pagePrevCounterAvailableAdd');
pagePrevCounterAvailableAdd.addEventListener('click', function(e) {
    if (counter_page_modal > 1) {
        counter_page_modal--;
        if (counter_page_modal === 1) {
            pagePrevCounterAvailableAdd.classList.add('disabled');
        }
        loadAddEmployees();
    }
});

let pageNextCounterAvailableAdd = document.getElementById('pageNextCounterAvailableAdd');
pageNextCounterAvailableAdd.addEventListener('click', function(e) {
    pagePrevCounterAvailableAdd.classList.remove('disabled');
    e.preventDefault();
    counter_page_modal++;
    loadAddEmployees();
});


let addSearchUsername = document.getElementById('addSearchUsername');
addSearchUsername.addEventListener('keyup', function(e) {
    page_counterModal = 1;
    pagePrevCounterAvailableAdd.classList.add('disabled');
    e.preventDefault();
    counter_search = this.value;
    loadAddEmployees();
});

let frmAddEmployee = document.getElementById('frmAddCounter');
frmAddEmployee.addEventListener('submit', function(e) {
    e.preventDefault();

    let formAlert = document.getElementById('addCounterAlert');
    let formAlertMsg = document.getElementById('addCounterAlertMsg');
    const formData = new FormData(this);
    const employee_id = document.querySelector('input[name="employee-counter-set"]:checked').value;
    const counter_number = formData.get('counter_no_add');

    // console.log(employee_id, counter_number);

    $.ajax({
        url: realHost + '/public/api/api_endpoint.php',
        type: 'POST',
        data: JSON.stringify({
            method: "counter-add",
            idemployee: employee_id,
            counterNumber: counter_number,
        }),
        success: function(response) {
            console.log(response);
            if (response.status === 'success') {
                formAlertMsg.innerText = response.message;
                formAlert.classList.remove('d-none', 'alert-danger');
                formAlert.classList.add('alert-success');
                setTimeout(()=>{
                    location.reload();
                }, 1000);
            } else {
                formAlertMsg.innerText = response.message;
                formAlert.classList.add('alert-danger');
                formAlert.classList.remove('d-none', 'alert-success');
                setTimeout(()=>{
                    formAlert.classList.add('d-none');
                }, 5000);
            }
        },
    });
});

$(document).on('click', '[id^="update-counter-"]', function (e) {
    counter_search = '';
    counter_page_modal = 1;
    e.preventDefault();

    const counterId = this.id.split('-')[2];
    console.log(counterId);

    const params = new URLSearchParams({
        counters: true,
        id: counterId,
        page: counter_page_modal,
        paginate: paginate,
    });
    let frmUpdateCounter = document.getElementById('frmUpdateCounter');

    $.ajax({
        url: realHost + '/public/api/api_endpoint.php?' + params,
        type: 'GET',
        success: function (response) {
        console.log(response);
        if (response.status === 'success') {
            const counter = response.counter;
            let updateCounterDisplay = document.getElementById('updateCounterDisplay');
            updateCounterDisplay.innerText = counter.counterNumber;
            let updateCounterUsername = document.getElementById('updateCounterUsername');
            updateCounterUsername.innerText = counter.username;
            let updateCounterNumber = document.getElementById('updateCounterNumber');
            updateCounterNumber.innerText = counter.counterNumber;
            let update_id = document.getElementById('update_id');
            update_id.value = counter.idcounter;
            console.log(counter);
            loadUpdateEmployees();

            frmUpdateCounter.reset();
            frmUpdateCounter.elements['update_id'].value = counter.idcounter;
            frmUpdateCounter.elements['update_counter_no'].value = counter.counterNumber;
        }
        },
    
    });
});

let pagePrevCounterAvailableUpdate = document.getElementById('pagePrevCounterAvailableUpdate');
pagePrevCounterAvailableUpdate.addEventListener('click', function(e) {
    if (counter_page_modal > 1) {
        counter_page_modal--;
        if (counter_page_modal === 1) {
            pagePrevCounterAvailableUpdate.classList.add('disabled');
        }
        loadUpdateEmployees();
    }
});

let pageNextCounterAvailableUpdate = document.getElementById('pageNextCounterAvailableUpdate');
pageNextCounterAvailableUpdate.addEventListener('click', function(e) {
    pagePrevCounterAvailableUpdate.classList.remove('disabled');
    e.preventDefault();
    counter_page_modal++;
    loadUpdateEmployees();
});

let updateSearchUsername = document.getElementById('updateSearchUsername');
updateSearchUsername.addEventListener('keyup', function(e) {
    page_counterModal = 1;
    pagePrevCounterAvailableUpdate.classList.add('disabled');
    e.preventDefault();
    counter_search = this.value;
    loadUpdateEmployees();
});

function loadUpdateEmployees() {
    let table_counters_available = document.getElementById('table-update-counter-available');
    if (table_counters_available) {
        const params = new URLSearchParams({
            counters: true,
            available: true,
            search: counter_search,
            page: counter_page_modal,
            paginate: paginate,
        });
        $.ajax({
            url: realHost + '/public/api/api_endpoint.php?' + params,
            type: 'GET',
            success: function (response) {
                while (table_counters_available.rows.length > 1) {
                    table_counters_available.deleteRow(-1);
                }
                if (response.status === 'success') {
                    const employees = response.counters;
                    employees.forEach(employee => {
                        let row = table_counters_available.insertRow(-1);
                        row.innerHTML = `
                            <tr>
                                <td style="min-width:20px;max-width:35px">
                                    <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-set-${employee.id}" value="${employee.id}">
                                </td>
                                <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                                    <span>${userStatusIcon(employee.username, employee.role_type, employee.active)}</span>
                                </td>
                                <td style="min-width:20px;max-width:35px">
                                    ${textBadge(employee.availability, employee.availability === 'Available' ? 'success' : employee.availability === 'Assigned' ? 'danger' : 'warning')}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    let row = table_counters_available.insertRow(-1);
                    row.innerHTML = `
                        <tr>
                                <td colspan="3" class="text-center fw-bold">No employee available</td> 
                        </tr>
                    `;
                }
            },
            error: function (xhr, status, error) {
                console.error('Error loading employees:', error);
            }
        });
    }
}
let frmUpdateCounter = document.getElementById('frmUpdateCounter');
frmUpdateCounter.addEventListener('submit', function(e) {
    e.preventDefault();

    let formAlert = document.getElementById('updateCounterAlert');
    let formAlertMsg = document.getElementById('updateCounterAlertMsg');

    const formData = new FormData(this);    // RESERVE :>

    const idcounter = formData.get('update_id');
    const employee_id = document.querySelector('input[name="employee-counter-set"]:checked').value;
    const counter_number = formData.get('update_counter_no');
    console.log(employee_id);

    $.ajax({
        url: realHost + '/public/api/api_endpoint.php',
        method: 'POST',
        data: JSON.stringify({
            method: "counters-update",
            id: idcounter,
            counterNumber: counter_number,
            counter_pwd: false,
            idemployee: employee_id,
        }),
        success: function(response) {
            console.log(response);
            if (response.status === 'success') {
                formAlertMsg.innerText = response.message;
                formAlert.classList.remove('d-none', 'alert-danger');
                formAlert.classList.add('alert-success');
                setTimeout(()=>{
                    location.reload();
                }, 1000);
            } else {
                formAlertMsg.innerText = response.message;
                formAlert.classList.add('alert-danger');
                formAlert.classList.remove('d-none', 'alert-success');
                setTimeout(()=>{
                    formAlert.classList.add('d-none');
                }, 5000);
            }
        }
    });



});

$(document).on('click', '[id^="delete-counter-"]', function (e) {
    e.preventDefault();

    const counterId = this.id.split('-')[2];
    console.log(counterId);

    let frmDeleteCounter = document.getElementById('frmDeleteCounter');
    const params = new URLSearchParams({
        counters: true,
        id: counterId,
    });

    $.ajax({
        url: realHost + '/public/api/api_endpoint.php?' + params,
        type: 'GET',
        success: function (response) {
            console.log(response);
            let deleteCounterDisplay = document.getElementById('deleteCounterDisplay');
            deleteCounterDisplay.innerText = response.counter.counterNumber;
            let deleteCounterUsername = document.getElementById('deleteCounterUsername');
            deleteCounterUsername.innerText = response.counter.username;
            let deleteCounterNumber = document.getElementById('deleteCounterNumber');
            deleteCounterNumber.innerText = response.counter.counterNumber;
            frmDeleteCounter.reset();
            frmDeleteCounter.elements['delete_id'].value = response.counter.idcounter;
        }
    });
});

let frmDeleteCounter = document.getElementById('frmDeleteCounter');
frmDeleteCounter.addEventListener('submit', function(e) {
    e.preventDefault();

    let formAlert = document.getElementById('deleteCounterAlert');
    let formAlertMsg = document.getElementById('deleteCounterAlertMsg');

    const formData = new FormData(this);
    const idcounter = formData.get('delete_id');

    $.ajax({
        url: realHost + '/public/api/api_endpoint.php',
        method: 'POST',
        data: JSON.stringify({
            method: "counters-delete",
            id: idcounter,
        }),
        success: function(response) {
            console.log(response);
            if (response.status === 'success') {
                formAlertMsg.innerText = response.message;
                formAlert.classList.remove('d-none', 'alert-danger');
                formAlert.classList.add('alert-success');
                setTimeout(()=>{
                    location.reload();
                }, 1000);
            } else {
                formAlertMsg.innerText = response.message;
                formAlert.classList.add('alert-danger');
                formAlert.classList.remove('d-none', 'alert-success');
                setTimeout(()=>{
                    formAlert.classList.add('d-none');
                }, 5000);
            }
        }
    });
});

let pagePrevCounterRegistered = document.getElementById('pagePrevCounterRegistered');
pagePrevCounterRegistered.addEventListener('click', function(e) {
    e.preventDefault();
    console.log(counter_search);
    if (counter_page > 1) {
        counter_page--;
        if (counter_page === 1) {
            pagePrevCounterRegistered.classList.add('disabled');
        }
        loadCounters();
    }
});

let pageNextCounterRegistered = document.getElementById('pageNextCounterRegistered');
pageNextCounterRegistered.addEventListener('click', function(e) {
    pagePrevCounterRegistered.classList.remove('disabled');
    e.preventDefault();
    counter_page++;
    loadCounters();
});

let searchCounterRegistered = document.getElementById('searchCounterRegistered');
searchCounterRegistered.addEventListener('keyup', function(e) {
    console.log(this.value);
    counter_page = 1;
    pagePrevCounterRegistered.classList.add('disabled');
    e.preventDefault();
    counter_search = this.value;
    loadCounters();
});


