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
                    // console.log('Counters found:', response.data);
                    displayCounters(response.data);
                } else {
                    // console.log('Error:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    // Function to display counters in a table
    function displayCounters(counters) {
        // To get table from class and not id
        var tableBody = $('#table-counters');
        tableBody.empty();
        tableHeader = `
            <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Counter No.</th>
                <th>Queue Count</th>
            </tr>`;
        tableBody.append(tableHeader);
        // if counting is not available

        // BUG!!
        if (counters.length == 0) {
            var row = `
                <tr>
                    <td colspan="4" class="text-center">No counters found</td>
                </tr>`;
            tableBody.append(row);
            return;
        }
        counters.forEach(function(counter) {
            var row = `
                <tr>
                    <td>${counter.idcounter}</td>
                    <td>${counter.idemployee}</td>
                    <td>${counter.counterNumber}</td>
                    <td>${counter.queue_count}</td>
                </tr>`;
            tableBody.append(row);
        });
    }

    // Load counters when the page is ready

    loadCounters();

    setInterval(function() {
        loadCounters();
    }, 10000);

    // Search counters
    if ($('#search-counter').length) {
        $('#search-counter').on('input', function() {
            var searchQuery = $(this).val();
            loadCounters(searchQuery);
        });
    }
})