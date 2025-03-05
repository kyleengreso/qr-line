$(document).ready(function() {

        $.ajax({
            url: './../api/api_requester_number.php',
            type: 'GET',
            success: function(response) {
                console.log(response.data);
                if (response.status === 'success') {
                    
                    var user_number = response.queueNumber;
                    var counter_number = response.counterNumber;
                    var current_queue_number = response.currentQueueNumber;
                    $('#queueNumber').text(user_number);
                    $('#counterNumber').text(counter_number);
                    $('#currentQueueNumber').text(current_queue_number);
                } else {
                    console.log(response.message);
                }
            },
            error: function() {
                $('#user_number').text('0');
            }
        });

});