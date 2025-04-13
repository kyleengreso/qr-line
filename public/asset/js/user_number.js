
var protocol = window.location.protocol;
var host = window.location.host;
var realHost = protocol + '//' + host;

function fetchYourQuery() {
    const token = new URLSearchParams(window.location.search).get('requester_token');

    if (!token) {
        alert("Token was not assigned");
        window.location.href = `${realHost}/public/requester/requester_form.php`;
    }

    var param = new URLSearchParams({
        requester_number: true,
        requester_token : token
    })
    $.ajax({
        url: `${realHost}/public/api/api_endpoint.php?${param}`,
        type: 'GET',
        success: function(response) {
            console.log(response);
            if (response.status === 'success') {
                $('#queueNumber').text(response.queueNumber);
                $('#counterNumber').text(response.counterNumber);
                $('#currentQueueNumber').text(response.currentQueueNumber);
            } else {
                alert(response.message);
            }
        },
        error: function() {
            $('#user_number').text('0');
        }
    });

}


fetchYourQuery();

setInterval(function() {
    fetchYourQuery()
}, 5000);