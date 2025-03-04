
$(document).ready(function() {
    function auth_success(message) {
        $form = $('#frmLogIn');
        $form.find('.alert').remove();
        $form.prepend('<div class="alert alert-success">'+message+'</div>');
    }

    function auth_error(message) {
        $form = $('#frmLogIn');
        $form.find('.alert').remove();
        $form.prepend('<div class="alert alert-danger">'+message+'</div>');
    }

    function register_success(message) {
        $form = $('#frmRegister');
        $form.find('.alert').remove();
        $form.prepend('<div class="alert alert-success">'+message+'</div>');
    }

    function register_error(message) {
        $form = $('#frmRegister');
        $form.find('.alert').remove();
        $form.prepend('<div class="alert alert-danger">'+message+'</div>');
    }


    function authenticate(username, password) {

        var data = {
            username: username,
            password: password,
            auth_method: 'login'
        };

        $.ajax({
            url: './../api/api_authenticate.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    auth_success('Login successful');
                    var token = atob(response.token);
                    role = token.split('!!')[3];
                    setTimeout(function() {
                        if (role === 'admin') {
                            window.location.href = "./../admin/dashboard.php";
                        } else if (role === 'employee') {
                            window.location.href = "./../employee/counter.php";
                        }
                    }, 1000);
                } else {
                    auth_error('Invalid username or password');
                }
            },
        });
    }

    function register(username, password, confirm_password) {

        var data = {
            username: username,
            password: password,
            confirm_password: confirm_password,
            auth_method: 'register'
        };

        $.ajax({
            url: './../api/api_authenticate.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    register_success(response.message);
                    setTimeout(function() {
                        window.location.href = "./login.php";
                    }, 1000);
                } else {
                    register_error(response.message);
                }
            },
        })

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