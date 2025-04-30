<?php
// Checking if token was present
if (isset($_COOKIE['token'])) {
    $token = $_COOKIE['token'];
    $token = decryptToken($token, $master_key);
    $token = json_encode($token);
    $token = json_decode($token);
}
?>
<nav class="navbar fixed-top" style="background-color: rgb(255, 110, 55);">
    <div class="container d-flex justify-content-between align-items-center" style="width: 100%;">

        <button class="btn btn-primary text-decoration-none me-3" style="border-radius: 5px; border: 1px solid #fff; background-color: transparent;" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Toggle Sidebar">
            <i class="bi bi-list"></i>
        </button>

        <span class="navbar-brand mb-0 text-white d-flex align-items-start align-md-items-center mx-auto">
            <img src="./../asset/images/logo.png" alt="PSU logo" width="40" height="40" class="me-2">
            <span class="d-none d-md-block fs-5 fw-normal fs-4"><?php echo $project_name_full; ?></span>
        </span>

        <!-- Logout Button (Right) -->
        <?php if (isset($token) && $token) : ?>
        <a class="d-none d-md-block btn btn-primary text-decoration-none" style="border-radius: 5px; border: 1px solid #fff; background-color: transparent;" id="btn-logout-1" aria-label="Logout">
            <i class="bi bi-box-arrow-right"></i>
        </a>
        <?php endif; ?>
    </div>
</nav>

<!-- Sidebar -->
<div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" style="width: 350px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fs-3" id="sidebarLabel"><?php echo $project_name?></h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex align-items-center mb-3">
            <img src="./../asset/images/user_icon.png" alt="User" class="rounded-circle me-2 mr-2" width="75" height="75" style="border: 1px #111 solid">
            <div class="text-white">
                <h6 class="mb-0 fs-4"><?php echo $token->username; ?></h6>
            </div>
        </div>
        <ul class="list-unstyled">
            <?php if ($token->role_type == 'admin') : ?>
            <li>
                <div class="py-4 px-2">
                    <a href="/public/admin/dashboard.php" class="w-100 fs-4 text-white text-decoration-none">
                        <div class="row">
                            <div class="col-2">
                                <i class="bi bi-house-fill"></i>
                            </div>
                            <div class="col-10">
                                <span>Home</span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>
            <li>
                <div class="py-4 px-2">
                    <a href="/public/admin/employees.php" class="w-100 fs-4 text-white text-decoration-none">
                        <div class="row">
                            <div class="col-2">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="col-10">
                                <span>Employees</span>    
                            </div>
                        </div>
                    </a>
                </div>
            </li>
            <li>
                <div class="py-4 px-2">
                        <a href="/public/admin/counters.php" class="w-100 fs-4 text-white text-decoration-none"> 
                            <div class="row">
                                <div class="col-2">
                                    <i class="bi bi-arrow-down-up"></i>
                                </div>
                                <div class="col-10">
                                    <span>Counters</span>
                                </div>
                            </div>                 
                    </a>
                </div>
            </li>
            <li>
                <div class="py-4 px-2">
                    <a href="#" class="fs-4 text-white text-decoration-none">
                        <div class="row">
                            <div class="col-2">
                                <i class="bi bi-person-lines-fill"></i>
                            </div>
                            <div class="col-10">
                                <span>Transaction History</span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>
            <?php endif; ?>
            <li>
                <div class="py-4 px-2">
                    <a class="w-100 fs-4 text-white text-decoration-none" id="btn-logout-2">
                        <div class="row">
                            <div class="col-2">
                                <i class="bi bi-box-arrow-right"></i>
                            </div>
                            <div class="col-10">
                                <span>Logout</span>
                            </div>
                        </div>
                    </a>
                </div>
            </li>
        </ul>
    </div>
    <div class="offcanvas-footer text-center py-3" style="background-color: rgb(255, 110, 55);">
        <span>&copy <?php echo project_year()?> <?php echo $project_name?>, All Rights Reserved.</span>
    </div>
</div>
<script src="./../asset/js/navbar.js"></script>