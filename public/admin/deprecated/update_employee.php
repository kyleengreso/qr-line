<?php
include_once __DIR__ . "/../base.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Update Employee | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer" style="margin-top:100px">
        <div class="row justify-content-center mt-5">
        <div class="col-12 col-md-6" style="max-width: 500px;">
                <div class="card px-4 py-2 mb-2" style="border-radius:30px">
                    <nav aria-label="breadcrumb mx-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="./dashboard.php" style="text-decoration:none;color:black">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="./employees.php" style="text-decoration:none;color:black">Employees</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Update Employee</li>
                        </ol>
                    </nav>
                </div>
                <div class="card shadow-sm p-4" style="border-radius:30px">
                <div class="row align-center my-4">
                        <div class="col">
                            <h4 class="text-center my-1 fw-bold">Update Employee</h4>
                        </div>
                    </div>
                    <form id="frmUpdateEmployee" method="POST">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" id="username" class="form-control" placeholder="Username" disabled>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control" placeholder="Password">
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm Password">
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email">
                            </div>
                        </div>
                        <div class="mb-4">
                            <select class="form-select" name="role_type" id="role_type">
                                <option value="">Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" id="status" value="1">
                            <label class="form-check-label">Activate Employee</label>
                        </div>
                        <div class="mb-4">
                            <button type="submit" class="btn btn-primary w-100 mb-2">Update Employee</button>
                            <a class="btn btn-secondary w-100" href="employees.php">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include_once "./../includes/footer.php"; ?>
    <?php after_js()?>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/employee.js"></script>
</body>
</html>