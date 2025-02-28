$(document).ready(function() {

    function displayTransactionHistory(transactions) {
        var table = $('#table-transaction-history');
        table.empty();

        var tableHeader = `
            <tr>
                <th>Datetime</th>
                <th>Transaction Type</th>
            </tr>`;
        table.append(tableHeader);
        for (var i = 0; i < transactions.length; i++) {
            var row = `
                <tr>
                    <td>${transactions[i].transaction_time}</td>
                    <td>${transactions[i].purpose}</td>
                </tr>`;
            table.append(row);
        }
    }

    function getTransactionHistory() {
        $.ajax({
            url: './../api/api_transaction_history.php',
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    displayTransactionHistory(response.data);
                } else {
                    console.log(response.message);
                }
            },
            error: function() {
                displayTransactionHistory([]);
            }
        });
    }

    if ($('#table-transaction-history').length) {
        $('#table-transaction-history').ready(function() {
            getTransactionHistory();
        });
    }


});