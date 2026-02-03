<?php
include_once __DIR__ . '/../base.php';
restrictCheckLoggedIn();

// Random for captcha until 50
$int_first = rand(1, 50);
$int_second = rand(1, 50);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Forgot Password | <?php echo $project_name?></title>
    <?php head_css()?>
</head>
<body class="bg bg-gray-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-xl rounded-2xl overflow-hidden">
            <div class="bg-[rgb(255,110,55)] px-8 py-6 text-center">
                <img src="./../asset/images/logo.png" alt="<?php echo $project_name?>" class="mx-auto w-16 mb-3">
                <h1 class="text-xl font-bold text-white"><?php echo $project_name?></h1>
                <p class="text-white/80 text-sm">Reset your password</p>
            </div>
            <div class="p-8">
                <form method="POST" id="frmForgotPassword">
                    <div class="mb-4">
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-1">Username</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-person-fill"></i></span>
                            <input type="text" name="username" id="username" placeholder="Enter username" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <div class="mb-6">
                        <label for="sum_captcha" class="block text-sm font-medium text-gray-700 mb-1">Solve: <?php echo $int_first?> + <?php echo $int_second?> = ?</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"><i class="bi bi-shield-lock-fill"></i></span>
                            <input type="text" name="sum_captcha" id="sum_captcha" placeholder="Enter answer" required class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[rgb(255,110,55)] focus:border-transparent outline-none">
                        </div>
                    </div>
                    <button type="submit" class="w-full py-3 bg-[rgb(255,110,55)] text-white font-semibold rounded-lg hover:bg-[rgb(230,60,20)] transition">Send Password</button>
                    <p class="text-center mt-6 text-sm"><a class="text-[rgb(255,110,55)] hover:underline" href="login.php">Back to Login</a></p>
                </form>
            </div>
        </div>
        <p class="text-center text-gray-500 text-sm mt-6">&copy; <?php echo project_year()?> <?php echo $project_name?></p>
    </div>
    <?php after_js()?>
    <script>
    const endpointHost = window.endpointHost;
    const captcha_correct = <?php echo ($int_first + $int_second)?>;
    
    function msg_ok(m){ $('#frmForgotPassword').find('.msg').remove(); $('#frmForgotPassword').prepend('<div class="msg mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">'+m+'</div>'); }
    function msg_err(m){ $('#frmForgotPassword').find('.msg').remove(); $('#frmForgotPassword').prepend('<div class="msg mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">'+m+'</div>'); }
    function msg_info(m){ $('#frmForgotPassword').find('.msg').remove(); $('#frmForgotPassword').prepend('<div class="msg mb-4 p-3 bg-blue-100 text-blue-800 rounded-lg text-sm">'+m+'</div>'); }

    function forgot_password(username) {
        msg_info('Processing...');
        if (!(endpointHost && endpointHost.length > 0)) { msg_err('Service unavailable'); return; }
        $.ajax({
            url: endpointHost.replace(/\/$/, '') + '/api/forgot_password',
            type: 'POST',
            data: JSON.stringify({username: username}),
            contentType: 'application/json',
            dataType: 'json',
            xhrFields: { withCredentials: true },
            success: function(r) {
                if (r.status === 'success') {
                    msg_ok(r.message || 'If the account exists, password reset instructions have been sent.');
                    setTimeout(function(){ window.location.href = 'login.php'; }, 2000);
                } else {
                    msg_err(r.message || 'Request failed');
                }
            },
            error: function(xhr) {
                msg_err(xhr.responseJSON && xhr.responseJSON.message || 'Request failed');
            }
        });
    }

    $('#frmForgotPassword').on('submit', function(e) {
        e.preventDefault();
        var username = $('#username').val();
        var sum = $('#sum_captcha').val();
        if (parseInt(sum) !== captcha_correct) {
            msg_err('Captcha is incorrect');
        } else {
            forgot_password(username);
        }
    });
    </script>
</body>
</html>