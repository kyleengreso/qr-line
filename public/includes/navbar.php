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

<nav class="navbar navbar-expand-md fixed-top" style="background: linear-gradient(135deg, rgb(255,110,55) 0%, #e63c14 100%); box-shadow: 0 4px 12px rgba(0,0,0,.15);">
    <div class="container-fluid d-flex justify-content-between align-items-center py-2 px-3">
        <?php if ($is_admin) : ?>
            <button class="btn text-white me-3 p-2 rounded-3"
                    style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25);"
                    data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Toggle Sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>
        <?php else : ?>
            <div class="me-3" style="width:38px"></div>
        <?php endif; ?>

        <a class="text-decoration-none flex-grow-1" href="/public">
            <span class="navbar-brand mb-0 text-white d-flex align-items-center">
                <img src="./../asset/images/logo.png" alt="Logo" width="40" height="40" class="me-2">
                <span class="fs-5 fw-semibold d-none d-md-inline"><?php echo $project_name_full; ?></span>
            </span>
        </a>

        <div class="d-flex align-items-center gap-2">
            <?php if ($token_username) : ?>
                <span class="text-white d-none d-md-flex align-items-center px-3 py-1 rounded-3"
                      style="background-color: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25)">
                    <i class="bi bi-person-circle me-2"></i>
                    <span class="fs-6 fw-semibold"><?php echo htmlentities($token_username); ?></span>
                </span>
            <?php endif; ?>
            <span class="text-white d-none d-md-flex align-items-center px-3 py-1 rounded-3" id="navbar-clock"
                  style="background-color: rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.25)">
                <i class="bi bi-clock me-2"></i>
                <span id="current-time" class="fw-semibold"></span>
            </span>
            <button class="btn text-white p-2 rounded-3 position-relative" id="btn-notifications" aria-label="Notifications"
                    style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25)">
                <i class="bi bi-bell fs-5"></i>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notification-count">0</span>
            </button>
            <button class="btn text-white p-2 rounded-3" id="btn-logout-1" aria-label="Logout"
                    style="background-color: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.25)">
                <i class="bi bi-box-arrow-right fs-5"></i>
            </button>
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
            <img src="./../asset/images/user_icon.png" alt="User" class="me-2" width="75" height="75" style="border: 1px #111 solid">
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