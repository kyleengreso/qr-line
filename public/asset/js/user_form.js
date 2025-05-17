
function sumbitUserForm(user) {
    var form = $('#frmUserForm');
    message_info(form, 'Processing...');
    $.ajax({
        url: '/public/api/api_endpoint.php',
        type: 'POST',
        data: JSON.stringify(user),
        success: function(response) {
            console.log(response.data);
            if (response.status === 'success') {
                message_success(form, response.message);
                localStorage.setItem('requester_token', response.token_number);
                var requester_token = localStorage.getItem('requester_token');
                setTimeout(function() {
                    window.location.href = "./requester_number.php?requester_token=" + requester_token;
                }, 1000);
            } else {
                message_error(form, response.message);
            }
        },
        error: function() {
            $('#user_number').text('0');
        }
    });
}

var payment = null;
$('#transaction-history-payment').change(function() {
    payment = $(this).val();
    if (payment == 'null') {
        payment = null;
        // console.log(payment);
    }
    console.log(payment);
});

$('#frmUserForm').submit(function(e) {
    e.preventDefault();

    if (payment === null) {
        message_error($('#frmUserForm'), 'Please select payment type');
        return;
    }
    var user = {
        method : "requester_form",
        name: $('#name').val(),
        email: $('#email').val(),
        payment: payment,
        website: `${realHost}/public/requester/requester_number.php`
    };
    console.log(user);
    sumbitUserForm(user);
});
