$(document).ready(function() {

    function sumbitUserForm(user) {
        $.ajax({
            url: './../api/api_user_form.php',
            type: 'POST',
            data: JSON.stringify(user),
            success: function(response) {
                console.log(response.data);
                var form = $('#frmUserForm');
                if (response.status === 'success') {
                    message_success(form, response.message);
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

    $('#frmUserForm').submit(function(e) {
        e.preventDefault();
        var user = {
            name: $('#name').val(),
            email: $('#email').val(),
            purpose: $('#purpose').val(),
        };
        sumbitUserForm(user);
    });
});