$(document).ready(function() {

    var counter_pwd = false;

    function addCounter(employee_id, counter_no) {
        $.ajax({
            url: './../api/api_counter.php',
            type: 'POST',
            data: JSON.stringify({
                employee_id: employee_id,
                counter_no: counter_no,
                counter_pwd: counter_pwd
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
    function loadEmployees(search = '') {
        if (search.length > 0) {
            var url = './../api/api_counter.php?available&search=' + search;
        } else {
            var url = './../api/api_counter.php?available';
        }
        $.ajax({
            url: url, // Update the path to your actual file location
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    console.log('Employees found:', response.data);
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

    // Function to display employees in a table
    function displayEmployees(employees) {
        var tableBody = $('#table-counters');
        tableBody.empty();
        var tableHeader = `
            <tr>
                <th class="col-2"></th>
                <th>Username</th>
                <th>Available</th>
            </tr>`;
        tableBody.append(tableHeader);
        // if counting is not available
        if (employees.length == 0) {
            var row = `
                <tr>
                    <td colspan="3" class="text-center">No employees found</td>
                </tr>`;
            tableBody.append(row);
            return;
        }
        employees.forEach(function(employee) {
            var row = `
                <tr>
                    <td style="background-color: #FFEAC1"><input class="form-check-input" type="radio" name="employee" id="employee-radio" value="${employee.id}"></td>
                    <td class="d-xl-block">${employee.username}</td>
                    <td>${employee.availability}</td>
                </tr>`;
            tableBody.append(row);
        });
    }

    // Load all employees on page load
    loadEmployees();

    // Handle search input change
    $('#search').on('input', function() {
        var search = $(this).val();
        loadEmployees(search);
    });

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

    $('#counter-pwd').on('click', function(event) {
        event.preventDefault();
        counter_pwd = $('#counter-pwd').attr('value');
        document.getElementById('counter-type').innerText = 'Yes';
        $('#counter-type').text('Yes');
    });

    $('#counter-non-pwd').on('click', function(event) {
        event.preventDefault();
        counter_pwd = $('#counter-non-pwd').attr('value');
        $('#counter-type').text('No');
    });
});