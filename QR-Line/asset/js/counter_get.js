$(document).ready(function() {

    function addCounter(employee_id, counter_no) {
        $.ajax({
            url: './../api/api_counter.php',
            type: 'POST',
            data: JSON.stringify({
                employee_id: employee_id,
                counter_no: counter_no
            }),
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if (response.status == "success") {
                    $form = $('#frmAddCounter');
                    message_success($form, response.message);
                    setTimeout(function() {
                        window.location.href = "./counters.php";
                    }, 1000);
                } else {
                    $form = $('#frmAddCounter');
                    message_error($form, response.message);
                }
            },
            error: function(status) {
                console.error('AJAX Error:', status);
            }
        });
    }

    // Function to load employees
    function loadEmployees(searchQuery = '') {
        $.ajax({
            url: './../api/api_counter.php?available', // Update the path to your actual file location
            type: 'GET',
            data: { search: searchQuery },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    console.log('Employees found:', response.data);
                    // Handle the success response, e.g., display the employees in a table
                    displayEmployees(response.data);
                } else {
                    console.log('Error:', response.message);
                    // Handle the error response
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // Function to display employees in a table
    function displayEmployees(employees) {
        var tableBody = $('#table-members');
        tableBody.empty();
        var tableHeader = `
            <tr>
                <th></th>
                <th class="d-none d-xl-block">#</th>
                <th>Username</th>
                <th>Available</th>
                <th>Query</th>
            </tr>`;
        tableBody.append(tableHeader);
        // if counting is not available
        if (employees.length == 0) {
            var row = `
                <tr>
                    <td colspan="5" class="text-center">No employees found</td>
                </tr>`;
            tableBody.append(row);
            return;
        }
        employees.forEach(function(employee) {
            var row = `
                <tr>
                    <td><input class="form-check-input" type="radio" name="employee" id="employee-radio" value="${employee.employee_id}"></td>
                    <td class="d-none d-xl-block">${employee.employee_id}</td>
                    <td>${employee.employee_username}</td>
                    <td>${employee.available}</td>
                    <td>${employee.queue_count}</td>
                </tr>`;
            tableBody.append(row);
        });
    }

    // Load all employees on page load
    loadEmployees();

    // Handle search input change
    $('#search').on('input', function() {
        var searchQuery = $(this).val();
        loadEmployees(searchQuery);
    });

    if ($('#frmAddCounter').length) {
        $('#frmAddCounter').on('submit', function(event) {
            event.preventDefault();

            // To get value from name of te radio
            var employee_id = document.querySelector('input[name="employee"]:checked').value;
            var counter_no = document.querySelector('input[name="counter_no"]').value;
            console.log(employee_id, counter_no);
            if (employee_id) {
                addCounter(employee_id, counter_no);
            } else {
                message_error($('#frmAddCounter'), 'Please select an employee.');
            }
        });
    }

});