<?php
include_once __DIR__ . "/../base.php";
restrictAdminMode();

// Read token cookie defensively and normalize into an object (like other pages)
$raw_token = isset($_COOKIE['token']) ? $_COOKIE['token'] : null;
$token = null;
if ($raw_token) {
    $decoded = decryptToken($raw_token, $master_key);
    if ($decoded) {
        // Normalize to stdClass so downstream code can use ->property safely with isset checks
        $token = json_decode(json_encode($decoded));
    }
}

// Defensive accessors to avoid undefined property warnings when token claims are missing
$id = isset($token->id) ? $token->id : null;
$username = isset($token->username) ? $token->username : null;
$role_type = isset($token->role_type) ? $token->role_type : (isset($_COOKIE['role_type']) ? $_COOKIE['role_type'] : null);
$email = isset($token->email) ? $token->email : null;
$counterNumber = isset($token->counterNumber) ? $token->counterNumber : null;
// Server-side fetch employees to render the page initially (fallback to client-side AJAX)
// Note: The Flask API server is called client-side via buildApiUrl() in JavaScript
$employees = [];
$total = 0;
$activeCount = 0;
$inactiveCount = 0;
$adminsCount = 0;
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
    <!-- moved local styles to /public/asset/css/theme.css -->
    <style>
        /* Allow dropdown menus to overflow table-responsive container */
        .table-responsive {
            overflow: visible !important;
        }
        
        /* Ensure dropdown stays on top and not clipped */
        .table-responsive .dropdown {
            position: relative;
        }
        
        .table-responsive .dropdown-menu {
            position: absolute !important;
            overflow: visible;
            z-index: 1050;
        }
    </style>
</head>
<body>
    <?php include "./../includes/navbar.php"; ?>

    <div class="container before-footer d-flex justify-content-center page-top-spacing">
    <div class="col-12 col-md-10 col-lg-8 mx-auto max-w-900">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2 card-rounded-30">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" class="text-a-black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow transactions-card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="mb-0">Employees</h5>
                        <div class="small text-muted">List of employees, roles and status</div>
                    </div>
                    <div class="d-flex gap-2 align-items-center">
                        <div class="small text-muted">Per page</div>
                        <select id="employeesPerPage" class="form-select form-select-sm per-page-select">
                            <option value="10">10</option>
                            <option value="25" selected>25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
                <div class="card-body position-relative">
                    <div class="mb-3 transactions-toolbar">
                        <div class="input-group flex-fill">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" name="searchEmployee" id="searchEmployee" class="form-control" placeholder="Search username or email">
                        </div>

                        <div class="d-flex gap-2 flex-wrap flex-fill w-100">
                            <div class="form-floating flex-fill">
                                <select class="form-select" id="filterRole">
                                    <option value="none">All Roles</option>
                                    <option value="employee">Employee</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <label for="filterRole">Role</label>
                            </div>
                            <div class="form-floating flex-fill">
                                <select class="form-select" id="filterStatus">
                                    <option value="none">All Status</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                <label for="filterStatus">Status</label>
                            </div>
                        </div>

                        <div class="ms-auto d-flex gap-2">
                            <a class="btn btn-success text-white px-3" id="btn-add-employee" data-bs-toggle="modal" data-bs-target="#addEmployeeModal"><span class="fw-bold">+</span> Add</a>
                            <button id="btnExportEmployeesCsv" class="btn btn-outline-secondary btn-sm">Export CSV</button>
                            <button id="btnRefreshEmployees" class="btn btn-primary btn-sm">Refresh</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm align-middle transactions-table" id="table-employees">
                            <thead class="table-light">
                                <tr>
                                    <th>Username</th>
                                    <th class="d-none d-md-table-cell">Email</th>
                                    <th class="d-none d-md-table-cell">Role</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($employees)) {
                                    foreach ($employees as $employee) {
                                        $id = htmlspecialchars($employee['id']);
                                        $username = htmlspecialchars($employee['username']);
                                        $role = htmlspecialchars($employee['role_type'] ?? '');
                                        $emailAddr = htmlspecialchars($employee['email'] ?? '&mdash;');
                                        $active = isset($employee['active']) && $employee['active'] == 1;
                                        $statusBadge = $active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                        $nameCell = (isset($employee['role_type']) && $employee['role_type'] === 'admin') ? '<strong class="text-primary">' . $username . '</strong>' : '<strong>' . $username . '</strong>';
                                        echo "<tr>";
                                        echo "<td>" . $nameCell . "</td>";
                                        echo "<td class=\"d-none d-md-table-cell td-truncate\"><a href=\"mailto:" . $emailAddr . "\">" . $emailAddr . "</a></td>";
                                        echo "<td class=\"d-none d-md-table-cell small text-muted\">" . ($role ?: '&mdash;') . "</td>";
                                        echo "<td>" . $statusBadge . "</td>";
                                        echo "<td class=\"text-end actions-col\">";
                                        echo "<div class=\"dropdown\">";
                                        echo "<button class=\"btn btn-sm btn-outline-secondary dropdown-toggle\" type=\"button\" data-bs-toggle=\"dropdown\" aria-expanded=\"false\">Actions</button>";
                                        echo "<ul class=\"dropdown-menu dropdown-menu-end\">";
                                        echo "<li><a class=\"dropdown-item btn-view\" href=\"#\" data-id=\"" . $id . "\">View</a></li>";
                                        echo "<li><a class=\"dropdown-item btn-edit\" href=\"#\" data-id=\"" . $id . "\">Edit</a></li>";
                                        echo "<li><a class=\"dropdown-item btn-delete\" href=\"#\" data-id=\"" . $id . "\">Delete</a></li>";
                                        echo "</ul></div></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan=\"5\" class=\"text-center text-muted py-4\">No employees found — <button class=\"btn btn-sm btn-success\" onclick=\"document.getElementById('btn-add-employee').click();\">Add Employee</button></td></tr>";
                                } ?>
                            </tbody>
                        </table>
                    </div>

                    <div id="employeesOverlay" class="d-none loader-overlay"><div><div class="spinner-border text-primary" role="status" aria-hidden="true"></div><div class="small text-muted mt-2">Loading...</div></div></div>

                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">Showing up to <strong id="empShowingCount"><?php echo ($total > 0) ? '1-' . $total : '0'; ?></strong></small>
                        </div>
                        <div id="employeesLoader" class="d-none">
                            <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
                            <small class="ms-2">Loading...</small>
                        </div>
                    </div>

                    <nav aria-label="">
                        <ul class="pagination justify-content-center mt-3 mb-0" id="employeesPagination"></ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- View Employee -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="viewEmployeeTitle">View Employee: <span id="viewUsernameDisplay"></span></h5>
                        <div class="small text-white-50" id="viewEmployeeSubtitle">Employee details</div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
                <div class="modal-body py-3 px-4" id="viewEmployeeBody">
                    <div class="container-fluid">
                        <div class="row g-3 align-items-start">
                            <div class="col-12 col-md-4 text-center">
                                <div class="mb-3">
                                    <div class="display-4 text-muted"><i class="bi bi-person-circle"></i></div>
                                </div>
                                <h5 class="fw-bold" id="viewEmployeeUsername">N/A</h5>
                                <div class="small text-muted" id="viewEmployeeRoleType">&mdash;</div>
                                <div class="mt-2" id="viewEmployeeStatus">&mdash;</div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="row mb-2">
                                    <div class="col-4 text-muted small">ID</div>
                                    <div class="col-8"><span id="viewEmployeeId">N/A</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted small">Email</div>
                                    <div class="col-8"><a href="#" id="viewEmployeeEmail">N/A</a></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted small">Role</div>
                                    <div class="col-8"><span id="viewEmployeeRoleType">&mdash;</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted small">Status</div>
                                    <div class="col-8"><span id="viewEmployeeStatus">&mdash;</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted small">Created</div>
                                    <div class="col-8"><span id="viewEmployeeCreated">&mdash;</span></div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-4 text-muted small">Last login</div>
                                    <div class="col-8"><span id="viewEmployeeLastLogin">&mdash;</span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" id="viewEmployeeFooter">
                    <div class="d-flex justify-content-end w-100">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employee (redesigned) -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <form method="POST" id="frmAddEmployee">
                    <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                        <div>
                            <h5 class="modal-title fw-bold" id="addEmployeeTitle">Add Employee</h5>
                            <div class="small text-white-50">Create a new employee account and assign role</div>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0"><strong id="addEmployeeDisplay"></strong></div>
                        </div>
                    </div>
                    <div class="modal-body py-3 px-4" id="addEmployeeBody">
                        <div class="alert alert-danger w-100 d-none" id="addEmployeeAlert">
                            <span id="addEmployeeAlertMsg"></span>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="add_username" id="add_username" class="form-control" placeholder="Username" required>
                                    <label for="add_username">Username</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="add_email" id="add_email" class="form-control" placeholder="Email" required>
                                    <label for="add_email">Email</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="password" name="add_password" id="add_password" class="form-control" placeholder="Password" required>
                                    <label for="add_password">Password</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="password" name="add_confirm_password" id="add_confirm_password" class="form-control" placeholder="Confirm password" required>
                                    <label for="add_confirm_password">Confirm password</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="add_role_type" id="add_role_type" required>
                                        <option value="">Select Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="employee">Employee</option>
                                    </select>
                                    <label for="add_role_type" class="form-label">Role</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch ms-md-3">
                                    <input class="form-check-input" type="checkbox" name="add_status" id="add_status" value="1">
                                    <label class="form-check-label">Activate Employee</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end w-100">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success" id="btnAddEmployee">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Update Employee -->
    <div class="modal fade" id="updateEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <form id="frmUpdateEmployee" method="POST">
                    <div class="modal-header bg-orange-custom d-flex justify-content-between text-white">
                        <div>
                            <h5 class="modal-title fw-bold" id="updateEmployeeTitle">Update Employee: <span id="updateUsernameDisplay"></span></h5>
                            <div class="small text-white-50">Edit employee details and role</div>
                        </div>
                        <div class="text-end">
                            <span class="h6 mb-0 text-white-75">Edit</span>
                        </div>
                    </div>
                    <div class="modal-body py-3 px-4" id="updateEmployeeBody">
                        <div class="alert alert-danger w-100 d-none" id="updateEmployeeAlert" role="alert" aria-live="polite">
                            <span id="updateEmployeeAlertMsg">No changes made</span>
                        </div>

                        <input type="hidden" name="update_id" id="update_id">

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="text" name="update_username" id="update_username" class="form-control" placeholder="Username">
                                    <label for="update_username">Username</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="email" name="update_email" id="update_email" class="form-control" placeholder="Email">
                                    <label for="update_email">Email</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="password" name="update_password" id="update_password" class="form-control" placeholder="Password">
                                    <label for="update_password">New Password</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <input type="password" name="update_confirm_password" id="update_confirm_password" class="form-control" placeholder="Confirm Password">
                                    <label for="update_confirm_password">Confirm Password</label>
                                </div>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" name="update_role_type" id="update_role_type">
                                        <option value="">Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="employee">Employee</option>
                                    </select>
                                    <label for="update_role_type" class="form-label">Role</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-6 d-flex align-items-center">
                                <div class="form-check form-switch ms-md-3">
                                    <input class="form-check-input" type="checkbox" name="update_active" id="update_active" value="1">
                                    <label class="form-check-label">Activate Employee</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer" id="updateEmployeeFooter">
                        <div class="d-flex justify-content-end w-100">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary" id="btnUpdateEmployee">Save</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Employee (redesigned) -->
    <div class="modal fade" id="deleteEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger d-flex justify-content-between text-white">
                    <div>
                        <h5 class="modal-title fw-bold" id="deleteEmployeeTitle">Delete Employee</h5>
                        <div class="small text-white-50">This action cannot be undone</div>
                    </div>
                    <div class="text-end">
                        <span class="h5 mb-0"><strong id="deleteUsernameDisplay">&mdash;</strong></span>
                    </div>
                </div>
                <form method="POST" id="frmDeleteEmployee">
                    <div class="modal-body py-4 px-4" id="deleteEmployeeBody">
                        <div class="mb-3">
                            <div class="alert alert-danger w-100 d-none" id="deleteEmployeeAlert">
                                <span id="deleteEmployeeAlertMsg"></span>
                            </div>
                        </div>
                        <input type="hidden" name="delete_id" id="delete_id">
                        <div class="text-center mb-3">
                            <div class="display-4 text-danger mb-2"><i class="bi bi-exclamation-triangle-fill"></i></div>
                            <h5 class="fw-bold">Confirm deletion</h5>
                            <p class="mb-0">Are you sure you want to remove <strong><span id="delete_username"></span></strong> from the system?</p>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger" id="btnDeleteEmployee">Confirm</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php after_js()?>
    <?php include_once "./../includes/footer.php";?>
    <script src="./../asset/js/message.js"></script>
    <script>       
    const endpointHost = "<?php echo isset($endpoint_server) ? rtrim($endpoint_server, '/') : ''; ?>";
    
    var employee_search = '';
    var page_employees = 1;
    // read default per-page from select if present
    var paginate = (document.getElementById('employeesPerPage') && document.getElementById('employeesPerPage').value) ? Number(document.getElementById('employeesPerPage').value) : 25;
    var role_type_employee = 'none';
    var status_employee = 'none';

    // buildApiUrl is provided by base.js
    const search = document.getElementById('searchEmployee');
    const filterRole = document.getElementById('filterRole');
    const filterStatus = document.getElementById('filterStatus');
    const perPageSelect = document.getElementById('employeesPerPage');

        // Initialize from URL query params if present (search, role_type, page, paginate)
        (function initFromUrl() {
            try {
                const urlParams = new URLSearchParams(window.location.search);
                if (urlParams.has('search')) {
                    employee_search = urlParams.get('search') || '';
                    if (search) search.value = employee_search;
                }
                if (urlParams.has('role_type')) {
                    role_type_employee = urlParams.get('role_type') || 'none';
                    if (filterRole) filterRole.value = role_type_employee;
                }
                if (urlParams.has('status')) {
                    status_employee = urlParams.get('status') || 'none';
                    if (filterStatus) filterStatus.value = status_employee;
                }
                if (urlParams.has('page')) {
                    const p = parseInt(urlParams.get('page'), 10);
                    if (!isNaN(p) && p > 0) page_employees = p;
                }
                if (urlParams.has('paginate')) {
                    const pg = parseInt(urlParams.get('paginate'), 10);
                    if (!isNaN(pg) && pg > 0) paginate = pg;
                } else if (perPageSelect) {
                    paginate = Number(perPageSelect.value) || paginate;
                }
            } catch (e) {
                // ignore URL parsing errors
                console.warn('Failed to parse URL params for employees filters', e);
            }
        })();
        function debounce(fn, wait) {
            let t = null;
            return function (...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), wait);
            };
        }

        function showEmployeesLoading() {
            // Prefer injecting a loading row into the table body when present
            const tbody = document.querySelector('#table-employees tbody');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="5" class="text-center py-4" id="employeesLoading"><div class="spinner-border" role="status"></div></td></tr>`;
                return;
            }
            // Fallback to list container for older layouts
            const employeesList = document.getElementById('employeesList');
            if (!employeesList) return;
            employeesList.innerHTML = `
                <div class="col-12 text-center py-4" id="employeesLoading">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `;
        }

        // Hide loading is implicit when content is replaced by response

        // Debounced search (300ms). Enter triggers immediate search.
        const debouncedSearch = debounce((value) => {
            page_employees = 1;
            employee_search = value;
            loadEmployees();
        }, 300);

        if (search) {
            search.addEventListener('keyup', (e) => {
                if (e.key === 'Enter') {
                    page_employees = 1;
                    employee_search = e.target.value;
                    loadEmployees();
                    return;
                }
                debouncedSearch(e.target.value);
            });
        }

        if (filterRole) {
            filterRole.addEventListener('change', (e) => {
                page_employees = 1;
                role_type_employee = e.target.value;
                loadEmployees();
            });
        }
        if (filterStatus) {
            filterStatus.addEventListener('change', (e) => {
                page_employees = 1;
                status_employee = e.target.value;
                loadEmployees();
            });
        }
        if (perPageSelect) {
            perPageSelect.addEventListener('change', (e) => {
                paginate = Number(e.target.value) || paginate;
                page_employees = 1;
                loadEmployees();
            });
        }

        // Render pagination when totalPages is known
        function renderPagination(totalPages, currentPage) {
            const container = document.getElementById('employeesPagination');
            if (!container) return;
            let items = '';

            const makeItem = (label, page, disabled, active, id) => {
                return `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"><a href="#" class="page-link" ${id ? `id="${id}"` : ''} data-page="${page}">${label}</a></li>`;
            };

            // First
            items += makeItem('First', 1, currentPage === 1, false, 'pageFirstEmployees');
            // Prev
            items += makeItem('Previous', Math.max(1, currentPage - 1), currentPage === 1, false, 'pagePrevEmployees');

            // Page numbers with collapsing
            const CAP = 5; // number of middle pages to try to show
            let start = Math.max(2, currentPage - 2);
            let end = Math.min(totalPages - 1, currentPage + 2);
            if (currentPage <= 3) { start = 2; end = Math.min(totalPages - 1, CAP); }
            if (currentPage > totalPages - 3) { start = Math.max(2, totalPages - CAP); end = totalPages - 1; }

            // first page
            items += `<li class="page-item ${currentPage === 1 ? 'active' : ''}"><a href="#" class="page-link" data-page="1">1</a></li>`;
            if (start > 2) {
                items += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
            }

            for (let p = start; p <= end; p++) {
                items += `<li class="page-item ${p === currentPage ? 'active' : ''}"><a href="#" class="page-link" data-page="${p}">${p}</a></li>`;
            }

            if (end < totalPages - 1) {
                items += `<li class="page-item disabled"><span class="page-link">&hellip;</span></li>`;
            }

            if (totalPages > 1) {
                items += `<li class="page-item ${currentPage === totalPages ? 'active' : ''}"><a href="#" class="page-link" data-page="${totalPages}">${totalPages}</a></li>`;
            }

            // Next
            items += makeItem('Next', Math.min(totalPages, currentPage + 1), currentPage === totalPages, false, 'pageNextEmployees');
            // Last
            items += makeItem('Last', totalPages, currentPage === totalPages, false, 'pageLastEmployees');

            container.innerHTML = items;
        }

        // Render pagination when total is unknown: show simple Prev, current, Next
        function renderPaginationUnknown(currentPage, hasMore) {
            const container = document.getElementById('employeesPagination');
            if (!container) return;
            const prevDisabled = currentPage === 1;
            const nextDisabled = !hasMore;
            const items = `
                <li class="page-item ${prevDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${Math.max(1, currentPage - 1)}">Previous</a></li>
                <li class="page-item active"><span class="page-link">${currentPage}</span></li>
                <li class="page-item ${nextDisabled ? 'disabled' : ''}"><a href="#" class="page-link" data-page="${currentPage + 1}">Next</a></li>
            `;
            container.innerHTML = items;
        }

        function loadEmployees() {
            // Prefer rendering into the table body when present. Keep a reference
            // to the legacy `employeesList` container (server-side rendered fallback)
            // but do not abort if it's missing.
            const employeesList = document.getElementById('employeesList');

            // show spinner
            showEmployeesLoading();

            // disable pagination while loading
            const employeesPagination = document.getElementById('employeesPagination');
            if (employeesPagination) {
                employeesPagination.querySelectorAll('a.page-link').forEach(a => a.classList.add('disabled'));
            }

            // Map page/paginate to Flask params (set_limit, set_offset) and username for search
            const set_limit = paginate;
            const set_offset = (page_employees - 1) * paginate;
            const params = new URLSearchParams();
            if (employee_search && employee_search.trim() !== '') params.set('username', employee_search.trim());
            if (role_type_employee && role_type_employee !== 'none') params.set('role_type', role_type_employee);
            if (status_employee && status_employee !== 'none') params.set('status', status_employee);
            params.set('set_limit', set_limit);
            params.set('set_offset', set_offset);

            // Choose API URL via helper; require endpointHost (do not fall back to local php proxy)
            let apiUrl = buildApiUrl('/api/users', params);
            if (!apiUrl) {
                console.error('API host not configured. Set $endpoint_server in includes/config.php');
                const tbody = document.querySelector('#table-employees tbody');
                if (tbody) tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-4">Service unavailable — API host not configured.</td></tr>`;
                try { document.dispatchEvent(new CustomEvent('employees:loaded')); } catch (e) {}
                return;
            }

            $.ajax({
                // call chosen API URL (proxy for same-origin auth, or direct host when same-origin)
                url: apiUrl,
                type: 'GET',
                timeout: 10000,
                dataType: 'json',
                // when calling the remote host cross-origin we'd require credentials; for proxy it's same-origin
                xhrFields: { withCredentials: true },
                crossDomain: true,
                    beforeSend: function() {
                        try { showEmployeesLoading(); } catch (e) {}
                        try { document.dispatchEvent(new CustomEvent('employees:loading')); } catch (e) {}
                    },
                success: function (response) {
                    // prefer rendering into the table body if present
                    const tbody = document.querySelector('#table-employees tbody');
                    if (tbody) tbody.innerHTML = '';

                    if (response.status === 'success') {
                        // Flask returns list under `data` for list responses
                        const employees = response.data || response.employees || [];

                        // If API provides a total count, prefer it for overall totals/pagination
                        const total = (typeof response.total !== 'undefined') ? parseInt(response.total, 10) : employees.length;
                        const activeCount = employees.filter(e => e.active == 1).length;
                        const inactiveCount = employees.length - activeCount;
                        const adminsCount = employees.filter(e => e.role_type === 'admin').length;

                        const totalElem = document.getElementById('empTotalCount');
                        const activeElem = document.getElementById('empActiveCount');
                        const inactiveElem = document.getElementById('empInactiveCount');
                        const adminsElem = document.getElementById('empAdminsCount');
                        if (totalElem) totalElem.innerText = total;
                        if (activeElem) activeElem.innerText = activeCount;
                        if (inactiveElem) inactiveElem.innerText = inactiveCount;
                        if (adminsElem) adminsElem.innerText = adminsCount;

                        // Pagination: render full numeric pagination when API provides total, otherwise render a simple prev/current/next
                        if (typeof response.total !== 'undefined') {
                            const totalPages = Math.max(1, Math.ceil(total / paginate));
                            renderPagination(totalPages, page_employees);
                        } else {
                            const hasMore = employees.length === paginate;
                            renderPaginationUnknown(page_employees, hasMore);
                        }

                        employees.forEach((employee) => {
                            let iconHtml = '';
                            let usernameHtml = '';
                            if (employee.role_type === 'admin') {
                                iconHtml = '<i class="bi bi-person-fill-gear text-primary me-2"></i>';
                                usernameHtml = `<strong class="text-primary">${employee.username}</strong>`;
                            } else {
                                iconHtml = '<i class="bi bi-person-fill text-success me-2"></i>';
                                usernameHtml = `<strong class="text-success">${employee.username}</strong>`;
                            }

                            // render as a table row into the employees table body
                            const emailCell = employee.email ? `<a href="mailto:${employee.email}">${employee.email}</a>` : '&mdash;';
                            const roleCell = employee.role_type ? employee.role_type : '&mdash;';
                            const statusCell = (employee.active == 1) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                            const nameCell = (employee.role_type === 'admin') ? `<strong class="text-primary">${employee.username}</strong>` : `<strong>${employee.username}</strong>`;

                            const row = `
                                <tr>
                                    <td>${nameCell}</td>
                                    <td class="d-none d-md-table-cell td-truncate">${emailCell}</td>
                                    <td class="d-none d-md-table-cell small text-muted">${roleCell}</td>
                                    <td>${statusCell}</td>
                                    <td class="text-end actions-col">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item btn-view" href="#" data-id="${employee.id}">View</a></li>
                                                <li><a class="dropdown-item btn-edit" href="#" data-id="${employee.id}">Edit</a></li>
                                                <li><a class="dropdown-item btn-delete" href="#" data-id="${employee.id}">Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            `;

                            if (tbody) tbody.insertAdjacentHTML('beforeend', row);
                            else if (employeesList) employeesList.insertAdjacentHTML('beforeend', row);
                        });

                        // update about card height if legacy list is used
                        try { syncAboutCardHeight(); } catch (e) {}
                    } else {
                        const noRow = `<tr><td colspan="5" class="text-center fw-bold py-4">No employees assigned</td></tr>`;
                        const tbody = document.querySelector('#table-employees tbody');
                        if (tbody) tbody.innerHTML = noRow;
                        else if (employeesList) employeesList.insertAdjacentHTML('beforeend', `<div class="col-12"><div class="card"><div class="card-body fw-bold text-center">No employees assigned</div></div></div>`);
                        try { syncAboutCardHeight(); } catch (e) {}
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Employees load error', status, error, xhr && xhr.responseText);

                    // reset counters in the About card
                    const totalElem = document.getElementById('empTotalCount');
                    const activeElem = document.getElementById('empActiveCount');
                    const inactiveElem = document.getElementById('empInactiveCount');
                    const adminsElem = document.getElementById('empAdminsCount');
                    if (totalElem) totalElem.innerText = 0;
                    if (activeElem) activeElem.innerText = 0;
                    if (inactiveElem) inactiveElem.innerText = 0;
                    if (adminsElem) adminsElem.innerText = 0;

                    // Handle specific HTTP statuses for friendlier UX
                    let handled = false;
                    // Try to parse JSON body if present
                    let body = null;
                    try {
                        body = xhr && xhr.responseText ? JSON.parse(xhr.responseText) : null;
                    } catch (e) {
                        body = null;
                    }

                    // If API returned a JSON message saying 'No employees found', treat as empty state
                    if (body && body.message && body.message.toLowerCase().includes('no employees')) {
                        const noRow = `<tr><td colspan="5" class="text-center fw-bold py-4">No employees assigned</td></tr>`;
                        const tbody = document.querySelector('#table-employees tbody');
                        if (tbody) tbody.innerHTML = noRow;
                        else if (employeesList) employeesList.innerHTML = `<div class="col-12"><div class="card"><div class="card-body fw-bold text-center">No employees assigned</div></div></div>`;
                        handled = true;
                    }

                    // 404 explicitly -> empty state
                    if (!handled && xhr && xhr.status === 404) {
                        const noRow = `<tr><td colspan="5" class="text-center fw-bold py-4">No employees assigned</td></tr>`;
                        const tbody = document.querySelector('#table-employees tbody');
                        if (tbody) tbody.innerHTML = noRow;
                        else employeesList.innerHTML = `<div class="col-12"><div class="card"><div class="card-body fw-bold text-center">No employees assigned</div></div></div>`;
                        handled = true;
                    }

                    // 400 with other message -> bad request
                    if (!handled && xhr && xhr.status === 400) {
                        const tbody = document.querySelector('#table-employees tbody');
                        if (tbody) tbody.innerHTML = `<tr><td colspan="5"><div class="alert alert-warning">Bad request. Check filters and try again.</div></td></tr>`;
                        else if (employeesList) employeesList.innerHTML = `<div class="col-12"><div class="alert alert-warning">Bad request. Check filters and try again.</div></div>`;
                        handled = true;
                    }

                    // fallback
                    if (!handled) {
                        const tbody = document.querySelector('#table-employees tbody');
                        const richError = `
                            <div class="alert alert-danger d-flex align-items-start gap-2 mb-0" role="alert">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <div>
                                    <div class="fw-semibold">We couldn’t load employees</div>
                                    <div class="small opacity-75">Please check your connection or try again.</div>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-light js-retry-employees">Retry</button>
                                    </div>
                                </div>
                            </div>`;
                        if (tbody) tbody.innerHTML = `<tr><td colspan="5">${richError}</td></tr>`;
                        else if (employeesList) employeesList.innerHTML = `<div class="col-12">${richError}</div>`;
                    }

                    // ensure About card height is updated
                    try { syncAboutCardHeight(); } catch (e) {}
                },
                complete: function() {
                    // nothing needed; table/list rendering replaces the loader
                    try { document.dispatchEvent(new CustomEvent('employees:loaded')); } catch (e) {}
                }
            });
        }

        function syncAboutCardHeight() {
            const aboutCard = document.getElementById('aboutCard');
            const list = document.getElementById('employeesList');
            if (!aboutCard || !list) return;

            if (window.innerWidth < 768) {
                aboutCard.style.minHeight = '';
                aboutCard.style.height = '';
                return;
            }

            const height = list.offsetHeight || list.scrollHeight || 0;
            const CAP = 380;

            aboutCard.style.height = 'auto';
            aboutCard.style.minHeight = (height > CAP ? CAP : height) + 'px';
        }

        // keep in sync on resize (debounced)
        let _syncTimeout = null;
        window.addEventListener('resize', () => {
            clearTimeout(_syncTimeout);
            _syncTimeout = setTimeout(syncAboutCardHeight, 120);
        });

        // retry handler for rich error alert
        $(document).on('click', '.js-retry-employees', function (e) {
            e.preventDefault();
            try { showEmployeesLoading(); } catch (ex) {}
            loadEmployees();
        });

        // View Employee (delegated for dynamic rows). Accepts both data-id buttons and legacy id-based links.
        $(document).on('click', '.btn-view, [id^="view-employee-"]', function (e) {
            e.preventDefault();

            // Prefer data-id attribute (used by dynamically-rendered rows). Fall back to id-based parsing.
            const $el = $(this);
            const employeeId = $el.data('id') || (function () { const elementId = $el.attr('id'); return elementId ? elementId.split('-').pop() : null; })();
            if (!employeeId) return;

            // Call Flask users endpoint for single-user fetch
            const params = new URLSearchParams({ user_id: employeeId });

            // Use helper to pick direct URL; do not fallback to local proxy
            let apiUrl = buildApiUrl('/api/users', params);
            if (!apiUrl) {
                console.error('API host not configured. Set $endpoint_server in includes/config.php');
                const viewModalEl = document.getElementById('viewEmployeeModal');
                if (viewModalEl) {
                    try { new bootstrap.Modal(viewModalEl).show(); } catch (e) {}
                    const body = document.getElementById('viewEmployeeBody');
                    if (body) body.innerHTML = '<div class="alert alert-danger">Service unavailable — API host not configured.</div>';
                }
                return;
            }

            $.ajax({
                url: apiUrl,
                type: 'GET',
                timeout: 8000,
                dataType: 'json',
                xhrFields: { withCredentials: true },
                crossDomain: true,
                beforeSend: function() {
                    try { document.body.style.cursor = 'progress'; } catch (e) {}
                },
                success: function (response) {
                    console.log(response);
                    const employee = response.data || response.employee || {};
                    const username = employee.username || 'N/A';
                    const email = employee.email || null;
                    const role_type = employee.role_type || '';
                    const active = employee.active;

                    let viewUsernameDisplay = document.getElementById('viewUsernameDisplay');
                    if (viewUsernameDisplay) viewUsernameDisplay.innerText = username;
                    let viewEmployeeId = document.getElementById('viewEmployeeId');
                    if (viewEmployeeId) viewEmployeeId.innerText = employeeId;
                    let viewEmployeeUsername = document.getElementById('viewEmployeeUsername');
                    if (viewEmployeeUsername) viewEmployeeUsername.innerText = username;
                    let viewEmployeeEmail = document.getElementById('viewEmployeeEmail');
                    if (viewEmployeeEmail) viewEmployeeEmail.innerText = email ? email : 'Not present';
                    let viewEmployeeRoleType = document.getElementById('viewEmployeeRoleType');
                    if (viewEmployeeRoleType) viewEmployeeRoleType.innerText = role_type;
                    let viewEmployeeStatus = document.getElementById('viewEmployeeStatus');
                    if (viewEmployeeStatus) {
                        if (active == 1) {
                            viewEmployeeStatus.innerHTML = textBadge('Active', 'success');
                        } else {
                            viewEmployeeStatus.innerHTML = textBadge('Inactive', 'danger');
                        }
                    }

                    // Optional fields: created_at and last_login (if provided by API)
                    try {
                        const fmtDate = (iso) => {
                            if (!iso) return '—';
                            try {
                                const d = new Date(iso);
                                if (isNaN(d.getTime())) return iso; // fallback to raw string
                                return d.toLocaleString();
                            } catch (e) {
                                return iso;
                            }
                        };

                        let viewEmployeeCreated = document.getElementById('viewEmployeeCreated');
                        if (viewEmployeeCreated) viewEmployeeCreated.innerText = fmtDate(employee.created_at);
                        let viewEmployeeLastLogin = document.getElementById('viewEmployeeLastLogin');
                        // show 'Never' when last_login is empty/null
                        if (viewEmployeeLastLogin) viewEmployeeLastLogin.innerText = employee.last_login ? fmtDate(employee.last_login) : 'Never';

                        // For the email anchor, set href when email exists
                        let viewEmployeeEmailAnchor = document.getElementById('viewEmployeeEmail');
                        if (viewEmployeeEmailAnchor) {
                            if (email) {
                                viewEmployeeEmailAnchor.innerText = email;
                                try { viewEmployeeEmailAnchor.setAttribute('href', 'mailto:' + email); } catch (e) {}
                            } else {
                                viewEmployeeEmailAnchor.innerText = '—';
                                try { viewEmployeeEmailAnchor.removeAttribute('href'); } catch (e) {}
                            }
                        }

                        // Ensure role/status show friendly fallbacks
                        try {
                            if (!role_type) {
                                const el = document.getElementById('viewEmployeeRoleType');
                                if (el) el.innerText = '—';
                            }
                            if (typeof active === 'undefined' || active === null) {
                                const el = document.getElementById('viewEmployeeStatus');
                                if (el) el.innerHTML = '<span class="text-muted">—</span>';
                            }
                        } catch (e) {}

                    } catch (e) {
                        // ignore missing fields
                    }

                    // Show the view modal (rows are rendered dynamically so we must open programmatically)
                    try {
                        const viewModalEl = document.getElementById('viewEmployeeModal');
                        if (viewModalEl && typeof bootstrap !== 'undefined') {
                            new bootstrap.Modal(viewModalEl).show();
                        }
                    } catch (e) { console.warn('Could not show view modal', e); }
                },
                error: function(xhr, status, error) {
                    // leave to existing UX; just ensure cursor resets in complete
                },
                complete: function() {
                    try { document.body.style.cursor = ''; } catch (e) {}
                }

            });

        });

        // Add Employee
        let btnAddEmployeeModal = document.getElementById('btn-add-employee');
        if (btnAddEmployeeModal) {
            btnAddEmployeeModal.addEventListener('click', (e) => {
                e.preventDefault();
                let form = document.getElementById('frmAddEmployee');
                if (form) form.reset();
            });
        }

        let frmAddEmployee = document.getElementById('frmAddEmployee');
        if (frmAddEmployee) frmAddEmployee.addEventListener('submit', function (e) {
            e.preventDefault();

            let formAlert = document.getElementById('addEmployeeAlert');
            let formAlertMsg = document.getElementById('addEmployeeAlertMsg');
            const formData = new FormData(this);
            const username = formData.get('add_username');
            const password = formData.get('add_password');
            const confirm_password = formData.get('add_confirm_password');
            const email = formData.get('add_email');
            const role_type = formData.get('add_role_type');
            // normalize checkbox to integer 1/0 to match API expectations
            const active = formData.get('add_status') ? 1 : 0;
            console.log(username, password, confirm_password, email, role_type, active);

            if (password !== confirm_password) {
                formAlertMsg.innerText = 'Password and Confirm Password do not match';
                formAlert.classList.remove('d-none');
                setTimeout(() => {
                    formAlert.classList.add('d-none');
                }, 5000);
                return;
            }

            if (!(typeof endpointHost !== 'undefined' && endpointHost && endpointHost.length > 0)) {
                formAlertMsg.innerText = 'Service unavailable — API host not configured.';
                formAlert.classList.remove('d-none');
                setTimeout(() => { formAlert.classList.add('d-none'); }, 5000);
                return;
            }

            $.ajax({
                url: buildApiUrl('/api/users'),
                type: 'POST',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    username : username,
                    password : password,
                    email : email,
                    user_role : role_type,
                    active: active
                }),
                beforeSend: function() {
                    try {
                        const btn = document.querySelector('#frmAddEmployee button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.dataset.prevLabel = btn.innerHTML;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                        }
                    } catch (e) {}
                },
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(()=> {
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none',);
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    console.error('Add employee error', s, e, x && x.responseText);
                    formAlertMsg.innerText = 'Error: ' + (x.responseText || s);
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                },
                complete: function() {
                    try {
                        const btn = document.querySelector('#frmAddEmployee button[type="submit"]');
                        if (btn) {
                            btn.disabled = false;
                            if (btn.dataset.prevLabel) btn.innerHTML = btn.dataset.prevLabel;
                            delete btn.dataset.prevLabel;
                        }
                    } catch (e) {}
                }
            });
        });

        // Edit Employee (delegated). Works for dynamic rows (data-id) and legacy id-based links.
        $(document).on('click', '.btn-edit, [id^="update-employee-"]', function (e) {
            e.preventDefault();
            const $el = $(this);
            const employeeId = $el.data('id') || (function () { const elementId = $el.attr('id'); return elementId ? elementId.split('-').pop() : null; })();
            if (!employeeId) return;

            // Fetch single user from Flask users endpoint
            const params = new URLSearchParams({ user_id: employeeId });

            // Use helper to pick direct URL; do not fallback to local proxy
            let apiUrl = buildApiUrl('/api/users', params);
            if (!apiUrl) {
                console.error('API host not configured. Set $endpoint_server in includes/config.php');
                return;
            }

            $.ajax({
                url: apiUrl,
                type: 'GET',
                timeout: 8000,
                dataType: 'json',
                xhrFields: { withCredentials: true },
                crossDomain: true,
                beforeSend: function() {
                    try { document.body.style.cursor = 'progress'; } catch (e) {}
                },
                success: function (response) {
                    console.log(response);

                    const employee = response.data || response.employee || {};
                    const username = employee.username || '';
                    const email = employee.email || '';
                    const role_type = employee.role_type || '';
                    const active = employee.active;

                    let updateUsernameDisplay = document.getElementById('updateUsernameDisplay');
                    if (updateUsernameDisplay) updateUsernameDisplay.innerText = username;

                    let frmUpdateEmployee = document.getElementById('frmUpdateEmployee');
                    if (!frmUpdateEmployee) return;
                    frmUpdateEmployee.reset();
                    frmUpdateEmployee.elements['update_id'].value = employeeId;
                    frmUpdateEmployee.elements['update_username'].value = username;
                    frmUpdateEmployee.elements['update_email'].value = email;
                    frmUpdateEmployee.elements['update_role_type'].value = role_type;  
                    frmUpdateEmployee.elements['update_active'].checked = (active == 1); 

                    // Store the original values on the form so we can detect no-op updates
                    try {
                        const original = {
                            username: (username || '').toString().trim(),
                            email: (email || '').toString().trim(),
                            role_type: (role_type || '').toString(),
                            active: (active == 1 ? 1 : 0)
                        };
                        frmUpdateEmployee.dataset.original = JSON.stringify(original);
                    } catch (e) {
                        // ignore dataset failures
                        console.warn('Could not save original update form state', e);
                    }

                    // Show the update modal programmatically (dynamic rows won't have data-bs attrs)
                    try {
                        const updModalEl = document.getElementById('updateEmployeeModal');
                        if (updModalEl && typeof bootstrap !== 'undefined') {
                            new bootstrap.Modal(updModalEl).show();
                        }
                    } catch (e) { console.warn('Could not show update modal', e); }
                },
                error: function(xhr, status, error) {
                    // handled by existing following logic; ensure cursor resets
                },
                complete: function() {
                    try { document.body.style.cursor = ''; } catch (e) {}
                }
            });
        });

        let frmUpdateEmployee = document.getElementById('frmUpdateEmployee');
        if (frmUpdateEmployee) frmUpdateEmployee.addEventListener('submit', function (e) {
            e.preventDefault();

            let formAlert = document.getElementById('updateEmployeeAlert');
            let formAlertMsg = document.getElementById('updateEmployeeAlertMsg');
            
            const formData = new FormData(this);
            const employeeId = formData.get('update_id');
            console.log(employeeId);
            const username = formData.get('update_username');
            const password = formData.get('update_password');
            const confirm_password = formData.get('update_confirm_password');
            const email = formData.get('update_email');
            const role_type = formData.get('update_role_type');
            const active = formData.get('update_active') ? 1 : 0;

            console.log('ID: ' + employeeId);
            console.log('Username: ' + username);
            console.log('Password: ' + password);
            console.log('Confirm Password: ' + confirm_password);
            console.log('Email: ' + email);
            console.log('Role Type: ' + role_type);
            console.log('Active: ' + active);
            // console.log('Status: ' + status);
            if (password !== confirm_password) {
                formAlertMsg.innerText = 'Password and Confirm Password do not match';
                formAlert.classList.remove('d-none');
                setTimeout(() => {
                    formAlert.classList.add('d-none');
                }, 5000);
                return;
            }

            // Let the server be authoritative about whether there were changes.
            // This avoids client/server drift and ensures the response.message
            // returned by the Flask API is displayed to the user.

            // Use Flask API for update (PATCH) so we get consistent behavior with the
            // new frontend list/view endpoints. Only include password when non-empty.
            const payload = { id: employeeId };
            if (username !== null && username !== undefined) payload.username = username;
            if (email !== null && email !== undefined) payload.email = email;
            if (role_type !== null && role_type !== undefined) payload.role_type = role_type;
            if (typeof active !== 'undefined') payload.active = active;
            if (password && password.trim() !== '') payload.password = password;

            // Update via API helper (PATCH). Do not fall back to local proxy.
            let apiUrl = buildApiUrl('/api/users');
            if (!apiUrl) {
                console.error('API host not configured. Set $endpoint_server in includes/config.php');
                let formAlertMsg = document.getElementById('updateEmployeeAlertMsg');
                if (formAlertMsg) formAlertMsg.innerText = 'Service unavailable — API host not configured.';
                return;
            }

            $.ajax({
                url: apiUrl,
                type: 'PATCH',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify(payload),
                xhrFields: { withCredentials: true },
                crossDomain: true,
                beforeSend: function() {
                    try {
                        const btn = document.querySelector('#frmUpdateEmployee button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.dataset.prevLabel = btn.innerHTML;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Saving...';
                        }
                    } catch (e) {}
                },
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message || 'Employee updated';
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message || 'No changes made';
                        formAlert.classList.remove('d-none');
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(xhr, status, err) {
                    console.error('Update employee error', status, err, xhr && xhr.responseText);
                    let msg = 'Error: ' + (xhr && xhr.responseText ? xhr.responseText : status);
                    try {
                        const b = xhr && xhr.responseText ? JSON.parse(xhr.responseText) : null;
                        if (b && b.message) msg = b.message;
                    } catch (e) {}
                    formAlertMsg.innerText = msg;
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                },
                complete: function() {
                    try {
                        const btn = document.querySelector('#frmUpdateEmployee button[type="submit"]');
                        if (btn) {
                            btn.disabled = false;
                            if (btn.dataset.prevLabel) btn.innerHTML = btn.dataset.prevLabel;
                            delete btn.dataset.prevLabel;
                        }
                    } catch (e) {}
                }
            });
        });

        // Delete Employee (delegated). Works for dynamic rows (data-id) and legacy id-based links.
        $(document).on('click', '.btn-delete, [id^="delete-employee-"]', function (e) {
            e.preventDefault();
            const $el = $(this);
            const employeeId = $el.data('id') || (function () { const elementId = $el.attr('id'); return elementId ? elementId.split('-').pop() : null; })();
            if (!employeeId) return;

            const params = new URLSearchParams({ user_id: employeeId });

            // Use helper to pick direct or proxied URL for delete confirmation fetch
            let apiUrl = buildApiUrl('/api/users', params);
            if (!apiUrl) {
                console.error('API host not configured. Set $endpoint_server in includes/config.php');
                return;
            }

            $.ajax({
                url: apiUrl,
                type: 'GET',
                timeout: 8000,
                dataType: 'json',
                xhrFields: { withCredentials: true },
                crossDomain: true,
                beforeSend: function() {
                    try { document.body.style.cursor = 'progress'; } catch (e) {}
                },
                success: function (response) {
                    console.log(response);
                    let frmDeleteEmployee = document.getElementById('frmDeleteEmployee');
                    if (!frmDeleteEmployee) return;
                    frmDeleteEmployee.reset();
                    const employee = response.data || response.employee || {};
                    const username = employee.username || '';

                    let updateUsernameDisplay = document.getElementById('deleteUsernameDisplay');
                    if (updateUsernameDisplay) updateUsernameDisplay.innerText = username;

                    frmDeleteEmployee.elements['delete_id'].value = employeeId;

                    let del_username = document.getElementById('delete_username');
                    if (del_username) del_username.innerText = username;

                    // Show delete confirmation modal programmatically
                    try {
                        const delModalEl = document.getElementById('deleteEmployeeModal');
                        if (delModalEl && typeof bootstrap !== 'undefined') {
                            new bootstrap.Modal(delModalEl).show();
                        }
                    } catch (e) { console.warn('Could not show delete modal', e); }
                },
                error: function(xhr, status, error) {
                    // reset cursor in complete
                },
                complete: function() {
                    try { document.body.style.cursor = ''; } catch (e) {}
                }
            });
        });

        let frmDeleteEmployee = document.getElementById('frmDeleteEmployee');
        if (frmDeleteEmployee) frmDeleteEmployee.addEventListener('submit', function (e) {
            e.preventDefault();

            let formAlert = document.getElementById('deleteEmployeeAlert');
            let formAlertMsg = document.getElementById('deleteEmployeeAlertMsg');
            
            const formData = new FormData(this);
            const employeeId = formData.get('delete_id');
            console.log(employeeId);
            console.log('ID: ' + employeeId);
            
            // Use Flask API for hard delete (force=true)
            // This ensures notify rows are cleaned up before employee deletion
            // Delete via API helper (DELETE)
            let apiUrl = buildApiUrl('/api/users');
            if (!apiUrl) {
                console.error('API host not configured. Set $endpoint_server in includes/config.php');
                return;
            }

            $.ajax({
                url: apiUrl,
                type: 'DELETE',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    user_id: employeeId,
                    force: true
                }),
                xhrFields: { withCredentials: true },
                crossDomain: true,
                beforeSend: function() {
                    try {
                        const btn = document.querySelector('#frmDeleteEmployee button[type="submit"]');
                        if (btn) {
                            btn.disabled = true;
                            btn.dataset.prevLabel = btn.innerHTML;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Deleting...';
                        }
                    } catch (e) {}
                },
                success: function (response) {
                    console.log(response);
                    if (response.status === 'success') {
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none', 'alert-danger');
                        formAlert.classList.add('alert-success');
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        formAlertMsg.innerText = response.message || 'Delete failed';
                        formAlert.classList.remove('d-none');
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    console.error('Delete employee error', s, e, x && x.responseText);
                    let msg = 'Error: ' + (x.responseText || s);
                    try {
                        const b = x && x.responseText ? JSON.parse(x.responseText) : null;
                        if (b && b.message) msg = b.message;
                    } catch (parse_err) {}
                    formAlertMsg.innerText = msg;
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                },
                complete: function() {
                    try {
                        const btn = document.querySelector('#frmDeleteEmployee button[type="submit"]');
                        if (btn) {
                            btn.disabled = false;
                            if (btn.dataset.prevLabel) btn.innerHTML = btn.dataset.prevLabel;
                            delete btn.dataset.prevLabel;
                        }
                    } catch (e) {}
                }
            });
        });

        // Delegated click handler for dynamic pagination links
        const employeesPagination = document.getElementById('employeesPagination');
        if (employeesPagination) {
            employeesPagination.addEventListener('click', function (e) {
                e.preventDefault();
                const target = e.target.closest('a.page-link');
                if (!target) return;
                const pageAttr = target.getAttribute('data-page');
                if (pageAttr) {
                    const p = parseInt(pageAttr, 10);
                    if (!isNaN(p) && p > 0) {
                        page_employees = p;
                        loadEmployees();
                    }
                }
            });
        }

        loadEmployees();

        // Add simple spinner UX for Refresh and Export buttons
        (function() {
            const btnRefresh = document.getElementById('btnRefreshEmployees');
            const btnExport = document.getElementById('btnExportEmployeesCsv');

            function setLoadingBtn(btn, label) {
                if (!btn) return;
                try {
                    btn.dataset.prevLabel = btn.innerHTML;
                } catch (e) {}
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' + label;
            }

            function restoreBtn(btn) {
                if (!btn) return;
                btn.disabled = false;
                try {
                    if (btn.dataset.prevLabel) {
                        btn.innerHTML = btn.dataset.prevLabel;
                        delete btn.dataset.prevLabel;
                    }
                } catch (e) {}
            }

            if (btnRefresh) {
                btnRefresh.addEventListener('click', function(e) {
                    e.preventDefault();
                    setLoadingBtn(this, 'Refreshing...');
                    // Trigger reload; `employees:loaded` event will restore the button
                    loadEmployees();
                });
            }

            if (btnExport) {
                btnExport.addEventListener('click', function(e) {
                    e.preventDefault();
                    const btn = this;
                    setLoadingBtn(btn, 'Exporting...');

                    // Attempt to download CSV from the Flask API
                    const downloadParams = new URLSearchParams({ format: 'csv', paginate: 1000 });
                    let url = buildApiUrl('/api/users', downloadParams);
                    if (!url) {
                        alert('Service unavailable — API host not configured.');
                        restoreBtn(btn);
                        return;
                    }

                    fetch(url, { credentials: 'include' })
                        .then(resp => {
                            if (!resp.ok) throw new Error('Export failed (status ' + resp.status + ')');
                            return resp.blob();
                        })
                        .then(blob => {
                            const a = document.createElement('a');
                            const urlBlob = URL.createObjectURL(blob);
                            a.href = urlBlob;
                            a.download = 'employees.csv';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            URL.revokeObjectURL(urlBlob);
                        })
                        .catch(err => {
                            console.error('Export CSV failed', err);
                            alert('Failed to export CSV: ' + (err && err.message ? err.message : err));
                        })
                        .finally(() => {
                            restoreBtn(btn);
                        });
                });
            }

            // Restore refresh button when employees load completes
            document.addEventListener('employees:loaded', function() { restoreBtn(btnRefresh); });
        })();
    </script>
</body>
</html>
