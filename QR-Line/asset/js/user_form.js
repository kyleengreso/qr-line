$(document).ready(function() {

    function sumbitUserForm(user) {
        $.ajax({
            url: './../api/api_requester_form.php',
            type: 'POST',
            data: JSON.stringify(user),
            success: function(response) {
                // console.log(response.data);
                var form = $('#frmUserForm');
                if (response.status === 'success') {
                    message_success(form, response.message);
                    localStorage.setItem('requester_token', response.token_number);
                    // var requester_token = localStorage.getItem('requester_token');
                    // console.log(requester_token);
                    setTimeout(function() {
                        window.location.href = "./user_number.php";
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
    $('#transaction-history-filter-assessment').click(function() {
        payment = 'assessment';
        $('#transaction-history-filter-payment').text('Assessment');
    });
    
    $('#transaction-history-filter-registrar').click(function() {
        payment = 'registrar';
        $('#transaction-history-filter-payment').text('Registrar');
    });

    $('#frmUserForm').submit(function(e) {
        e.preventDefault();

        // For payment to tell

        if (payment === null) {
            message_error($('#frmUserForm'), 'Please select payment type');
            return;
        }
        var user = {
            name: $('#name').val(),
            email: $('#email').val(),
            payment: payment,
        };
        sumbitUserForm(user);
    });
});