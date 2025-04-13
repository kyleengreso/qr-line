<?php
include_once __DIR__ . "/../base.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Delete Employee | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container container-set before-footer" style="margin-top:100px">
        <div class="row justify-content-center mt-5">
            <div class="col-12 col-md-6" style="max-width:500px">
                <div class="card px-4 py-2 mb-2" style="border-radius:30px">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="./dashboard.php" style="text-decoration:none; color:black;">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="./employees.php" style="text-decoration:none; color:black;">Employees</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Delete Employee</li>
                        </ol>
                    </nav>
                </div>
                <div class="card shadow-sm p-4 w-100" style="border-radius:30px">
                    <form method="POST" id="frmDeleteEmployee">
                        <div class="mb-4">
                            <div class="d-flex justify-content-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16" style="color:red">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                </svg>
                            </div>
                                <label class="form-label">Do you want to delete this employee <strong><span id="username"></span></strong>?</label>
                        </div>
                        <div class="col col-12 offset-md-3 col-md-6 p-0 mb-4">
                            <button type="submit" class="btn btn-danger w-100 w-md-50 mb-4">Delete Employee</button>
                            <a class="btn btn-secondary w-100 w-md-50" href="employees.php">Back</a>
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
