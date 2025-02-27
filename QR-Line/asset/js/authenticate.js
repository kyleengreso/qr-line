
$(document).ready(function() {
    function auth_success(message) {
        // alert("Login successful");       // Debug :)
        $form = $('#frmLogIn') ? $('#frmLogIn') : $('#frmRegister');
        $form.prepend('<div class="alert alert-success">'+message+'</div>');
    }

    function auth_error(message) {
        // put this into this form
        $form = $('#frmLogIn') ? $('#frmLogIn') : $('#frmRegister');
        $form.prepend('<div class="alert alert-danger">'+message+'</div>');
    }

    function register_success() {
    }

    function authenticate(username, password) {
        // console.log(username, password);
        // Convert into JSON format
        var data = {
            username: username,
            password: password
        };

        // Then encrypt using base64
        // var auth_enc = btoa(JSON.stringify(data));

        console.log("LOGIN")
        $.ajax({
            url: './../api/api_authenicate.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    auth_success('Login successful');
                    setTimeout(function() {
                        window.location.href = "./../employee/counter.php";
                    }, 1000);
                } else {
                    auth_error('Invalid username or password');
                }
            },
        });
    }

    function register(username, password) {
        // Convert into JSON format

        if (password != confirm_password) {
            auth_error('Password does not match');
        }
    }

    // Login Form
    $('#frmLogIn').on('submit', function(event) {
        event.preventDefault();
        username = $('#username').val();
        password = $('#password').val();
        authenticate(username, password);
    });

    // Register Form
    $('#frmRegister').on('submit', function(event) {
        event.preventDefault();
        username = $('#username').val();
        password = $('#password').val();
        confirm_password = $('#confirm_password').val();
        register(username, password, confirm_password);
    }); 
});