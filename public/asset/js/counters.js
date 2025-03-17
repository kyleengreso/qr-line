$(document).ready(function(){
    // Function to load counters
    function loadCounters(searchQuery = '') {
        $.ajax({
            url: './../api/api_counter.php?search=' + searchQuery,
            type: 'GET',
            data: { search: searchQuery },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // console.log(response.data);
                    displayCounters(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    function chkCounterAction(queue_count) {
        // if (queue_count == 0) {
        //     return `<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCounterModal" data-counter-id="${queue_count}">Delete</button>`;
        // } else {
        //     return `<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCounterModal" data-counter-id="${queue_count}" disabled>Resign?</button>`;
        // }
        
        // About to delete
        return `<a class="btn btn-primary btn-sm" id="">Delete</a>`;
    }

    // Function to display counters in a table
    function displayCounters(counters) {
        var tableBody = $('#table-counters');
        tableBody.empty();
        tableHeader = `
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Queue Count</th>
                <th>Action</th>
            </tr>`;
        tableBody.append(tableHeader);

        if (counters.length == 0) {
            var row = `
                <tr>
                    <td colspan="3" class="text-center">No counters found</td>
                </tr>`;
            tableBody.append(row);
            return;
        }
        counters.forEach(function(counter) {
            var row = `
                <tr>
                    <td>${counter.counterNumber}</td>
                    <td>${counter.username}</td>
                    <td>${counter.queue_count}</td>
                    <td>
                        <button class="btn btn-primary btn-sm" onclick="window.location.href='./delete_counter.php?idcounter=${counter.idcounter}'">Delete</button>
                    </td>
                </tr>`;
            tableBody.append(row);
        });
    }

    // Load counters when the page is ready

    loadCounters();

    // Search counters
    if ($('#search-counter').length) {
        $('#search-counter').on('input', function() {
            var searchQuery = $(this).val();
            loadCounters(searchQuery);
        });
    }

    if ($('#frmDeleteCounter').length) {
        var counterId = new URLSearchParams(window.location.search).get('idcounter');
        // console.log(counterId);
        $.ajax({
            url: './../api/api_counter.php?id=' + counterId,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#counterNumber').text(response.data.counterNumber);
                    $('#username').text(response.data.username);
                    $('#queue_count').text(response.data.queue_count);
                } else {
                    console.log('Error: Unexpected response status');
                }
            },
            error: function(xhr, status, error) {
                // Print the error message from raw response
                console.error('AJAX Error:', status, error);
            }
        });
        $('#frmDeleteCounter').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: './../api/api_counter.php?id=' + counterId,
                type: 'POST',
                data: JSON.stringify({
                    "method" : 'delete',
                    "counter_no" : counterId,
                }),
                success: function(response) {
                    if (response.status === 'success') {
                        window.location.href = './counters.php';
                    } else {
                        console.log('Error: Unexpected response status');
                    }
                },
                error: function(xhr, status, error) {
                    // Print the error message from raw response
                    console.error('AJAX Error:', status, error);
                }
            });
        });
    }
})