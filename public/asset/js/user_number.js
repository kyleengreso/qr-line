$(document).ready(function() {
    var requester_token = localStorage.getItem('requester_token');
    var cancel = new URLSearchParams(window.location.search).get('cancel');
    // console.log(requester_token);
    // var data = {
    //     // make it string to avoid error
    //     requester_token: requester_token
    // };

    function getUserNumber() {
        // get ?requester_token= from the url
        var requester_token = new URLSearchParams(window.location.search).get('requester_token');
        // if (requester_token === null) {
        //     requester_token = localStorage.getItem('requester_token');
        // }
        if (cancel == true || cancel == 'true') {
            $.ajax({
                url: './../api/api_requester_number.php?requester_token=' + requester_token + '&cancel=true',
                type: 'GET',
                // data: JSON.stringify(data),
                success: function(response) {
                    // console.log(response);
                    if (response.status === 'success') {
                        var user_number = response.queueNumber;
                        var counter_number = response.counterNumber;
                        var current_queue_number = response.currentQueueNumber;
                        $('#queueNumber').text(user_number);
                        $('#counterNumber').text(counter_number);
                        $('#currentQueueNumber').text(current_queue_number);
                    } else {
                        // console.log(response.message);
                    }
                },
                error: function() {
                    $('#queueNumber').text('0');
                }
            });
        } else {
            $.ajax({
                url: './../api/api_requester_number.php?requester_token=' + requester_token,
                type: 'GET',
                // data: JSON.stringify(data),
                success: function(response) {
                    // console.log(response);
                    if (response.status === 'success') {
                        var user_number = response.queueNumber;
                        var counter_number = response.counterNumber;
                        var current_queue_number = response.currentQueueNumber;
                        $('#queueNumber').text(user_number);
                        $('#counterNumber').text(counter_number);
                        $('#currentQueueNumber').text(current_queue_number);
                    } else {
                        // console.log(response.message);
                    }
                },
                error: function() {
                    $('#queueNumber').text('0');
                }
            });
        }
    }

    $('#btnCancelRequest').click(function() {
        var requester_token = new URLSearchParams(window.location.search).get('requester_token');
        $.ajax({
            url: './../api/api_requester_number.php?requester_token=' + requester_token + '&cancel=true',
            type: 'POST',
            success: function(response) {
                if (response.status === 'success') {
                    var circle = $('#circle-info');
                    message_success(circle, response.message);
                    setTimeout(function() {
                        circle.text('');
                    }, 5000);
                } else {
                    var circle = $('#circle-info');
                    message_error(circle, response.message);
                    // console.log(response.message);
                }
            },
            error: function() {
                $('#queueNumber').text('0');
            }
        });
    });
    getUserNumber();

    setInterval(function() {
        getUserNumber();
    }, 5000);
});