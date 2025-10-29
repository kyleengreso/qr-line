<?php
// Safe helper to read a property from the token which may be an object or an array.
function token_prop($token, $prop) {
    if (is_object($token) && isset($token->{$prop})) return $token->{$prop};
    if (is_array($token) && isset($token[$prop])) return $token[$prop];
    return null;
}

?>
<?php
// Normalize common token values for template checks
$token_role_raw = token_prop($token, 'role_type');
// If token payload doesn't include a role, fall back to role_type cookie set at login
if (!$token_role_raw && isset($_COOKIE['role_type'])) {
    $token_role_raw = $_COOKIE['role_type'];
}
$token_role = is_string($token_role_raw) ? strtolower($token_role_raw) : null;
$is_admin = in_array($token_role, ['admin','administrator','superadmin'], true);
$is_employee = in_array($token_role, ['employee','cashier','attendant'], true);
$token_username = token_prop($token, 'username');
?>

<nav class="navbar navbar-expand-md fixed-top" style="background-color: rgb(255, 110, 55);">
    <div class="container-fluid d-flex justify-content-between align-items-center">

        <!-- Sidebar Toggle Button -->
    <?php if ($is_admin) : ?>
        <button class="btn btn-link text-white me-3 p-0" style="border-radius: 5px; border: 1px solid rgba(255,255,255,0.12); background-color: transparent;" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Toggle Sidebar">
            <i class="bi bi-list fs-4"></i>
        </button>
        <?php else : ?>
        <div class="me-3" style="width:38px"></div>
        <?php endif; ?>

        <!-- Project Name -->
        <a class="text-decoration-none" href="/public">
            <span class="navbar-brand mb-0 text-white d-flex align-items-center">
                <img src="./../asset/images/logo.png" alt="PSU logo" width="40" height="40" class="me-2">
                <span class="fs-5 fw-normal d-md-inline d-none"><?php echo $project_name_full; ?></span>
            </span>
        </a>

        <!-- Username and Clock -->
        <div class="d-flex align-items-center">
            <?php if ($token_username) : ?>
                    <span class="text-white me-3 d-none d-md-block">
                        <span class="fs-6 fw-semibold"><?php echo htmlentities($token_username); ?></span>
                    </span>
            <?php endif; ?>
            <span class="text-white me-3 d-none d-md-block" id="navbar-clock">
                <span id="current-time"></span>
            </span>
            <button class="btn btn-link text-white p-1" id="btn-logout-1" aria-label="Logout">
                <i class="bi bi-box-arrow-right fs-4"></i>
            </button>
        </div>
    </div>
    </div>
</nav>

<!-- Sidebar -->
<?php if ($is_admin) : ?>
<div class="offcanvas offcanvas-start bg-primary text-white" tabindex="-1" id="sidebar" aria-labelledby="sidebarLabel" style="width: 320px;">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title fs-3" id="sidebarLabel"><?php echo $project_name?></h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="d-flex align-items-center mb-3">
            <img src="./../asset/images/user_icon.png" alt="User" class="rounded-circle me-2" width="75" height="75" style="border: 1px #111 solid">
            <div class="text-white">
                <h6 class="mb-0 fs-4"><?php echo htmlentities($token->username); ?></h6>
            </div>
        </div>
        <ul class="list-unstyled">
            <li>
            <?php if (strpos($_SERVER['REQUEST_URI'], '/public/admin') !== false): ?>
                <div class="py-3 px-2">
                    <div class="row">
                        <a class="btn btn-danger ms-auto" id="employee-cut-off">
                            <span><i class="bi bi-power shadow-sm"></i></span>
                            CUT OFF
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            </li>
            <?php if ($is_admin) : ?>
            <li>
                <div class="py-3 px-2">
                    <a href="/public/admin" class="w-100 fs-5 text-white text-decoration-none">
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
                <div class="py-3 px-2">
                    <a href="/public/employees" class="w-100 fs-5 text-white text-decoration-none">
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
                <div class="py-3 px-2">
                        <a href="/public/counters" class="w-100 fs-5 text-white text-decoration-none"> 
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
                <div class="py-3 px-2">
                    <a href="/public/transaction_history" class="fs-5 text-white text-decoration-none">
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
            <li>
                <div class="py-3 px-2">
                    <a href="/public/schedule" class="w-100 fs-5 text-white text-decoration-none">
                        <div class="row">
                            <div class="col-2">
                                <i class="bi bi-gear-fill"></i>
                            </div>
                            <div class="col-10">
                                <span>Settings</span>    
                            </div>
                        </div>
                    </a>
                </div>
            </li>
            <?php endif; ?>
            <li>
                <div class="py-3 px-2">
                    <button class="btn btn-link text-white w-100 text-start" id="btn-logout-2">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-box-arrow-right me-3"></i>
                            <span>Logout</span>
                        </div>
                    </button>
                </div>
            </li>
        </ul>
    </div>
    <div class="offcanvas-footer text-center py-3" style="background-color: rgb(255, 110, 55);">
        <span>&copy <?php echo project_year()?> <?php echo $project_name?>, All Rights Reserved.</span>
    </div>
</div>
<?php endif; ?>

<script src="./../asset/js/navbar.js"></script>