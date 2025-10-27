<?php
include_once __DIR__ . '/../base.php';

restrictCheckLoggedIn();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php head_icon()?>
    <title>Login | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>

    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top: 100px;min-height: 600px;">
        <div class="card shadow-sm p-4 w-100" style="max-width: 400px;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <div class="text-center">
                <h5 class="text-center fw-bold">Welcome to <?php echo $project_name?></h5>
                <p>Login to continue</p>
            </div>
            <form class="needs-validation" method="POST" id="frmLogIn" novalidate>
                <div class="input-group mb-2">
                    <div class="input-group-text" ><i class="bi bi-person-fill"></i></div>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>
                </div>
                <div class="input-group mb-4">
                    <div class="input-group-text" ><i class="bi bi-lock-fill"></i></div>
                    <div class="form-floating">
                        <input type="password" class="form-control" name="password" id="password" placeholder="Password" required>
                        <label for="password">Password</label>
                    </div>
                </div>
                <div class="mb-2">
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                    <p class="text-center mt-3"><a class="forgot-password text-decoration-none" href="forgot_password.php">Forgot Password?</a></p>
                    <?php if ($enable_register_employee) : ?>
                    <p class="text-center mt-3"><a class="register text-decoration-none" href="register.php">Request Account</a></p>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script>
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
                    method: 'login',
                    device_name: navigator.userAgent
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
                    error: function(xhr, textStatus, errorThrown) {
                        // Try to parse JSON response for a message, fallback to plain text or status
                        var msg = 'An unexpected error occurred';
                        try {
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.responseText) {
                                try {
                                    var parsed = JSON.parse(xhr.responseText);
                                    if (parsed && parsed.message) {
                                        msg = parsed.message;
                                    } else {
                                        msg = xhr.responseText;
                                    }
                                } catch (e) {
                                    // not JSON
                                    msg = xhr.responseText;
                                }
                            } else if (xhr.statusText) {
                                msg = xhr.statusText + ' (' + xhr.status + ')';
                            }
                        } catch (e) {
                            msg = 'Request failed';
                        }
                        auth_error(msg);
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
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>