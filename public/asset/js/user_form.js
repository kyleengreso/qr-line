$(document).ready(function() {
    var domain = window.location.hostname;
    console.log(domain);
    // how about add with http or https from domain
    var url = window.location.protocol + '//' + window.location.hostname;
    // var url = window.location.href;
    console.log(url);

    function sumbitUserForm(user) {
        // console.log(user);
        // return;
        $.ajax({
            url: './../api/api_requester_form.php',
            type: 'POST',
            data: JSON.stringify(user),
            success: function(response) {
                console.log(response.data);
                var form = $('#frmUserForm');
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

        // var domain = window.location.hostname;
        var domain = window.location.protocol + '//' + window.location.hostname;
        // For payment to tell

        if (payment === null) {
            message_error($('#frmUserForm'), 'Please select payment type');
            return;
        }
        var user = {
            name: $('#name').val(),
            email: $('#email').val(),
            payment: payment,
            website: domain + '/public/user/requester_number.php'
        };
        sumbitUserForm(user);
    });
});