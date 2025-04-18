<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Employees | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer" style="margin-top:100px">
        <div class="row justify-content-center mt-5">
            <div class="col-12" style="min-width:450px;max-width: 900px;">
                <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                    <nav aria-label="breadcrumb mx-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="./dashboard.php" style="text-decoration:none;color:black">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Employees</li>
                        </ol>
                    </nav>
                </div>
                <div class="card shadow">
                    <div class="card-header">
                        Employees
                    </div>
                    <div class="card-body">
                        <div class="row align-center my-2">
                            <div class="row">
                                <div class="col">
                                    <h3 class="text-start my-1 mx-2 fw-bold">Employees</h3>
                                </div>
                                <div class="col d-flex justify-content-end p-0">
                                    <a class="btn btn-success text-white" id="btn-add-employee" data-toggle="modal" data-target="#addEmployeeModal" ><span class="fw-bold">+</span> Add New</a>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <div class="input-group w-75">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="search" class="form-control" placeholder="Search username">
                            </div>
                            <div class="w-25">
                                <select class="form-select" name="getRoleType" id="getRoleType">
                                    <option value="none">All</option>
                                    <option value="admin">Admin</option>
                                    <option value="employee">Cashier</option>
                                </select>
                            </div>
                        </div>
                        <table class="table table-striped table-members" id="table-employees">
                            <tr>
                                <th>#</th>
                                <th>Username</th>
                                <th>Action</th>
                            </tr>
                        </table>
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center">
                                <li class="page-item">
                                <a class="page-link" id="pagePrevEmployees">Previous</a>
                                </li>
                                <!-- page number -->
    
                                <li class="page-item">
                                <a class="page-link" id="pageNextEmployees">Next</a>
                                </li>
                            </ul>
                        </nav>

                    </div>
                </div>
            </div>
        </div>
        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateEmployeeModal">
            Edit
        </button> -->
    </div>

    
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="overflow-y:auto;margin-top: 100px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">View Employee: <span id="viewUsernameDisplay"></span></h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                    <div class="col">
                        <div class="row-12">
                            <div class="col text-center">
                                <h4 class="text-center my-1 fw-bold">Employee Details</h4>
                            </div>
                        </div>
                        <!-- <div class="row-12">
                            <div class="col d-flex justify-content-center" style="max-width:300px;max-height:300px;">
                                <img class="w-100 h-100" src="./../asset/images/user_icon.png" alt="">
                            </div>
                        </div> -->
                        <div class="row">
                            <div class="col">
                                ID
                            </div>
                            <div class="col">
                                <span id="viewEmployeeId">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Username
                            </div>
                            <div class="col">
                                <span id="viewEmployeeUsername">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Email
                            </div>
                            <div class="col">
                                <span id="viewEmployeeEmail">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Role
                            </div>
                            <div class="col">
                                <span id="viewEmployeeRoleType">N/A</span>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col">
                                Status
                            </div>
                            <div class="col">
                                <span id="viewEmployeeStatus">N/A</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="frmAddEmployee">
                    <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                        <h5 class="modal-title fw-bold" id="addEmployeeTitle">Modal title</h5>
                    </div>
                    <div class="modal-body py-4 px-6" id="addEmployeeBody">
                        <div class="mb-4">
                            <div class="input-group">
                                <div class="alert alert-danger w-100 d-none" id="addEmployeeAlert">
                                    <span id="addEmployeeAlertMsg"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="add_username" id="add_username" class="form-control" placeholder="Username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="add_password" id="add_password" class="form-control" placeholder="Password" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="add_confirm_password" id="add_confirm_password" class="form-control" placeholder="Confirm password" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="add_email" id="add_email" class="form-control" placeholder="Email" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="role_type" class="form-label">Role</label>
                            <select class="form-select" name="add_role_type" id="add_role_type" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="add_status" id="add_status" value="1">
                            <label class="form-check-label">Activate Employee</label>
                        </div>
                    </div>
                    <div class="modal-footer" id="addEmployeeFooter">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btnAddEmployee">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="frmUpdateEmployee" method="POST">
                    <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                        <h5 class="modal-title fw-bold" id="updateEmployeeTitle">Update Employee: <span id="updateUsernameDisplay"></span></h5>
                    </div>
                    <div class="modal-body py-4 px-6" id="updateEmployeeBody">
                        <div class="mb-4">
                            <div class="input-group">
                                <div class="alert alert-danger w-100 d-none" id="updateEmployeeAlert">
                                    <span id="updateEmployeeAlertMsg"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" name="update_username" id="update_username" class="form-control" placeholder="Username">
                            </div>
                        </div>
                        <div class="mb-4">
                            <input type="hidden" name="update_id" id="update_id">
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="update_password" id="update_password" class="form-control" placeholder="Password">
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" name="update_confirm_password" id="update_confirm_password" class="form-control" placeholder="Confirm Password">
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" name="update_email" id="update_email" class="form-control" placeholder="Email">
                            </div>
                        </div>
                        <div class="mb-4">
                            <select class="form-select" name="update_role_type" id="update_role_type">
                                <option value="">Role</option>
                                <option value="admin">Admin</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="update_active" id="update_active" value="1">
                            <label class="form-check-label">Activate Employee</label>
                        </div>
                    </div>
                    <div class="modal-footer" id="updateEmployeeFooter">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btnUpdateEmployee">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" id="frmDeleteEmployee">
                    <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                        <h5 class="modal-title fw-bold" id="deleteEmployeeTitle">
                            Delete Employee: <span id="deleteUsernameDisplay"></span>
                        </h5>
                    </div>
                    <div class="modal-body p-4 px-6" id="deleteEmployeeBody">
                        <div class="mb-4">
                            <div class="input-group">
                                <div class="alert alert-danger w-100 d-none" id="deleteEmployeeAlert">
                                    <span id="deleteEmployeeAlertMsg"></span>
                                </div>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16" style="color:red">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                </svg>
                            </div>
                            <input type="hidden" name="delete_id" id="delete_id">
                            <label class="form-label">
                                Do you want to delete this employee <strong><span id="delete_username"></span></strong>?
                            </label>
                        </div>
                        <div class="modal-footer" id="deleteEmployeeFooter">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="btnDeleteEmployee">Delete</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include_once "./../includes/footer.php";?>
    <?php after_js()?>
    <script src="./../asset/js/jquery-3.7.1.js"></script>
    <script src="./../asset/js/message.js"></script>
    <script src="./../asset/js/employee.js"></script>
</body>
</html>
