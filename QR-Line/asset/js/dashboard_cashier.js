$(document).ready(function() {

    var employee_id = localStorage.getItem('user_id');
    var transaction = null;
    var resp = 0;
    var user_id = localStorage.getItem('user_id');
    console.log(user_id);

    // Load the Transaction Session for the Cashier
    function loadTransactions() {
        transaction = null;
        
        $.ajax({
            url : './../api/api_transaction_history.php?cashier&employee_id=' + user_id,
            type : 'GET',
            success : function(response) {
                if (response.status === 'success') {
                    console.log(response);
                    transaction = response;
                    displayTransaction(transaction);
                    
                } else {
                    displayTransaction(response);
                    console.log('Error:', response.message);
                }
            },
            error : function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
            }
        });
    }

    function displayTransaction(response) {
        // As your cashier, first queue right...
        var p_queue_number = $('#queue-number');
        // console.log(response);
        if (response.status === 'empty') {       
            p_queue_number.text('No queue');
        } else {
            console.log(transaction);
            // console.log(response.data);
            // console.log(transaction);
            p_queue_number.text(transaction.data.queue_number);
        }
    }

    $('#btn-counter-success').click(function(e) {
        e.preventDefault();
        console.log(transaction); // Check if transaction is defined and has the expected properties
    
        // Create data object to be sent in the AJAX request
        var data = {
            employee_id : user_id,
            transaction_id : transaction.data.idtransaction, // Ensure transaction.idtransaction is defined
            cashier : transaction.data.idcounter,
            status : "completed"
        };
        console.log(data); // Verify the contents of the data object
    
        // Send AJAX POST request

        $.ajax({
            url : './../api/api_transaction_history.php',
            type : 'POST',
            data : JSON.stringify(data), // Convert data object to JSON string
            success: function(response) {
                console.log(response); // Verify the server response
                // Check if the response status is 'success'
                if (response.status === 'success') {
                    // Handle success case
                    loadTransactions();
                } else {
                    // Handle error case
                    console.error('Error:', response.message);
                }
            },
            error : function(xhr, status, error) {
                console.error(xhr);
                console.error('AJAX Error:', status, error); // Log AJAX error details
            }
        });
    });

    
    $('#btn-counter-skip').click(function(e) {
        e.preventDefault();
        console.log(transaction); // Check if transaction is defined and has the expected properties
    
        // Create data object to be sent in the AJAX request
        var data = {
            employee_id : user_id,
            transaction_id : transaction.data.idtransaction, // Ensure transaction.idtransaction is defined
            cashier : transaction.data.idcounter,
            status : "missed"
        };
        console.log(data); // Verify the contents of the data object
    
        // Send AJAX POST request

        $.ajax({
            url : './../api/api_transaction_history.php',
            type : 'POST',
            data : JSON.stringify(data), // Convert data object to JSON string
            success: function(response) {
                console.log(response); // Verify the server response
                // Check if the response status is 'success'
                if (response.status === 'success') {
                    // Handle success case
                    loadTransactions();
                } else {
                    // Handle error case
                    console.error('Error:', response.message);
                }
            },
            error : function(xhr, status, error) {
                console.error(xhr);
                console.error('AJAX Error:', status, error); // Log AJAX error details
            }
        });
    });

    loadTransactions();

    console.log(transaction);
    // Refresh by Cashier
    // setInterval(function() {
    //     loadTransactions();
    // }, 5000);



});