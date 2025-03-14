$(document).ready(function() {
    var requester_token = localStorage.getItem('requester_token');
    // console.log(requester_token);
    // var data = {
    //     // make it string to avoid error
    //     requester_token: requester_token
    // };

    function getUserNumber() {
        $.ajax({
            url: './../api/api_requester_number.php?requester_token=' + requester_token,
            type: 'GET',
            // data: JSON.stringify(data),
            success: function(response) {
                console.log(response);
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
                $('#queueNumber').text('0');
            }
        });
    }

    getUserNumber();

    setInterval(function() {
        getUserNumber();
    }, 5000);
});