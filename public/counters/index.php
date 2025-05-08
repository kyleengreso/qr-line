<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();

$token = $_COOKIE['token'];
$token = decryptToken($token, $master_key);
$token = json_encode($token);
$token = json_decode($token);

$id = $token->id;
$username = $token->username;
$role_type = $token->role_type;
$email = $token->email;
$counterNumber = $token->counterNumber;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php head_icon()?>
    <title>Counters | <?php echo $project_name?></title>
    <?php head_css()?>
    <?php before_js()?>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;min-height:500px">
        <div class="col-md-6" style="min-width:400px;max-width:900px;transform:scale(0.9)">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" style="text-decoration:none;color:black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Counters</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow">
                <div class="card-header">
                    <span>Counters</span>
                </div>
                <div class="card-body">
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-start my-1 mx-2 fw-bold">Counters</h3>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <a class="btn btn-success text-white px-4" id="btn-add-counter" data-toggle="modal" data-target="#addCounterModal" ><span class="fw-bold">+</span> Add New</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col-12">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-search"></i></div>
                                    <div class="form-floating">
                                        <input type="text" name="searchAdd" id="searchCounterRegistered" class="form-control" placeholder="Search username">
                                        <label for="searchAdd">Search Username</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <table class="table table-striped table-members" id="table-counters-registered">
                        <thead>
                            <th>#</th>
                            <!-- <th>Queue Count</th> -->
                            <th>Employee</th>
                            <th>Action</th>
                        </thead>
                        <tbody>
                            <!-- Load -->
                        </tbody>
                    </table>
                    <nav aria-label="">
                        <ul class="pagination justify-content-center">
                            <li class="page-item">
                                <a class="page-link disabled" id="pagePrevCounterRegistered">Previous</a>
                            </li>
                            <!-- Page number reserved -->
                            <li class="page-item">
                                <a class="page-link" id="pageNextCounterRegistered">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW COUNTER -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="viewEmployeeTitle">Modal title</h5>
                </div>
                <div class="modal-body py-4 px-6" id="viewEmployeeBody">
                
                </div>
                <div class="modal-footer col" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- ADD COUNTER -->
    <div class="modal fade" id="addCounterModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                    <h5 class="modal-title fw-bold" id="addCounterTitle">Add Counter</h5>
                </div>
                <form method="POST" id="frmAddCounter">
                    <div class="modal-body py-4 px-6" id="addCounterBody">
                        <div class="mb-2">
                            <div class="alert alert-danger w-100 d-none" id="addCounterAlert">
                                <span id="addCounterAlertMsg"></span>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-person-fill"></i></div>
                            <div class="form-floating">
                                <input type="text" name="addSearchUsername" id="addSearchUsername" class="form-control" placeholder="Search username">
                                <label for="addSearchUsername">Search Username</label>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Employees Available</label>
                            <div class="w-100">
                                <table class="table table-striped table-members" id="table-add-counter-available">
                                    <tr>
                                        <th class="col-2"></th>
                                        <th>Username</th>
                                        <th>Available</th>
                                    </tr>
                                </table>
                                <div class="w-100">
                                    <nav class="w-100" aria-label="Page navigation example">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item">
                                                <a class="page-link disabled" id="pagePrevCounterAvailableAdd">Previous</a>
                                            </li>
                                            <li class="page-item">
                                                <a class="page-link" id="pageNextCounterAvailableAdd">Next</a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text" ><i class="bi bi-arrow-down-up"></i></div>
                            <div class="form-floating">
                                <input type="number" name="counter_no_add" id="counter_no_add" class="form-control" placeholder="Counter Number" required>
                                <label for="counter_no_add">Counter Number</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div class="d-flex justify-content-end">
                                <a class="btn btn-secondary" style="width:max-content;margin-right:10px" data-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-primary" style="width:max-content;margin-right:10px">Add Counter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- UPDATE COUNTER -->
    <div class="modal fade" id="updateCounterModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="updateCounterTitle">Update Counter: <span id="updateCounterDisplay"></span></h5>
                </div>
                <div class="modal-body py-4 px-6" id="updateCounterBody">
                    <form method="POST" id="frmUpdateCounter">
                        <div class="mb-2">
                            <div class="alert alert-danger w-100 d-none" id="updateCounterAlert">
                                <span id="updateCounterAlertMsg"></span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <input type="hidden" name="update_counter_no" id="update_counter_no">
                            <input type="hidden" name="update_id" id="update_id">
                            <div class="w-100 pb-3">
                                Username: <strong><span id="updateCounterUsername">NaN</span></strong>
                            </div>
                            <div class="w-100 pb-3">
                                Counter No.: <strong><span id="updateCounterNumber">NaN</span></strong>
                            </div>
                            <div class="d-none">
                                <span id="update-idcounter"></span>
                            </div>
                        </div>
                        <div class="input-group mb-2">
                            <div class="input-group-text"><i class="bi bi-search"></i></div>
                            <div class="form-floating">
                                <input type="text" class="form-control" id="updateSearchUsername" placeholder="Search Username">
                                <label for="updateSearchUsername">Search Username</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Employees Available</label>
                            <div class="w-100">
                                <table class="table table-striped table-members" id="table-update-counter-available">
                                    <tr>
                                        <th class="col-2"></th>
                                        <th>Username</th>
                                        <th>Available</th>
                                    </tr>
                                </table>
                                <div class="w-100">
                                        <nav class="w-100" aria-label="Page navigation example">
                                            <ul class="pagination justify-content-center">
                                                <li class="page-item">
                                                <a class="page-link disabled" id="pagePrevCounterAvailableUpdate">Previous</a>
                                                </li>
                                                <li class="page-item">
                                                <a class="page-link" id="pageNextCounterAvailableUpdate">Next</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                            </div>
                        </div>
                        <div class="mb-3 d-none">
                            <label class="form-label">Counter No.</label>
                            <div class="form-floating mb-2">
                                <span class="form-floating mb-2-text"><i class="fas fa-sort-numeric-up"></i></span>
                                <input type="hidden" name="counter_no_update" id="counter_no_update" class="form-control" placeholder="Enter counter number" required>
                            </div>
                        </div>
                        <div class="modal-footer row`">
                            <div class="d-flex justify-content-end">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="width:max-content;margin-right:10px">Cancel</button>
                                <button type="submit" class="btn btn-primary" style="width:max-content;margin-right:10px">Update Counter</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- DELETE COUNTER -->
    <div class="modal fade" id="deleteCounterModal" tabindex="-1" role="dialog"  aria-hidden="true" style="overflow-y:auto;margin-top: 50px">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-start text-white">
                <h5 class="modal-title fw-bold" id="deleteCounterTitle">Delete Counter: <span id="deleteCounterDisplay"></span></h5>
                </div>
                <form method="POST" id="frmDeleteCounter">
                    <div class="modal-body p-4 px-6" id="deleteEmployeeBody">
                        <div class="mb-2">
                            <div class="alert alert-danger w-100 d-none" id="deleteCounterAlert">
                                <span id="deleteCounterAlertMsg"></span>
                            </div>
                        </div>
                        <input type="hidden" name="delete_id" id="delete_id">
                        <div class="mb-4">
                            <div class="d-flex justify-content-center mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-exclamation-triangle" viewBox="0 0 16 16" style="color:red">
                                    <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.15.15 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.2.2 0 0 1-.054.06.1.1 0 0 1-.066.017H1.146a.1.1 0 0 1-.066-.017.2.2 0 0 1-.054-.06.18.18 0 0 1 .002-.183L7.884 2.073a.15.15 0 0 1 .054-.057m1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767z"/>
                                    <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0M7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0z"/>
                                </svg>
                            </div>
                            <div class="text-center">
                                <h5 class="fw-bold">Are you sure?</h5>
                            </div>
                        </div>
                        <div class="p-2">
                            <label class="form-label" style="color:#333;font-size:14px">Do you want to delete this employee <strong><span id="deleteCounterUsername"></span></strong> assigned at counter number <strong><span id="deleteCounterNumber"></span></strong>?</label>
                        </div>
                        <div class="modal-footer row">
                            <div class="d-flex justify-content-end">
                                <a class="btn btn-secondary" style="width:max-content;margin-right:10px" data-dismiss="modal">Cancel</a>
                                <button type="submit" class="btn btn-danger" style="width:max-content">Delete Counter</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <?php include_once './../includes/footer.php'; ?>
    <?php after_js()?>
    <script src="./../asset/js/counters.js"></script>
</body>
</html>
