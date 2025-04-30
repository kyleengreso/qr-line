
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
            method: 'login'
        };

        $.ajax({
            url: './../api/api_endpoint.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    auth_success(response.message);
                    setTimeout(function() {
                        if (response.data.role_type === 'admin') {
                            window.location.href = "/public/admin";
                        } else if (response.data.role_type === 'employee') {
                            window.location.href = "/public/employee";
                        }
                    }, 1000);
                } else {
                    auth_error(response.message);
                }
            },
        });
    }

    function register(username, password, email) {

        var data = {
            username: username,
            password: password,
            email: email,
            method: 'register'
        };

        $.ajax({
            url: './../api/api_endpoint.php',
            type: 'POST',
            data: JSON.stringify(data),
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

    function registerAdmin(username, password, confirm_password) {

        var data = {
            username: username,
            password: password,
            confirm_password: confirm_password,
            auth_method: 'registerAdmin'
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
        email = $('#email').val();
        confirm_password = $('#confirm_password').val();
        if (password !== confirm_password) {
            register_error('Passwords do not match');
            return;
        }
        register(username, password, email);
    }); 

    $('#frmRegisterAdmin').on('submit', function(event) {
        event.preventDefault();
        username = $('#username').val();
        password = $('#password').val();
        confirm_password = $('#confirm_password').val();
        registerAdmin(username, password, confirm_password);
    }); 

});