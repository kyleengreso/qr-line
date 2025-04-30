$(document).ready(function() {
    var search = '';
    var page_counter = 1;
    var paginate = 5;

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
    function loadEmployees() {
        $.ajax({
            url: './../api/api_endpoint.php?counters&available&page=' + page_counter + '&paginate=' + paginate + '&search=' + search,
            type: 'GET',
            success: function(response) {
                console.log('Employees found:', response.counters);
                var table = $('#table-counters');
                table.empty();
                table.append(`
                    <tr>
                        <th class="col-2"></th>
                        <th>Employee</th>
                        <th>Available</th>
                    </tr>`);
                if (response.status === 'success') {
                    response.counters.forEach(employee => {
                        table.append(`
                            <tr>
                                <td class="col-2">
                                    <input class="form-check-input" type="radio" name="employee" id="employee-radio" value="${employee.id}">
                                </td>
                                <td>${employee.username}</td>
                                <td>${employee.availability}</td>
                            </tr>`);
                    });
                } else {
                    table.append(`
                            <tr>
                                <td colspan="3" class="text-center">No employees found</td>
                            </tr>`);
                    console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // Load all employees on page load
    loadEmployees();

    // Handle search input change
    $('#search').on('input', function() {
        search = $(this).val();
        loadEmployees();
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
});