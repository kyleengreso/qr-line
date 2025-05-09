<?php
include_once __DIR__ . '/../base.php';
restrictCheckLoggedIn();

// Random for captcha until 50, you can tell
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
    <?php before_js()?>
</head>
<body class="bg">
    <?php include "./../includes/navbar_non.php"; ?>

    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top: 50px;min-height: 600px;">
        <div class="card shadow-sm p-4 w-100" style="max-width: 400px;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <div class="text-center">
                <h5 class="text-center fw-bold">Welcome to <?php echo $project_name?></h5>
                <p>Forgot Password</p>
            </div>
            <form method="POST" id="frmForgotPassword">
                <div class="input-group mb-2">
                    <div class="input-group-text" ><i class="bi bi-person-fill"></i></div>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
                        <label for="username">Username</label>
                    </div>
                </div>
                <div class="input-group mb-4">
                    <div class="input-group-text" ><i class="bi bi-shield-lock-fill"></i></div>
                    <div class="form-floating">
                        <input type="text" class="form-control" name="sum_captcha" id="sum_captcha" placeholder="<?php echo $int_first . "+" . $int_second?>" required>
                        <label for="sum_captcha"><?php echo $int_first . "+" . $int_second?></label>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Send Password</button>
                <p class="text-center mt-3"><a class="text-decoration-none" href="login.php">Back to Login?</a></p>
            </form>
        </div>
    </div>

    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <!-- <script src="./../asset/js/authenticate.js"></script> -->
    <script>
        let captcha_correct = <?php echo ($int_first + $int_second)?>;
        console.log(captcha_correct);
        // Exclusively for forgot password
        function forgot_password(username) {
            let data = {
                username: username,
                method: 'forgot-password'
            };
            const form = $('#frmForgotPassword');
            message_info(form, 'Processing...');
            $.ajax({
                url: './../api/api_endpoint.php',
                type: 'POST',
                data: JSON.stringify(data),
                success: function(response) {
                    if (response.status === 'success') {
                        message_success(form, response.message);
                        setTimeout(() => {
                            window.location.href = "login.php";
                        }, 2000);
                    } else {
                        message_error(form, response.message);
                    }
                },
            });
        }

        let frmForgotPassword = document.getElementById('frmForgotPassword');
        frmForgotPassword.addEventListener('submit', function(e) {
            let formData = new FormData(this);
            let username = formData.get('username');
            let sum_captcha = formData.get('sum_captcha');
            if (sum_captcha != captcha_correct) {
                e.preventDefault();
                message_error(frmForgotPassword, 'Captcha is incorrect');
                setTimeout(() => {
                    // Reserved
                }, timeout);
            } else {
                e.preventDefault();
                forgot_password(username);
            }
        });
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>