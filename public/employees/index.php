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
// Server-side fetch employees to render the page initially (fallback to client-side AJAX)
$employees = [];
$total = 0;
$activeCount = 0;
$inactiveCount = 0;
$adminsCount = 0;
// Build internal API URL
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$api_url = $protocol . '://' . $host . '/public/api/api_endpoint.php?employees=true&paginate=1000';
// Use cURL to fetch
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$resp = curl_exec($ch);
curl_close($ch);
if ($resp) {
    $data = json_decode($resp, true);
    if (is_array($data) && isset($data['status']) && $data['status'] === 'success' && isset($data['employees'])) {
        $employees = $data['employees'];
        $total = count($employees);
        foreach ($employees as $e) {
            if (isset($e['active']) && $e['active'] == 1) $activeCount++;
            if (isset($e['role_type']) && $e['role_type'] === 'admin') $adminsCount++;
        }
        $inactiveCount = $total - $activeCount;
    }
}
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

    <div class="container before-footer d-flex justify-content-center" style="margin-top:100px;margin-top:100px;min-height:500px">
    <div class="col-12 col-md-10 col-lg-8 mx-auto" style="max-width:900px">
            <div class="alert text-start alert-success d-none" id="logOutNotify">
                <span><?php echo $username?> has logged out successfully</span>
            </div>
            <div class="card shadow px-4 py-2 mb-2" style="border-radius:30px">
                <nav aria-label="breadcrumb mx-4">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/public/admin" style="text-decoration:none;color:black">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Employees</li>
                    </ol>
                </nav>
            </div>
            <div class="card shadow">
                <div class="card-header">
                    <span>Employees</span>
                </div>
                <div class="card-body">
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col">
                                <h3 class="text-start my-1 mx-2 fw-bold">Employees</h3>
                            </div>
                            <div class="col d-flex justify-content-end">
                                <a class="btn btn-success text-white px-4" id="btn-add-employee" data-bs-toggle="modal" data-bs-target="#addEmployeeModal"><span class="fw-bold">+</span> Add New</a>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12 m-0" style="min-height:auto">
                            <div style="border-radius:12px">
                                <div class="row g-2">
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="bg-white rounded shadow-sm border h-100 p-2">
                                            <div class="row g-2 align-items-center h-100">
                                                <div class="col-3 text-center py-2 px-2">
                                                    <div class="fs-3 text-primary"><i class="bi bi-people-fill"></i></div>
                                                </div>
                                                <div class="col-9 py-1 ps-2">
                                                    <div class="small text-muted mb-1">Total</div>
                                                    <div class="h5 fw-bold mb-0" id="empTotalCount"><?php echo $total;?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="bg-white rounded shadow-sm border h-100 p-2">
                                            <div class="row g-2 align-items-center h-100">
                                                <div class="col-3 text-center py-2 px-2">
                                                    <div class="fs-3 text-success"><i class="bi bi-person-check-fill"></i></div>
                                                </div>
                                                <div class="col-9 py-1 ps-2">
                                                    <div class="small text-muted mb-1">Active</div>
                                                    <div class="h5 text-success fw-bold mb-0" id="empActiveCount"><?php echo $activeCount;?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="bg-white rounded shadow-sm border h-100 p-2">
                                            <div class="row g-2 align-items-center h-100">
                                                <div class="col-3 text-center py-2 px-2">
                                                    <div class="fs-3 text-danger"><i class="bi bi-person-x-fill"></i></div>
                                                </div>
                                                <div class="col-9 py-1 ps-2">
                                                    <div class="small text-muted mb-1">Inactive</div>
                                                    <div class="h5 text-danger fw-bold mb-0" id="empInactiveCount"><?php echo $inactiveCount;?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3 mb-2">
                                        <div class="bg-white rounded shadow-sm border h-100 p-2">
                                            <div class="row g-2 align-items-center h-100">
                                                <div class="col-3 text-center py-2 px-2">
                                                    <div class="fs-3" style="color: rgb(37, 99, 235);"><i class="bi bi-shield-lock-fill"></i></div>
                                                </div>
                                                <div class="col-9 py-1 ps-2">
                                                    <div class="small text-muted mb-1">Admins</div>
                                                    <div class="h5 fw-bold mb-0" id="empAdminsCount"><?php echo $adminsCount;?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 mb-4">
                        <div class="row">
                            <div class="col-12 col-md-8">
                                <div class="input-group mb-2">
                                    <div class="input-group-text"><i class="bi bi-search"></i></div>
                                    <div class="form-floating">
                                        <input type="text" name="search" id="search" class="form-control" placeholder="Search username, email">
                                        <label for="search">Search</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-floating mb-2">
                                    <select class="form-control" name="getRoleType" id="getRoleType">
                                        <option value="none">All</option>
                                        <option value="admin">Admin</option>
                                        <option value="employee">Cashier</option>
                                    </select>
                                    <label for="getRoleType" class="form-label">Role</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="employeesList" class="row g-3">
                        <?php if (!empty($employees)) {
                            foreach ($employees as $employee) {
                                $id = htmlspecialchars($employee['id']);
                                $username = htmlspecialchars($employee['username']);
                                $role = htmlspecialchars($employee['role_type'] ?? '');
                                $emailAddr = htmlspecialchars($employee['email'] ?? '');
                                $created = htmlspecialchars($employee['created_at'] ?? '');
                                $lastLogin = htmlspecialchars($employee['employee_last_login'] ?? '');
                                $active = isset($employee['active']) && $employee['active'] == 1;
                                $statusBadge = $active ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>';
                                $iconHtml = '';
                                $usernameHtml = '<strong>' . $username . '</strong>';
                                if (isset($employee['role_type']) && $employee['role_type'] === 'admin') {
                                    $iconHtml = '<i class="bi bi-person-fill-gear text-primary me-2"></i>';
                                    $usernameHtml = '<strong class="text-primary">' . $username . '</strong>';
                                } else {
                                    $iconHtml = '<i class="bi bi-person-fill text-success me-2"></i>';
                                    $usernameHtml = '<strong class="text-success">' . $username . '</strong>';
                                }
                                echo "<div class=\"col-12\">\n";
                                echo "  <div class=\"card shadow-sm\">\n";
                                // card body: first line -> icon + username + status; second line -> email
                                echo "    <div class=\"card-body d-flex justify-content-between align-items-start\">\n";
                                echo "      <div>\n";
                                echo "        <div class=\"d-flex align-items-center\">\n";
                                echo "          {$iconHtml}\n";
                                echo "          <div>\n";
                                echo "            <div class=\"d-flex align-items-center\">{$usernameHtml}<div class=\"ms-2\">{$statusBadge}</div></div>\n";
                                echo "            <div class=\"small text-muted\">" . ($emailAddr ? $emailAddr : '&mdash;') . "</div>\n";
                                echo "          </div>\n";
                                echo "        </div>\n";
                                echo "      </div>\n";
                                echo "      <div class=\"ms-3\">\n";
                                echo "        <div class=\"btn-group\">\n";
                                echo "          <a class=\"btn btn-sm btn-outline-info text-info\" id=\"view-employee-{$id}\" data-bs-toggle=\"modal\" data-bs-target=\"#viewEmployeeModal\"><i class=\"bi bi-eye-fill\"></i></a>\n";
                                echo "          <a class=\"btn btn-sm btn-outline-primary text-primary\" id=\"update-employee-{$id}\" data-bs-toggle=\"modal\" data-bs-target=\"#updateEmployeeModal\"><i class=\"bi bi-pencil-square\"></i></a>\n";
                                echo "          <a class=\"btn btn-sm btn-outline-danger text-danger\" id=\"delete-employee-{$id}\" data-bs-toggle=\"modal\" data-bs-target=\"#deleteEmployeeModal\"><i class=\"bi bi-trash-fill\"></i></a>\n";
                                echo "        </div>\n";
                                echo "      </div>\n";
                                echo "    </div>\n";
                                echo "  </div>\n";
                                echo "</div>\n";
                            }
                        } else {
                            echo "<div class=\"col-12\">\n<div class=\"card\">\n<div class=\"card-body fw-bold text-center\">No employees assigned</div>\n</div>\n</div>";
                        } ?>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="mt-4 pagination justify-content-center" id="employeesPagination">

                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- View Employee -->
    <div class="modal fade" id="viewEmployeeModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down" role="document">
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
                    <div class="modal-footer col" id="viewEmployeeFooter">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                        <div class="alert alert-danger w-100 d-none" id="updateEmployeeAlert">
                            <span id="updateEmployeeAlertMsg"></span>
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
        var employee_search = '';
        var page_employees = 1;
        var paginate = 10;
        var role_type_employee = 'none';

        const search = document.getElementById('search');
        const getRoleType = document.getElementById('getRoleType');

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
                    if (getRoleType) getRoleType.value = role_type_employee;
                }
                if (urlParams.has('page')) {
                    const p = parseInt(urlParams.get('page'), 10);
                    if (!isNaN(p) && p > 0) page_employees = p;
                }
                if (urlParams.has('paginate')) {
                    const pg = parseInt(urlParams.get('paginate'), 10);
                    if (!isNaN(pg) && pg > 0) paginate = pg;
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

        search.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                page_employees = 1;
                employee_search = e.target.value;
                loadEmployees();
                return;
            }
            debouncedSearch(e.target.value);
        });

        getRoleType.addEventListener('change', (e) => {
            page_employees = 1;
            role_type_employee = e.target.value;
            loadEmployees();
        });

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
            const employeesList = document.getElementById('employeesList');
            if (!employeesList) return;

            // show spinner
            showEmployeesLoading();

            // disable pagination while loading
            const employeesPagination = document.getElementById('employeesPagination');
            if (employeesPagination) {
                employeesPagination.querySelectorAll('a.page-link').forEach(a => a.classList.add('disabled'));
            }

            const params = new URLSearchParams({
                employees: true,
                page: page_employees,
                paginate: paginate,
                search: employee_search,
                role_type: role_type_employee
            });

            $.ajax({
                // use absolute path to the API endpoint to avoid relative-path resolution issues
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    employeesList.innerHTML = '';

                    if (response.status === 'success') {
                        const employees = response.employees;

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

                            const card = `
                                <div class="col-12">
                                    <div class="card shadow-sm">
                                        <div class="card-body d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="d-flex align-items-center">
                                                    ${iconHtml}
                                                    <div>
                                                        <div class="d-flex align-items-center">${usernameHtml}<div class="ms-2">${employee.active == 1 ? textBadge('Active','success') : textBadge('Inactive','danger')}</div></div>
                                                        <div class="small text-muted">${employee.email ? employee.email : '—'}</div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="ms-3">
                                                <div class="btn-group">
                                                    <a class="btn btn-sm btn-outline-info text-info" id="view-employee-${employee.id}" data-bs-toggle="modal" data-bs-target="#viewEmployeeModal"><i class="bi bi-eye-fill"></i></a>
                                                    <a class="btn btn-sm btn-outline-primary text-primary" id="update-employee-${employee.id}" data-bs-toggle="modal" data-bs-target="#updateEmployeeModal"><i class="bi bi-pencil-square"></i></a>
                                                    <a class="btn btn-sm btn-outline-danger text-danger" id="delete-employee-${employee.id}" data-bs-toggle="modal" data-bs-target="#deleteEmployeeModal"><i class="bi bi-trash-fill"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;

                            employeesList.insertAdjacentHTML('beforeend', card);
                        });

                        syncAboutCardHeight();
                    } else {
                        const noCard = `
                            <div class="col-12">
                              <div class="card"><div class="card-body fw-bold text-center">No employees assigned</div></div>
                            </div>
                        `;
                        employeesList.insertAdjacentHTML('beforeend', noCard);
                        syncAboutCardHeight();
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
                        const noCard = `
                            <div class="col-12">
                              <div class="card"><div class="card-body fw-bold text-center">No employees assigned</div></div>
                            </div>
                        `;
                        employeesList.innerHTML = noCard;
                        handled = true;
                    }

                    // 404 explicitly -> empty state
                    if (!handled && xhr && xhr.status === 404) {
                        const noCard = `
                            <div class="col-12">
                              <div class="card"><div class="card-body fw-bold text-center">No employees assigned</div></div>
                            </div>
                        `;
                        employeesList.innerHTML = noCard;
                        handled = true;
                    }

                    // 400 with other message -> bad request
                    if (!handled && xhr && xhr.status === 400) {
                        employeesList.innerHTML = `<div class="col-12"><div class="alert alert-warning">Bad request. Check filters and try again.</div></div>`;
                        handled = true;
                    }

                    // fallback
                    if (!handled) {
                        employeesList.innerHTML = `<div class="col-12"><div class="alert alert-danger">Failed to load employees. Try again.</div></div>`;
                    }

                    // ensure About card height is updated
                    try { syncAboutCardHeight(); } catch (e) {}
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

        // View Employee
        $(document).on('click', '[id^="view-employee-"]', function (e) {
            e.preventDefault();

            const elementId = $(this).attr('id');
            const employeeId = elementId.split('-').pop();
            console.log(employeeId);

            const params = new URLSearchParams({
                employees: true,
                id: employeeId
            });

            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    const employee = response.employee;
                    const username = employee.username;
                    const email = employee.email;
                    const role_type = employee.role_type;
                    const active = employee.active;

                    let viewUsernameDisplay = document.getElementById('viewUsernameDisplay');
                    viewUsernameDisplay.innerText = username;
                    let viewEmployeeId = document.getElementById('viewEmployeeId');
                    viewEmployeeId.innerText = employeeId;
                    let viewEmployeeUsername = document.getElementById('viewEmployeeUsername');
                    viewEmployeeUsername.innerText = username;
                    let viewEmployeeEmail = document.getElementById('viewEmployeeEmail');
                    viewEmployeeEmail.innerText = email ? email : 'Not present';
                    let viewEmployeeRoleType = document.getElementById('viewEmployeeRoleType');
                    viewEmployeeRoleType.innerText = role_type;
                    let viewEmployeeStatus = document.getElementById('viewEmployeeStatus');
                    if (active === 1) {
                        viewEmployeeStatus.innerHTML = textBadge('Active', 'success');
                    } else {
                        viewEmployeeStatus.innerHTML = textBadge('Inactive', 'danger');
                    }
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

            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    username : username,
                    password : password,
                    email : email,
                    role_type : role_type,
                    method : "employees-add",
                    active: active
                }),
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
                }
            });
        });

        // Edit Employee
        $(document).on('click', '[id^="update-employee-"]', function (e) {
            e.preventDefault();

            // Get the ID of the clicked element
            const elementId = $(this).attr('id');
            const employeeId = elementId.split('-').pop(); 

            console.log(employeeId);
            const params = new URLSearchParams({
                employees: true,
                id: employeeId
            });

            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response);

                    const employee = response.employee;
                    const username = employee.username;
                    const email = employee.email;
                    const role_type = employee.role_type;
                    const active = employee.active;

                    let updateUsernameDisplay = document.getElementById('updateUsernameDisplay');
                    updateUsernameDisplay.innerText = username;

                    let frmUpdateEmployee = document.getElementById('frmUpdateEmployee');
                    frmUpdateEmployee.reset();
                    frmUpdateEmployee.elements['update_id'].value = employeeId;
                    frmUpdateEmployee.elements['update_username'].value = username;
                    frmUpdateEmployee.elements['update_email'].value = email;
                    frmUpdateEmployee.elements['update_role_type'].value = role_type;  
                    frmUpdateEmployee.elements['update_active'].checked = active === 1; 
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

            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    id: employeeId,
                    username : username,
                    password : password,
                    email : email,
                    role_type : role_type,
                    method : "employees-update",
                    active: active
                }),
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
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none',);
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    console.error('Update employee error', s, e, x && x.responseText);
                    formAlertMsg.innerText = 'Error: ' + (x.responseText || s);
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
                }
            });
        });

        // Delete Employee
        $(document).on('click', '[id^="delete-employee-"]', function (e) {
            e.preventDefault();

            const elementId = $(this).attr('id');
            const employeeId = elementId.split('-').pop(); 

            console.log(employeeId);
            const params = new URLSearchParams({
                employees: true,
                id: employeeId
            });

            $.ajax({
                url: '/public/api/api_endpoint.php?' + params,
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    console.log(response);
                    let frmDeleteEmployee = document.getElementById('frmDeleteEmployee');
                    frmDeleteEmployee.reset();
                    const employee = response.employee;
                    const username = employee.username;

                    let updateUsernameDisplay = document.getElementById('deleteUsernameDisplay');
                    updateUsernameDisplay.innerText = username;

                    frmDeleteEmployee.elements['delete_id'].value = employeeId;

                    let del_username = document.getElementById('delete_username');
                    del_username.innerText = username;
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
            
            $.ajax({
                url: '/public/api/api_endpoint.php',
                type: 'POST',
                contentType: 'application/json; charset=utf-8',
                dataType: 'json',
                data: JSON.stringify({
                    id: employeeId,
                    method : "employees-delete"
                }),
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
                        formAlertMsg.innerText = response.message;
                        formAlert.classList.remove('d-none',);
                        setTimeout(() => {
                            formAlert.classList.add('d-none');
                        }, 5000);
                    }
                },
                error: function(x, s, e) {
                    console.error('Delete employee error', s, e, x && x.responseText);
                    formAlertMsg.innerText = 'Error: ' + (x.responseText || s);
                    formAlert.classList.remove('d-none');
                    setTimeout(() => {
                        formAlert.classList.add('d-none');
                    }, 5000);
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
    </script>
</body>
</html>
