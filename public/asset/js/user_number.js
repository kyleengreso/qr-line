
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

let btnCancelRequest = document.getElementById("btnCancelRequest");
if (btnCancelRequest) {
    btnCancelRequest.addEventListener("click", function () {
        // get token from url
        const token = new URLSearchParams(window.location.search).get('requester_token');
        console.log(token);
        var data = {
            method: "requester-form-cancel",
            token_number: token
        }
        $.ajax({
            url: `${realHost}/public/api/api_endpoint.php`,
            type: "POST",
            data: JSON.stringify(data),
            success: function (response) {
                console.log(response);
                if (response.status) {
                    alert(response.message);
                    window.location.href = `${realHost}/public/requester/requester_form.php`;
                } else {
                    alert(response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("Logout request failed:", error);
                alert("An error occurred while logging out. Please try again.");
            }
        });
    });
}
fetchYourQuery();

setInterval(function() {
    fetchYourQuery()
}, 5000);


