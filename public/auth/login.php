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
                <?php if (session_status() == PHP_SESSION_NONE) session_start();
                if (isset($_SESSION['auth_notice'])) {
                    echo '<div class="alert alert-warning">' . htmlentities($_SESSION['auth_notice']) . '</div>';
                    unset($_SESSION['auth_notice']);
                }
                ?>
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
    <!-- endpointHost emitted centrally in base.php -->
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
                    device_name: navigator.userAgent
                };

                // helper to decode JWT payload without verification (for routing)
                function parseJwt (token) {
                    try {
                        var parts = token.split('.');
                        if (parts.length !== 3) return null;
                        var payload = parts[1];
                        // Add padding if needed
                        while (payload.length % 4 !== 0) payload += '=';
                        var decoded = atob(payload.replace(/-/g, '+').replace(/_/g, '/'));
                        return JSON.parse(decoded);
                    } catch (e) {
                        return null;
                    }
                }

                if (!(endpointHost && endpointHost.length > 0)) {
                    return auth_error('Authentication service unavailable');
                }
                $.ajax({
                    url: endpointHost.replace(/\/$/, '') + '/api/login',
                    type: 'POST',
                    data: JSON.stringify(data),
                    contentType: 'application/json',
                    dataType: 'json',
                    xhrFields: { withCredentials: true }, // allow cookie from API server
                    success: function(response) {
                        if (response.status === 'success') {
                            auth_success(response.message);
                            var token = response.data && response.data.token;
                            // Prefer the server-provided role if available (API returns data.role)
                            var role = (response.data && response.data.role) ? response.data.role : null;
                            if (token) {
                                var p = parseJwt(token);
                                if (p && !role) {
                                    role = p.role_type || p.user_role || p.role || null;
                                }

                                // Post token to local PHP endpoint that will set the cookie on this origin
                                // Then immediately redirect based on role returned by the API.
                                $.ajax({
                                    url: '/public/includes/system_auth.php?action=set_token',
                                    type: 'POST',
                                    data: JSON.stringify({ token: token, role: role }),
                                    contentType: 'application/json',
                                    dataType: 'json',
                                    success: function() {
                                        // Ensure any server-set "auth_notice" is not shown
                                        // (it may have been rendered when the page loaded). Remove
                                        // any existing alerts from the form before redirecting.
                                        $form.find('.alert').remove();

                                        // Also set the cookie client-side so the next navigation
                                        // immediately contains the token for server-side checks.
                                        try {
                                            // max-age = 30 days
                                            var maxAge = 86400 * 30;
                                            document.cookie = 'token=' + encodeURIComponent(token) + '; path=/; max-age=' + maxAge + ';';
                                        } catch (e) {
                                            // ignore
                                        }

                                        if (role === 'admin') {
                                            window.location.href = "/public/admin/index.php";
                                        } else if (role === 'employee') {
                                            window.location.href = "/public/employee/index.php";
                                        } else {
                                            window.location.reload();
                                        }
                                    },
                                    error: function() {
                                        // If local set-cookie fails, fallback to reload
                                        window.location.reload();
                                    }
                                });
                            } else {
                                setTimeout(function() { window.location.reload(); }, 1000);
                            }
                        } else {
                            var msg = '';
                            try {
                                msg = (response && typeof response.message === 'string') ? response.message.trim() : '';
                                if (!msg && response && response.data && typeof response.data.error === 'string') {
                                    msg = response.data.error.trim();
                                }
                            } catch (e) {}
                            if (!msg) msg = 'Login failed. Please check your username and password.';
                            auth_error(msg);
                        }
                    },
                    error: function(xhr, textStatus, errorThrown) {
                        // Log full error to console for debugging
                        console.error('Login request failed', {xhr: xhr, textStatus: textStatus, errorThrown: errorThrown});

                        // Try to parse JSON response for a message, fallback to plain text or status
                        function cleanMsg(m) {
                            if (!m) return '';
                            if (typeof m !== 'string') return m;
                            var t = m.match(/\((?:[^)]*?),\s*'([^']*)'\)/);
                            if (t && t[1]) return t[1];
                            return m;
                        }
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

                        // Hide raw server-side internal messages from users and show friendly guidance.
                        // If the server returned something that looks like a missing .env/config error,
                        // show a non-sensitive message and keep full details in the console.
                        try {
                            if (typeof msg === 'string' && msg.toLowerCase().indexOf('.env') !== -1) {
                                console.warn('Server configuration error (masked to user):', msg);
                                msg = 'Server configuration error. Please contact the administrator.';
                            }
                        } catch (e) {
                            // ignore
                        }

                        msg = cleanMsg(msg);
                        if (!msg || (typeof msg === 'string' && msg.trim().length === 0)) {
                            if (xhr && xhr.status === 401) msg = 'Invalid username or password.';
                            else if (xhr && xhr.status === 0) msg = 'Cannot reach authentication server.';
                            else if (xhr && xhr.status >= 500) msg = 'Server error. Please try again later.';
                            else msg = 'Request failed. Please try again.';
                        }
                        if (xhr && xhr.status) {
                            msg = msg + ' (' + xhr.status + ')';
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

                if (!(endpointHost && endpointHost.length > 0)) {
                    return register_error('Registration service unavailable');
                }
                $.ajax({
                    url: endpointHost.replace(/\/$/, '') + '/api/register',
                    type: 'POST',
                    data: JSON.stringify({ username: username, password: password, email: email }),
                    contentType: 'application/json',
                    dataType: 'json',
                    xhrFields: { withCredentials: true },
                    success: function(response) {
                        if (response.status === 'success') {
                            register_success(response.message);
                            setTimeout(function() { window.location.href = "./login.php"; }, 1000);
                        } else {
                            register_error(response.message || 'Registration failed');
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Registration failed';
                        if (xhr && xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                        register_error(msg);
                    }
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