<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Form - QR-Line</title>
    <link rel="stylesheet" href="asset/css/bootstrap.css">
    <link rel="stylesheet" href="asset/css/user_style.css">
    <script src="https://kit.fontawesome.com/0aa2c3c0f4.js" crossorigin="anonymous"></script>
</head>
<body>
    <?php include "includes/navbar.php"; ?>

    <div class="container">
        <div class="qr-form-container">
            <h4 class="text-center header-title">QR FORM</h4>
            <p class="text-center text-muted">PLEASE FILL UP</p>

            <form action="process.php" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label fw-bold">Name</label>
                    <input type="text" class="form-control input-orange" id="name" name="name" placeholder="Enter Your Name" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label fw-bold">Email</label>
                    <input type="email" class="form-control input-orange" id="email" name="email" placeholder="Enter Your Email Address" required>
                </div>

                <div class="mb-3">
                    <label for="purpose" class="form-label fw-bold">Purpose</label>
                    <input type="text" class="form-control input-orange" id="purpose" name="purpose" placeholder="Enter Your Purpose" required>
                </div>

                <div class="button-container">
                    <button type="submit" class="btn btn-orange">Next</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
