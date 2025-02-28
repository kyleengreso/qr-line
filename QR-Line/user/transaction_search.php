<?php
include "./../base.php"

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Search | <?php echo $project_name?></title>
    <link rel="stylesheet" href="./../asset/css/bootstrap.css">
    <link rel="stylesheet" href="./../asset/css/theme.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body class="bg">
    <?php include "./../includes/navbar.php"; ?>

    <div class="container d-flex justify-content-center align-items-center flex-column flex-md-row mt-5 mt-md-20">
    <div class="card shadow-sm p-4 mb-4 mr-0 mb-md-0 mx-md-4" style="max-width: 400px; width: 100%;">
        <h4 class="text-center mb-4">TRANSACTION SEARCH</h4>

    </div>
        <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
            <h4 class="text-center fw-bold">QR FORM</h4>
            <p class="text-center text-muted">PLEASE FILL UP</p>

            <form action="process.php" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Name</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Enter Your Name" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter Your Email Address" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label fw-bold">Purpose</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-info-circle"></i></span>
                        <input type="text" class="form-control" id="purpose" name="purpose" placeholder="Enter Your Purpose" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">Next</button>
            </form>
        </div>
    </div>

    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/counters.js"></script>
</body>
</html>
