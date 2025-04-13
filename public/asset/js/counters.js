
var counter_search = '';
var page_counter = 1;
var paginate = 5;

var counterAdd_search = '';
var page_counterModal = 1;



// Get the host using JS
var protocol = window.location.protocol;
var host = window.location.host;
var realHost = protocol + '//' + host;

console.log('Host: ' + realHost);

let table_counter_available = document.querySelectorAll('.table-counter-available');

let searchBar = document.querySelectorAll('.searchBar');
if (searchBar.length > 0) {
    searchBar.forEach((search) => {
        search.addEventListener('keyup', function (e) {
            page_counter = 1;
            counter_search = e.target.value; // Update the counter_search variable
            // console.log('Search Value:', counter_search); // Debugging log
            console.log(getCounterAvailable()); // Call the function to fetch data
        });
    });
}

let searchBarCounterAvailableAdd = document.querySelector('.searchBarCounterAvailableAdd');
if (searchBarCounterAvailableAdd) {
    searchBarCounterAvailableAdd.addEventListener('keyup', function (e) {
        page_counter = 1;
        counter_search = e.target.value;
        console.log('Search Value:', counter_search);
        console.log(getCounterAvailable());
        displayCounterAvailable(getCounterAvailable());
    });
}

let searchBarCounterAvailableUpdate = document.querySelector('.searchBarCounterAvailableUpdate');
if (searchBarCounterAvailableUpdate) {
    searchBarCounterAvailableUpdate.addEventListener('keyup', function (e) {
        page_counter = 1;
        counter_search = e.target.value;
        console.log('Search Value:', counter_search);
        console.log(getCounterAvailable());
        displayCounterAvailable(getCounterAvailable());
    });
}

let searchBarCounterRegistered = document.querySelector('.searchBarCounterRegistered');
if (searchBarCounterRegistered) {
    searchBarCounterRegistered.addEventListener('keyup', function (e) {
        counter_search = e.target.value;
        console.log('Search Value:', counter_search);
        console.log(getCounterRegistered());
        displayCounterRegistered(getCounterRegistered());
    });
}

function getCounterById(id) {
    // id that means for the following
    // idcounter = YES
    // counterNumber = NO
    // idemployee = NO

    if (!id) {
        console.error('ID is null or undefined');
        return null;
    }

    const params = new URLSearchParams({
        counters: true,
        id: id
    });
    let resp = null;
    $.ajax({
        url: `${realHost}/public/api/api_endpoint.php?${params}`,
        type: 'GET',
        async: false,
        success: function(response) {
            resp = response;
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            // alert('Network Error: Please check your connection');
        }
    });
    return resp;
}

function getCounterAvailable() {
    const params = new URLSearchParams({
        counters: true,
        available: true,
        page: page_counterModal,
        paginate: paginate,
        search: counter_search
    });

    let resp = null;

    $.ajax({
        url: `${realHost}/public/api/api_endpoint.php?${params}`,
        type: 'GET',
        async: false,
        success: function(response) {
            resp = response;
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            alert('Network Error: Please check your connection');
        }
    });
    return resp;
}

function getCounterRegistered() {
    const params = new URLSearchParams({
        counters: true,
        page: page_counter,
        paginate: paginate,
        search: counter_search
    });

    let resp = null;

    $.ajax({
        url: `${realHost}/public/api/api_endpoint.php?${params}`,
        type: 'GET',
        async: false,
        success: function(response) {
            resp = response;
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', {xhr, status, error});
            alert('Network Error: Please check your connection');
        }
    });
    return resp;
}


function displayCounterRegistered(response) {
    let data = response;

    // Sort in ascending order
    if (data && data.counters) {
        data.counters.sort((a, b) => {
            return a.counterNumber - b.counterNumber;
        });
    }

    let table_counter_registered = document.querySelector('.table-counter-registered');
    if (!table_counter_registered) {
        console.error('Table with class "table-counter-registered" not found.');
        return;
    }

    while (table_counter_registered.rows.length > 1) {
        table_counter_registered.deleteRow(-1);
    }

    if (data && data.counters) {
        data.counters.forEach(employee => {
            let row = table_counter_registered.insertRow(-1);
            row.innerHTML = `
            <tr>
                <td style="min-width:20px;max-width:35px">${employee.counterNumber}</td>
                <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                    <span>${userStatusIcon(employee.username, employee.role_type, employee.active)}</span>
                </td>
                <td style="min-width:20px;max-width:35px">
                    ${employee.queue_count}
                </td>
                <td>
                    <a class="btn btn-outline-primary text-primary" id="update-counter-${employee.idcounter}" data-toggle="modal" data-target="#updateCounterModal" style="border-top-right-radius:0px;border-bottom-right-radius:0px">Update</a>
                    <a id="delete-counter-${employee.idcounter}" class="btn btn-outline-danger delete-counter" data-toggle="modal" data-target="#deleteCounterModal" style="border-top-left-radius:0px;border-bottom-left-radius:0px">Delete</a>
                </td>
            </tr>
        `;
        });
    } else {
        let row = table_counter_registered.insertRow(-1);
        row.innerHTML = `
            <td colspan="4" class="text-center fw-bold">No data available</td>
        `;
    }
}

function displayCounterAvailable(response) {
    let data = response;
    console.log(response);
    let tables = document.querySelectorAll('.table-counter-available'); // Use querySelectorAll to get all matching tables

    if (!tables || tables.length === 0) {
        console.error('No tables with class "table-counter-available" found.');
        return;
    }

    // Loop through each table
    tables.forEach((table) => {
        // Clear existing rows except the header
        while (table.rows.length > 1) {
            table.deleteRow(-1);
        }

        // Populate the table with new data
        if (data && data.counters) {
            data.counters.forEach(employee => {
                let row = table.insertRow(-1);

                row.innerHTML = `
                    <tr>
                        <td style="min-width:20px;max-width:35px">
                            <input class="form-check-input" type="radio" name="employee-counter-set" id="employee-counter-${employee.id}" value="${employee.id}">
                        </td>
                        <td class="fw-bold role_type_employee_icon" style="min-width:40px">
                            <span>${userStatusIcon(employee.username, employee.role_type, employee.active)}</span>
                        </td>
                        <td style="min-width:20px;max-width:35px">
                            ${textBadge(employee.availability, employee.availability == 'Available' ? 'success' : employee.availability == 'Assigned' ? 'danger' : 'warning')}
                        </td>
                    </tr>
                `;

            });
        } else {
            let row = table.insertRow(-1);
            row.innerHTML = `
                <td colspan="3" class="text-center fw-bold">No data available</td>
            `;
        }
    });
}

// ADD COUNTER
let btn_add_counter = document.getElementById('btn-add-counter');
if (btn_add_counter) {
    btn_add_counter.addEventListener('click', function () {
        const employees = getCounterAvailable();
        displayCounterAvailable(employees);
    });
}

$('#frmAddCounter').on('submit', function(event) {
    event.preventDefault();

    // To get value from name of te radio
    var employee_id = null;
    var counter_no = document.querySelector('input[name="counter_no_add"]').value;
    console.log(employee_id, counter_no);

    if (document.querySelector('input[name="employee-counter-set"]:checked') && counter_no) {
        employee_id = document.querySelector('input[name="employee-counter-set"]:checked').value;
        console.log(employee_id);
        addCounter(employee_id, counter_no);
    } else {
        message_error($('#frmAddCounter'), 'Please select an employee.');
    }
});

function addCounter(employee_id, counter_no) {
    var data = {
        method: "counter-add",
        idemployee: employee_id,
        counterNumber: counter_no,
    }
    $.ajax({
        url: './../api/api_endpoint.php',
        type: 'POST',
        data: JSON.stringify(data),
        success: function(response) {
            $form = $('#frmAddCounter');
            console.log(response);
            if (response.status == "success") {
                message_success($form, response.message);
                setTimeout(function() {
                    window.location.href = "./counters.php";
                }, 1000);
            } else {
                message_error($form, response.message);
            }
        },
        error: function(status) {
            console.error('AJAX Error:', status);
        }
    });
}

// PAGINATE: PREV and NEXT
let pagePrevCounterRegistered = document.getElementById('pagePrevCounterRegistered');
let pageNextCounterRegistered = document.getElementById('pageNextCounterRegistered');

if (pagePrevCounterRegistered) {
    pagePrevCounterRegistered.addEventListener('click', function () {
        if (page_counter > 1) {
            page_counter--;
            const counterRegistered = getCounterRegistered();
            if (counterRegistered.status == 'error' || counterRegistered.counters.length < paginate || page_counter == 1) {
                pagePrevCounterRegistered.classList.add('disabled');
            }
            pageNextCounterRegistered.classList.remove('disabled');
            displayCounterRegistered(counterRegistered);
        }
    });
}

if (pageNextCounterRegistered) {
    pageNextCounterRegistered.addEventListener('click', function () {
        page_counter++;
        const counterRegistered = getCounterRegistered();
        console.log(counterRegistered);
        if (counterRegistered.status == 'error') {
            pageNextCounterRegistered.classList.add('disabled');
        } else if (counterRegistered.counters.length < paginate) {
            pageNextCounterRegistered.classList.add('disabled');
        }
        pagePrevCounterRegistered.classList.remove('disabled');
        displayCounterRegistered(counterRegistered);
    });
}

let pagePrevCounterAvailableAdd = document.getElementById('pagePrevCounterAvailableAdd');
let pageNextCounterAvailableAdd = document.getElementById('pageNextCounterAvailableAdd');

if (pagePrevCounterAvailableAdd) {
    pagePrevCounterAvailableAdd.addEventListener('click', function () {
        if (page_counterModal > 1) {
            page_counterModal--;
            const counterAvailable = getCounterAvailable();
            if (counterAvailable.status == 'error' || counterAvailable.counters.length < paginate || page_counter == 1) {
                pagePrevCounterAvailableAdd.classList.add('disabled');
            }
            pageNextCounterAvailableAdd.classList.remove('disabled');
            displayCounterAvailable(counterAvailable);
        }
    })
}
if (pageNextCounterAvailableAdd) {
    pageNextCounterAvailableAdd.addEventListener('click', function () {
        page_counterModal++;
        const counterAvailable = getCounterAvailable();
        if (counterAvailable.status == 'error' || counterAvailable.counters.length < paginate) {
            pageNextCounterAvailableAdd.classList.add('disabled');
        }
        pagePrevCounterAvailableAdd.classList.remove('disabled');
        displayCounterAvailable(counterAvailable);
    });
}

let pagePrevCounterAvailableUpdate = document.getElementById('pagePrevCounterAvailableUpdate');
let pageNextCounterAvailableUpdate = document.getElementById('pageNextCounterAvailableUpdate');

if (pagePrevCounterAvailableUpdate) {
    pagePrevCounterAvailableUpdate.addEventListener('click', function () {
        if (page_counterModal > 1) {
            page_counterModal--;
            const counterAvailable = getCounterAvailable();
            if (counterAvailable.status == 'error' || counterAvailable.counters.length < paginate) {
                pagePrevCounterAvailableUpdate.classList.add('disabled');
            } else {
                pageNextCounterAvailableUpdate.classList.remove('disabled');
            }
            displayCounterAvailable(counterAvailable);
        }
    })
}

if (pageNextCounterAvailableUpdate) {
    pageNextCounterAvailableUpdate.addEventListener('click', function () {
        page_counterModal++;
        const counterAvailable = getCounterAvailable();
        if (counterAvailable.status == 'error' || counterAvailable.counters.length < paginate) {
            pageNextCounterAvailableUpdate.classList.add('disabled');
        }
        pagePrevCounterAvailableUpdate.classList.remove('disabled');
        displayCounterAvailable(counterAvailable);
    });
}
// // PAGINATE (Beta)
// $(document).on('click', '[id^="page-array-"]', function () {
//     const page = this.id.split('-')[2];
    
// });

// UPDATE COUNTER
var counter_id_update = null;
$(document).on('click', '[id^="update-counter-"]', function () {
    const counterId = this.id.split('-')[2];
    const counterData = getCounterById(counterId);
    page_counterModal = 1;

    // Update Modal Property
    // Title
    const updateCounterTitle = document.getElementById('updateCounterTitle');
    updateCounterTitle.innerText = `Update Counter: ${counterData.counter.counterNumber}`;


    counter_id_update = counterData.counter.idcounter;
    
    const update_idcounter = document.getElementById('update-idcounter');
    if (update_idcounter) {
        update_idcounter.textContent = counterData.counter.idcounter; // Correctly set the text content
        console.log('Updated ID Counter:', update_idcounter.textContent);
    } else {
        console.error('Element with ID "update-idcounter" not found.');
    }

    const employees = getCounterAvailable();

    const counter_no_update = document.getElementById('counter_no_update');
    counter_no_update.value = counterData.counter.counterNumber;
    displayCounterAvailable(employees);
    console.log(counterData);
});

$('#frmUpdateCounter').on('submit', function(event) {
    event.preventDefault();

    // To get value from name of te radio
    const counterId = counter_id_update;
    console.log('COunter Id:', counterId);
    let employee_id = null;
    const counter_no_update = document.getElementById('counter_no_update').value;
    console.log(employee_id, counter_no_update);

    if (document.querySelector('input[name="employee-counter-set"]:checked') && counter_no_update) {
        employee_id = document.querySelector('input[name="employee-counter-set"]:checked').value;
        console.log(`Counter ID: ${counterId} | Employee ID: ${employee_id} | Counter No: ${counter_no_update}`);
        updateCounter(counterId, employee_id, counter_no_update);
    } else {
        message_error($('#frmUpdateCounter'), 'Please select an employee.');
    }

});
function updateCounter(idcounter, employee_id, counterNumber) {
    console.log('updateCounter called with:', { idcounter, employee_id, counterNumber });
    let data = {
        method: "counters-update",
        id: idcounter,
        idemployee: employee_id,
        counterNumber: counterNumber,
        counter_pwd: false
    };

    $.ajax({
        url: `${realHost}/public/api/api_endpoint.php`,
        type: 'POST',
        async: false,
        data: JSON.stringify(data),
        success: function(response) {
            console.log(response);
            if (response.status == "success") {
                message_success($('#frmUpdateCounter'), response.message);
                setTimeout(function() {
                    window.location.href = "./counters.php";
                }, 1000);
            } else {
                message_error($('#frmUpdateCounter'), response.message);
            }
        },
    });
}

// DELETE COUNTER
var counter_id_delete = null;
$(document).on('click', '[id^="delete-counter-"]', function () {
    const deleteCounterId = this.id.split('-')[2];
    counter_id_delete = deleteCounterId;
    const counterData = getCounterById(deleteCounterId);
    console.log(counterData);

    const deleteCounterTitle = document.getElementById('deleteCounterTitle');
    deleteCounterTitle.innerText = `Delete Counter: ${counterData.counter.counterNumber}`;

    
});

$('#frmDeleteCounter').on('submit', function(event) {
    event.preventDefault();
    console.log('Counter Id for delete:', counter_id_delete);
    deleteCounter(counter_id_delete);
});

function deleteCounter(idcounter) {
    console.log(idcounter);
    let data = {
        method: "counters-delete",
        id: idcounter
    }

    $.ajax({
        url: `${realHost}/public/api/api_endpoint.php`,
        type: 'POST',
        async: false,
        data: JSON.stringify(data),
        success: function(response) {
            console.log(response);
            if (response.status == "success") {
                message_success($('#frmDeleteCounter'), response.message);
                setTimeout(function() {
                    window.location.href = "./counters.php";
                }, 1000);
            } else {
                message_error($('#frmDeleteCounter'), response.message);
            }
        },
    });


}
// At last call them :)
console.log(getCounterRegistered());
displayCounterRegistered(getCounterRegistered());