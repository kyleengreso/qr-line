$(document).ready(function() {

    var employee_id = localStorage.getItem('user_id');
    var transaction_id = 0;
    var resp = 0;

    // Load the Transaction Session for the Cashier
    function loadTrnsactions() {
        var user_id = localStorage.getItem('user_id');
        console.log(user_id);
        $.ajax({
            url : './../api/api_transaction_history.php?cashier&employee_id=' + user_id,
            type : 'GET',
            success : function(response) {
                if (response.status === 'success') {
                    resp = response.data;
                    displayTransaction(resp);
                } else {
                    console.log('Error:', response.message);
                }
            },
        });
    }

    function displayTransaction() {
        // As your cashier, first queue right...
        var p_queue_number = $('#queue-number');
        if (resp.length == 0) {       
            p_queue_number.text('No queue');
        } else {
            var total_transactons = resp.length;
            p_queue_number.text(resp[0].queue_number);
        }
    }


    $('#frmNextTransaction').on('submit', function(e) {
        e.preventDefault();
        var data = {
            employee_id : employee_id,
            transaction_id : resp[0].idtransaction,
            cashier : employee_id
        };
        $.ajax({
            url : './../api/api_transaction_history.php',
            type : 'POST',
            data : JSON.stringify(data),
            contentType : 'application/json',
            dataType : 'json',
            success: function(response) {
                if (response.status === 'success') {
                    loadTrnsactions();
                } else {
                    console.log('Error:', response.message);
                }
            },
            error : function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });

    });
    // Refresh by Cashier
    loadTrnsactions();



});