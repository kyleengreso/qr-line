<?php
include_once __DIR__ . "/../base.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php head_icon()?>
    <title>Add Counter | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container container-set before-footer" style="margin-top:100px">
        <div class="row justify-content-center mt-5">
            <div class="col-12 col-md-6" style="max-width: 500px;">
                <div class="card px-4 py-2 mb-2" style="border-radius:30px">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="./dashboard.php" style="text-decoration:none; color:black;">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="./counters.php" style="text-decoration:none; color:black;">Counters</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Add Counter</li>
                        </ol>
                    </nav>
                </div>
                <div class="card shadow-sm p-4" style="border-radius:30px">
                <div class="row align-center my-4">
                        <div class="col text-center">
                            <h4 class="fw-bold">Add Counter</h4>
                        </div>
                    </div>
                    
                    <form method="POST" id="frmAddCounter">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search Username">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Employees Available</label>
                            <div class="input-group">

                            <table class="table table-striped table-members" id="table-counters">
                                <tr>
                                    <th class="col-2"></th>
                                    <th>Username</th>
                                    <th>Available</th>
                                </tr>
                            </table>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-sort-numeric-up"></i></span>
                                <input type="text" name="counter_no" id="counter_no" class="form-control" placeholder="Counter Number" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <button type="submit" class="btn btn-primary w-100">Add Counter</button>
                            <a class="btn btn-secondary w-100 mt-2" href="./counters.php">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include_once "./../includes/footer.php"; ?>
    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/counter_get.js"></script>
</body>
</html>
