<?php
include_once __DIR__ . '/../base.php';

// Make a random int 1 to 50
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

    <div class="container d-flex justify-content-center align-items-center container-set" style="margin-top: 50px">
        <div class="card shadow-sm p-4 w-100" style="max-width: 400px;border-radius:30px">
            <div class="w-100 py-3">
                <img src="./../asset/images/logo_blk.png" alt="<?php echo $project_name?>" class="img-fluid mx-auto d-block" style="max-width: 100px">
            </div>
            <div class="text-center">
                <h5 class="text-center fw-bold">Welcome to <?php echo $project_name?></h5>
                <p>Forgot Password</p>
            </div>
            <form method="POST" id="frmForgotPassword">
                <div class="input-group mb-4">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="username" id="username" placeholder="Username" required>
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

        $('#frmForgotPassword').on('submit', function(event) {
            event.preventDefault();
            username = $('#username').val();
            forgot_password(username);
        });
    </script>
    <?php include_once "./../includes/footer.php"; ?>
</body>
</html>