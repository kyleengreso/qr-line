$(document).ready(function(){
    // Function to load counters
    function loadCounters(searchQuery = '') {
        $.ajax({
            url: './../api/api_counter.php',
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
        if (queue_count == 0) {
            return `<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCounterModal" data-counter-id="${queue_count}">Delete</button>`;
        } else {
            return `<button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#editCounterModal" data-counter-id="${queue_count}" disabled>Resign?</button>`;
        }
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
                        ${chkCounterAction(counter.queue_count)}
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
})