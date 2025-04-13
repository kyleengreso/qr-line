<?php
include_once __DIR__ . "/../base.php";
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
                <div class="card shadow-sm px-4 py-2 mb-2" style="border-radius:30px">
                    <nav aria-label="breadcrumb mx-4">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="./dashboard.php" style="text-decoration:none;color:black">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Employees</li>
                        </ol>
                    </nav>
                </div>
                <div class="card shadow-sm p-4" style="border-radius:30px">
                    <div class="row align-center d-flex justify-content-between">
                        <div class="col d-flex">
                            <button class="btn btn-primary" onclick="window.location.href='./dashboard.php'">Back Dashboard</button>
                        </div>
                    </div>
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
                            <th>Created At</th>
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
        <!-- <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#updateEmployeeModal">
            Edit
        </button> -->
    </div>

    
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                
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
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="addEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body py-4 px-6" id="addEmployeeBody">
                    ...   
                </div>
                <div class="modal-footer" id="addEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="btnAddEmployee">Add</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="updateEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="updateEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body py-4 px-6" id="updateEmployeeBody">
                
                </div>
                <div class="modal-footer" id="updateEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="btnUpdateEmployee">Update</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" style="margin-top: 100px;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="deleteEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body p-4 px-6" id="deleteEmployeeBody">
                ...
                </div>
                <div class="modal-footer" id="deleteEmployeeFooter">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="btnDeleteEmployee">Delete</button>
                </div>
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
