$(document).ready(function() {

    // Function to load employees
    function counterAvailable(employee_id, counter_id) {
        $.ajax({
            url: './../api/api_counter.php?id=' + counter_id + "&employee_id=" + employee_id, // Update the path to your actual file location
            type: 'POST',
            data: { employee_id: employee_id, 
                    counter_id: counter_id
                },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    console.log('Counter found:', response.data);
                    // Handle the success response, e.g., display the employees in a table
                    displayCounter(response.data);
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
        var tableBody = $('#table-employees');
        tableBody.empty();
        tableHeader = `
            <tr>
                <th></th>
                <th>#</th>
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
                    <td><input class="form-check-input" type="radio" name="employee-choose" id="employee-radio" value="${employee.employee_id}"></td>
                    <td>${employee.employee_id}</td>
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
});