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
    <div class="offcanvas-body d-flex flex-column p-0">
        <!-- User Profile Section -->
        <div class="p-4" style="background: linear-gradient(135deg, rgba(0,0,0,.1) 0%, rgba(0,0,0,.05) 100%); border-bottom: 1px solid rgba(255,255,255,.1);">
            <div class="d-flex align-items-center">
                <div class="position-relative me-3">
                    <img src="./../asset/images/user_icon.png" alt="User" width="60" height="60" class="rounded-circle" style="object-fit: cover; border: 2px solid rgba(255,255,255,.3);">
                    <span class="position-absolute bottom-0 end-0 p-1 bg-success rounded-circle" style="border: 2px solid white; width: 14px; height: 14px;"></span>
                </div>
                <div class="flex-grow-1">
                    <h6 class="mb-1 text-white fw-bold"><?php echo htmlentities($token->username); ?></h6>
                    <small class="text-white-50">Administrator</small>
                </div>
            </div>
        </div>

        <!-- Cut Off Button (Admin Page Only) -->
        <?php if (strpos($_SERVER['REQUEST_URI'], '/public/admin') !== false): ?>
        <div class="p-3" style="border-bottom: 1px solid rgba(255,255,255,.1);">
            <button class="btn btn-outline-light w-100 btn-sm" id="employee-cut-off">
                <i class="bi bi-power me-2"></i>
                <span>Cut Off</span>
            </button>
        </div>
        <?php endif; ?>

        <!-- Menu Items -->
        <ul class="list-unstyled flex-grow-1 mb-0 mt-2">
            <?php if ($is_admin) : ?>
            <li class="nav-item">
                <a href="/public/admin" class="d-flex align-items-center px-4 py-3 text-white text-decoration-none transition-all" style="border-left: 3px solid transparent;">
                    <i class="bi bi-house-door-fill fs-5 me-3" style="width: 24px; text-align: center;"></i>
                    <span class="fs-6">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/public/employees" class="d-flex align-items-center px-4 py-3 text-white text-decoration-none transition-all" style="border-left: 3px solid transparent;">
                    <i class="bi bi-people-fill fs-5 me-3" style="width: 24px; text-align: center;"></i>
                    <span class="fs-6">Employees</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/public/counters" class="d-flex align-items-center px-4 py-3 text-white text-decoration-none transition-all" style="border-left: 3px solid transparent;">
                    <i class="bi bi-diagram-3-fill fs-5 me-3" style="width: 24px; text-align: center;"></i>
                    <span class="fs-6">Counters</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/public/transaction_history" class="d-flex align-items-center px-4 py-3 text-white text-decoration-none transition-all" style="border-left: 3px solid transparent;">
                    <i class="bi bi-clock-history fs-5 me-3" style="width: 24px; text-align: center;"></i>
                    <span class="fs-6">Transactions</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="/public/schedule" class="d-flex align-items-center px-4 py-3 text-white text-decoration-none transition-all" style="border-left: 3px solid transparent;">
                    <i class="bi bi-calendar-check-fill fs-5 me-3" style="width: 24px; text-align: center;"></i>
                    <span class="fs-6">Schedule</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>

        <!-- Divider -->
        <div style="border-top: 1px solid rgba(255,255,255,.1); margin: 0;"></div>

        <!-- Logout Button -->
        <div class="p-3">
            <button class="btn btn-outline-light w-100" id="btn-logout-2">
                <i class="bi bi-box-arrow-right me-2"></i>
                <span>Logout</span>
            </button>
        </div>

        <!-- Footer -->
        <div class="text-center py-2 text-white-50 px-3" style="border-top: 1px solid rgba(255,255,255,.1); font-size: 0.85rem;">
            <small>&copy; <?php echo project_year()?> <?php echo $project_name?></small>
        </div>
    </div>

    <style>
        .nav-item a {
            transition: all 0.3s ease;
        }
        .nav-item a:hover {
            background-color: rgba(255,255,255,.08);
            border-left-color: rgb(255, 110, 55) !important;
        }
        .nav-item a.active {
            background-color: rgba(255, 110, 55, .15);
            border-left-color: rgb(255, 110, 55) !important;
        }
        .transition-all {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const currentUrl = window.location.pathname;
            const navLinks = document.querySelectorAll('.nav-item a');
            
            navLinks.forEach(link => {
                const href = link.getAttribute('href');
                if (href && currentUrl.includes(href)) {
                    link.classList.add('active');
                }
            });
        });
    </script>
</div>
<?php endif; ?>

<script src="./../asset/js/navbar.js"></script>